<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;

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
            $name = time() . rand(1, 100) . '.' . $file->extension();
            $file->storeAs('public', $name);

            $image = new Image();

            $image->album_id = $request->album_id;
            $image->path = "storage/$name";

            $image->save();
        }

        return response([
            'message' => 'Images uploaded succesfully'
        ]);
    }

    /**
     * Update the image data
     */
    public function update(Request $request, Image $image)
    {
        $request->validate([
            'text' => 'nullable|string',
            'main_album_image' => 'required|boolean',
        ]);

        $image->text = $request->text;
        $image->main_album_image = $request->main_album_image;
        $image->save();

        return $image;
    }
}
