<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Resources\AlbumResource;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'per_page' => 'filled|integer|min:-1',
            'sort_by' => 'filled',
        ]);

        $albums = Album::with(['images' => function ($query) {
            $query->mainAlbumImage();
        }]);

        if ($request->has('hide')) {
            $albums = $albums->where('hide', $request->hide);
        }

        $sort_by = ($request->has('sort_by')) ? $request->sort_by : 'desc';

        $albums = $albums->orderBy('date', $sort_by)->get();

        $per_page = $request->has('per_page') ? $request->per_page : 12;

        $paginated = PaginationHelper::paginate($albums, $per_page);

        return AlbumResource::collection($paginated);
    }

    /**
     * Get minified data from all albums for client pathline
     */
    public function indexSimple()
    {
        return Album::where('hide', 0)
            ->select('id', 'km_total')
            ->orderBy('km_total', 'asc')
            ->get();
    }

    /**
     * Get one album with associated comments and images
     * Response 200 with data
     */
    public function show(Album $album)
    {
        $album->next_album = Album::whereDate('date', '>', $album->date)
            ->orderBy('date')
            ->first();

        $album->previous_album = Album::whereDate('date', '<', $album->date)
            ->orderBy('date', 'desc')
            ->first();

        return $album->load(['comments', 'images', 'videos']);
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
            'km_step' => 'required|numeric|min:0|max:100',
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
            'km_step' => 'filled|numeric|min:0|max:100',
            'hide' => 'filled|boolean',
        ]);

        $album->update($request->all());

        $this->recalculateAlbumsTotalKilometer();

        return $album->refresh()->load(['comments', 'images', 'videos']);
    }

    /**
     * Delete the album passed in parameter
     * Response 204
     */
    public function destroy(Album $album)
    {
        foreach ($album->images as $image) {
            $file_name = explode('/', $image->path)[1];
            Storage::delete("public/$file_name");
        }

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
