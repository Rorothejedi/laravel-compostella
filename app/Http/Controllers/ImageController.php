<?php

namespace App\Http\Controllers;

use App\Models\Image as ImageModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ImageManager;

class ImageController extends Controller
{
    /**
     * Upload one or several images on server side
     */
    public function store(Request $request)
    {
        $request->validate([
            'album_id' => 'required|exists:albums,id',
            'images' => 'required',
            'images.*' => 'image|mimes:jpeg,jpg,png',
        ]);

        if (!$request->hasFile('images')) {
            return response([
                'message' => 'No images to upload'
            ], 204);
        }

        foreach ($request->file('images') as $file) {
            // uniqid ?
            $name = time() . rand(1, 100);
            $thumbnail_name = "thumbnail_$name";
            $cover_name = "cover_$name";

            // Main image (for gallery)
            ImageManager::make($file)
                ->orientate()
                ->heighten(1500)
                ->save("storage/$name.jpg", 70);

            // Thumbnail image (for gallery)
            ImageManager::make($file)
                ->orientate()
                ->heighten(400)
                ->save("storage/$thumbnail_name.jpg", 80);

            // Cover image
            ImageManager::make($file)
                ->orientate()
                ->fit(400)
                ->save("storage/$cover_name.jpg", 85);

            $image = new ImageModel();

            $image_size = getimagesize("storage/$name.jpg");
            $thumbnail_size = getimagesize("storage/$thumbnail_name.jpg");

            $image->album_id = $request->album_id;
            $image->album_order = $this->getMaxImageOrder($request->album_id) + 1;

            $image->path = "storage/$name.jpg";
            $image->width = $image_size[0];
            $image->height = $image_size[1];

            $image->thumbnail_path = "storage/$thumbnail_name.jpg";
            // A voir si c'est utile (peut être pour la grid) ?
            $image->thumbnail_width = $thumbnail_size[0];
            $image->thumbnail_height = $thumbnail_size[1];

            $image->cover_path = "storage/$cover_name.jpg";

            $image->save();
        }

        return response([
            'message' => 'Images uploaded succesfully'
        ]);
    }

    /**
     * Update the image data
     * Response 204
     */
    public function update(Request $request, ImageModel $image)
    {
        $request->validate([
            'album_order' => 'filled|integer',
            'text' => 'nullable|string',
            'main_album_image' => 'filled|boolean',
        ]);

        if ($request->has('album_order')) {
            $this->switchOtherImageOrder($image->album_id, $image->album_order, $request->album_order);
            $image->album_order = $request->album_order;
        }
        if ($request->has('text')) {
            $image->text = $request->text;
        }
        if ($request->has('main_album_image')) {
            $image->main_album_image = $request->main_album_image;
        }

        $other_main_image = ImageModel::where([
            'album_id' => $image->album_id,
            'main_album_image' => 1,
        ])
            ->where('id', '!=', $image->id)
            ->first();

        if ($request->main_album_image && !is_null($other_main_image)) {
            $other_main_image->main_album_image = false;
            $other_main_image->save();
        }

        $image->save();

        return response()->noContent();
    }

    /**
     * Delete the image
     * Response 204
     */
    public function destroy(ImageModel $image)
    {
        $file_name = explode('/', $image->path)[1];
        $album_id = $image->album_id;

        Storage::delete("public/$file_name");
        Storage::delete("public/thumbnail_$file_name");
        Storage::delete("public/cover_$file_name");

        $image->delete();

        $this->recalculateImageOrder($album_id);

        return response()->noContent();
    }

    /**
     * Get the max value of album_order image data
     * Use for store
     */
    protected function getMaxImageOrder($album_id)
    {
        return ImageModel::where('album_id', $album_id)->max('album_order');
    }

    /**
     * Recalculate the album_order values by album_id image
     * Use for delete
     */
    protected function recalculateImageOrder($album_id)
    {
        $images = ImageModel::where('album_id', $album_id)->orderBy('album_order')->get();

        foreach ($images as $key => $image) {
            $image->album_order = $key;
            $image->save();
        }
    }

    /**
     * Switch the order of the image 
     * Use for update
     */
    protected function switchOtherImageOrder($album_id, $old_order, $new_order)
    {
        $image = ImageModel::where([
            'album_id' => $album_id,
            'album_order' => $new_order,
        ])->first();

        if (is_null($image)) return;

        $image->album_order = $old_order;
        $image->save();
    }
}
