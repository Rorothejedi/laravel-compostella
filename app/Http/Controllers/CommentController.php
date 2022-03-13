<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Store new comment in album passed in request
     * Response 200 with data
     */
    public function store(Request $request)
    {
        $request->validate([
            'g-recaptcha-response' => 'required|captcha',
            'album_id' => 'required|exists:albums,id',
            'author' => 'required|string',
            'text' => 'required|string',
        ]);

        return Comment::create($request->all());
    }

    /**
     * Get all comments where report column is over 0
     * Response 200 with data
     */
    public function reports()
    {
        return Comment::where('report', '>', 0)->get();
    }

    /**
     * Add one to report value for reported comment
     * Response 204
     */
    public function report(Comment $comment)
    {
        $comment->report++;
        $comment->save();

        return response()->noContent();
    }

    /**
     * Add one to love value for liked comment
     * Response 204
     */
    public function love(Comment $comment)
    {
        $comment->love++;
        $comment->save();

        return response()->noContent();
    }

    /**
     * Remove one to love value for liked comment
     * Response 204
     */
    public function unlove(Comment $comment)
    {
        if ($comment->love > 0) {
            $comment->love--;
            $comment->save();
        }

        return response()->noContent();
    }

    /**
     * Update comment passed in parameter with data in request 
     * Response 204
     */
    public function update(Request $request, Comment $comment)
    {
        $request->validate([
            // 'author' => 'required|string',
            // 'text' => 'required|string',
            'report' => 'filled|integer',
        ]);

        $comment->update($request->all());

        return response()->noContent();
    }

    /**
     * Delete the comment passed in parameter
     * Response 204
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();

        return response()->noContent();
    }
}
