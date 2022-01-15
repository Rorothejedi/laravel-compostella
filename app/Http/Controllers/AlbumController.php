<?php

namespace App\Http\Controllers;

use App\Http\Resources\AlbumResource;
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

        $albums = $albums->orderBy('date', 'DESC')->get();

        return AlbumResource::collection($albums);
    }

    /**
     * Get one album with associated comments and images
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
        ]);

        $album = Album::create($request->all());

        $this->recalculateAlbumsTotalKilometer();

        return $album->refresh();
    }

    /**
     * Update album passed in parameter with data in request
     * Response 200 with data
     */
    public function update(Request $request, Album $album)
    {
        $request->validate([
            'text' => 'nullable|string',
            'date' => 'filled|date',
            'place_departure' => 'filled|string',
            'place_arrival' => 'filled|string',
            'km_step' => 'filled|integer|min:0|max:100',
            'hide' => 'filled|boolean',
        ]);

        $album->update($request->all());

        $this->recalculateAlbumsTotalKilometer();

        return $album->refresh();
    }

    /**
     * Delete the album passed in parameter
     * Response 204
     */
    public function destroy(Album $album)
    {
        $album->delete();

        $this->recalculateAlbumsTotalKilometer();

        return response()->noContent();
    }

    /* 
    * Recalculation of km_total for all albums with new km_step value 
    */
    protected function recalculateAlbumsTotalKilometer()
    {
        $albums = Album::orderby('date')->get();

        $previous_km_total = 0;

        foreach ($albums as $key => $album) {
            if (!empty($albums[$key - 1])) {
                $previous_km_total =  $albums[$key - 1]->km_total;
            }

            $album->km_total = $previous_km_total + $album->km_step;
            $album->save();
        }
    }
}
