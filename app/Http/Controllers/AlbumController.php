<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    /**
     * Get all albums with main album image
     * Response 200 with data
     */
    public function index(Request $request)
    {
        $request->validate([
            'hide' => 'filled|boolean',
        ]);

        $albums = Album::with(['images' => function ($query) {
            $query->mainAlbumImage();
        }]);

        if ($request->has('hide')) {
            $albums = $albums->where('hide', $request->hide);
        }

        return $albums->orderBy('date', 'DESC')->get();
    }

    /**
     * Get one album with associated comments
     * Response 200 with data
     */
    public function show(Album $album)
    {
        return $album->load(['comments', 'images']);
    }

    /**
     * Create new album with data in request
     * Response 200 with data
     */
    public function store(Request $request)
    {
        $request->validate([
            'text' => 'nullable|string',
            'date' => 'required|date',
            'place_departure' => 'required|string',
            'place_arrival' => 'required|string',
            'km_step' => 'required|integer|max:100',
            'km_total' => 'filled|integer|max:2000',
        ]);

        return Album::create($request->all());
    }

    /**
     * Update album passed in parameter with data in request
     * Response 204
     */
    public function update(Request $request, Album $album)
    {
        $request->validate([
            'text' => 'filled|string',
            'date' => 'filled|date',
            'place_departure' => 'filled|string',
            'place_arrival' => 'filled|string',
            'km_step' => 'filled|integer|max:100',
            'km_total' => 'filled|integer|max:2000',
            'hide' => 'filled|boolean',
        ]);

        $album->update($request->all());

        return response()->noContent();
    }

    /**
     * Delete the album passed in parameter
     * Response 204
     */
    public function destroy(Album $album)
    {
        $album->delete();

        return response()->noContent();
    }
}
