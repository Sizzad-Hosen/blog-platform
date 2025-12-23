<?php

namespace App\Http\Controllers;

use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use Illuminate\Http\Response;

class PostController extends Controller
{
    use ResponseTrait;

public function index(Request $request)
{
    try {
        $request->validate([
            'q' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $perPage = $request->get('per_page', 10);

        $query = Post::query()->with(['user', 'category', 'comments']);

        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('body', 'like', "%{$keyword}%");
            });
        }

        $posts = $query->latest()->paginate($perPage)->withQueryString();

        return new PostCollection($posts);
    } catch (\Exception $e) {
        \Log::error('Post index error: '.$e->getMessage());
        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
}


    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'body'        => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $post = Post::create([
            'title'       => $request->title,
            'body'        => $request->body,
            'category_id' => $request->category_id,
            'user_id'     => $request->user()->id,
        ]);

        return $this->sendResponse(new PostResource($post), 'Post created successfully', Response::HTTP_CREATED);
    }
    
public function update(Request $request, Post $post)
{
    $this->authorize('update', $post); 

    $request->validate([
        'title' => 'sometimes|required|string|max:255',
        'body' => 'sometimes|required|string',
        'category_id' => 'sometimes|required|exists:categories,id',
    ]);

    $post->update($request->only(['title', 'body', 'category_id']));

    return response()->json([
        'message' => 'Post updated successfully',
        'post' => $post
    ]);
}

    
    public function softDelete(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json([
            'status' => true,
            'message' => 'Post moved to trash'
        ], Response::HTTP_OK);
    }


    public function restore($postId)
    {
        $post = Post::onlyTrashed()->findOrFail($postId);

        $this->authorize('restore', $post);

        $post->restore();

        return response()->json([
            'status' => true,
            'message' => 'Post restored successfully'
        ], Response::HTTP_OK);
    }

    public function forceDelete($postId)
    {
        $post = Post::withTrashed()->findOrFail($postId);

        $this->authorize('forceDelete', $post);

        $post->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'Post permanently deleted'
        ], Response::HTTP_OK);
    }

}
