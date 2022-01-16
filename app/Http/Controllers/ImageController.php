<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            $name = time() . rand(1, 100) . '.' . $file->extension();
            $file->storeAs('public', $name);

            $image = new Image();
            $image_size = getimagesize($file);

            $image->album_id = $request->album_id;
            $image->album_order = $this->getMaxImageOrder($request->album_id) + 1;
            $image->path = "storage/$name";
            $image->width = $image_size[0];
            $image->height = $image_size[1];

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
    public function update(Request $request, Image $image)
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

        $other_main_image = Image::where([
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
    public function destroy(Image $image)
    {
        $file_name = explode('/', $image->path)[1];
        $album_id = $image->album_id;

        Storage::delete("public/$file_name");

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
        return Image::where('album_id', $album_id)->max('album_order');
    }

    /**
     * Recalculate the album_order values by album_id image
     * Use for delete
     */
    protected function recalculateImageOrder($album_id)
    {
        $images = Image::where('album_id', $album_id)->orderBy('album_order')->get();

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
        $image = Image::where([
            'album_id' => $album_id,
            'album_order' => $new_order,
        ])->first();

        if (is_null($image)) return;

        $image->album_order = $old_order;
        $image->save();
    }
}
