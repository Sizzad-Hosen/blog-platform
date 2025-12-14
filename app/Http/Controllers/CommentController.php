<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;


class CommentController extends Controller
{
   

    public function store(Request $request, $postId)
    {
        $request->validate([
            'body' => 'required|string',
        ]);

        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['message'=>'Post not found'], 404);
        }

        $comment = Comment::create([
            'body' => $request->body,
            'post_id' => $postId,
            'user_id' => $request->user()->id
        ]);

        return response()->json(['message'=>'Comment added', 'comment'=>$comment], 201);
    }



public function update(Request $request, $id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['message'=>'Comment not found'], 404);
        }


        if ($request->user()->id !== $comment->user_id ) {
            return response()->json(['message'=>'Unauthorized'], 403);
        }

        $request->validate([
            'body' => 'required|string',
        ]);

        $comment->update($request->only('body'));

        return response()->json(['message'=>'Comment updated', 'comment'=>$comment]);
    }



}
