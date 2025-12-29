<?php

namespace App\Http\Controllers;

use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostController extends Controller
{
    use ResponseTrait;
    use AuthorizesRequests;
public function index(Request $request)
{
    try {
        // Validate query params
        $request->validate([
            'q' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $perPage = $request->get('per_page', 10);

        // Query posts with relationships
        $query = Post::with(['user', 'category', 'comments']);

        // Search filter
        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('body', 'like', "%{$keyword}%");
            });
        }

        // Only non-deleted posts (if using SoftDeletes)
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses(Post::class))) {
            $query->whereNull('deleted_at');
        }

        // Paginate results
        $posts = $query->latest()->paginate($perPage)->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage(),
            ]
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
}



public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'body' => 'required|string',
        'category_id' => 'required|exists:categories,id',
    ]);

    $postData = [
        'title' => $validated['title'],
        'body' => $validated['body'],
        'category_id' => $validated['category_id'],
        'user_id' => $request->user()->id,
    ];

    $post = Post::create($postData);

    return response()->json([
        'success' => true,
        'data' => $post,
        'message' => 'Post created successfully'
    ], 201);
}
public function update(Request $request, Post $post)
{
    $this->authorize('update', $post);

    $validated = $request->validate([
        'title' => 'sometimes|required|string|max:255',
        'body' => 'sometimes|required|string',
        'category_id' => 'sometimes|required|exists:categories,id',
    ]);

    $post->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'Post updated successfully',
        'post' => $post
    ]);
}


    public function restore($postId)
    {
        $post = Post::onlyTrashed()->findOrFail($postId);

        $this->authorize('restore', $post);

        $post->restore();

        return response()->json([
            'status' => true,
            'message' => 'Post restored successfully'
        ]);
    }

    public function forceDelete($postId)
    {
        $post = Post::withTrashed()->findOrFail($postId);

        $this->authorize('forceDelete', $post);

        $post->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'Post permanently deleted'
        ]);
    }
}
