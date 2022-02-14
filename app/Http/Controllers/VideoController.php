<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    /**
     * Add one video link
     */
    public function store(Request $request)
    {
        $request->validate([
            'album_id' => 'required|exists:albums,id',
            'title' => 'filled|string',
            'link' => 'required|string',
        ]);

        $this->overrideRequestWithSimpleLink($request);

        return Video::create($request->all());
    }

    /**
     * Update video data
     * Response 204
     */
    public function update(Request $request, Video $video)
    {
        $request->validate([
            'title' => 'nullable|string',
            'link' => 'required|string',
        ]);

        if ($request->has('title')) {
            $video->title = $request->title;
        }

        $this->overrideRequestWithSimpleLink($request);
        $video->link = $request->link;
        $video->save();

        return response()->noContent();
    }

    /**
     * Delete one video link
     * Response 204
     */
    public function destroy(Video $video)
    {
        $video->delete();

        return response()->noContent();
    }

    /**
     * Retrieve simple link and write it in request
     * ex: https://youtu.be/qCd5eVtQhJE -> qCd5eVtQhJE
     */
    protected function overrideRequestWithSimpleLink($request)
    {
        $exploded_link = explode('/', $request->link);
        $last_key = count($exploded_link) - 1;

        $data['link'] =  $exploded_link[$last_key];

        $request->merge($data);
    }
}
