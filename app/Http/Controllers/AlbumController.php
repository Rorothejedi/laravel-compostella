<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Image;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    /**
     * Get all albums with main album image
     */
    public function index()
    {
        return Album::with(['images' => function ($query) {
            $query->mainAlbumImage();
        }])->orderBy('km', 'DESC')->get();
    }

    /**
     * Get one album with associated comments
     */
    public function show(Album $album)
    {
        return $album->load(['comments', 'images']);
    }

    /**
     * Create new album with data in request
     */
    public function store(Request $request)
    {
        $request->validate([
            'text' => 'nullable|string',
            'km' => 'required|integer|max:2000',
            'departure_place' => 'required|string',
            'arrival_place' => 'required|string',
        ]);

        return Album::create($request->all());
    }

    /**
     * Update album passed in parameter with data in request
     */
    public function update(Request $request, Album $album)
    {
        $request->validate([
            'text' => 'nullable|string',
            'km' => 'required|integer|max:2000',
            'departure_place' => 'required|string',
            'arrival_place' => 'required|string',
        ]);

        return $album->update($request->all());
    }

    /**
     * Delete the album passed in parameter
     */
    public function destroy(Album $album)
    {
        $album->delete();
    }
}
