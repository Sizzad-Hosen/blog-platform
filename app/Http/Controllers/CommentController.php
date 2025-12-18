<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;
use App\Http\Resources\CommentResource;
use App\Traits\ResponseTrait;


class CommentController extends Controller
{
    use ResponseTrait;

  
public function index(Request $request, Post $post)
{
    $limit = $request->get('limit', 5);
    
    $comments = $post->comments()
                     ->with('user')
                     ->latest()
                     ->take($limit)
                     ->get();

    return $this->sendResponse(
        CommentResource::collection($comments),
        'Comments retrieved successfully'
    );
}


    public function store(Request $request, Post $post)
    {
        $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $comment = $post->comments()->create([
            'body'    => $request->body,
            'user_id' => $request->user()->id,
        ]);

        return $this->sendResponse(new CommentResource($comment), 'Comment added successfully', 201);
    }


    public function update(Request $request, Post $post, Comment $comment)
    {
        if ($request->user()->id !== $comment->user_id) {
            return $this->sendError('Unauthorized', [], 403);
        }

        $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $comment->update(['body' => $request->body]);

        return $this->sendResponse(new CommentResource($comment), 'Comment updated successfully');
    }


    public function destroy(Request $request, Comment $comment)
    {
        

        $comment->delete();

        return $this->sendResponse(null, 'Comment deleted successfully');
    }



    
}
