<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Store new comment in album passed in request
     */
    public function store(Request $request)
    {
        $request->validate([
            'album_id' => 'required|exists:albums,id',
            'author' => 'required|string',
            'text' => 'required|string',
        ]);

        return Comment::create($request->all());
    }

    /**
     * Add one to report value for reported comment
     */
    public function report(Comment $comment)
    {
        $comment->report++;
        $comment->save();

        return $comment;
    }

    /**
     * Update comment passed in parameter with data in request 
     */
    public function update(Request $request, Comment $comment)
    {
        $request->validate([
            'author' => 'required|string',
            'text' => 'required|string',
            'report' => 'required|integer',
        ]);

        $comment->update($request->all());

        return $comment;
    }

    /**
     * Delete the comment passed in parameter
     */
    public function delete(Comment $comment)
    {
        $comment->delete();
    }

    /**
     * Get all comments where report column is over 0
     */
    public function reports()
    {
        return Comment::where('report', '>', 0)->get();
    }
}
