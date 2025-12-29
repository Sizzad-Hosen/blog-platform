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


        public function show(Post $post)
    {
        return response()->json([
            'success' => true,
            'post' => $post->load(['user', 'category'])
        ]);
    }
    

public function index(Request $request)
{
    // ✅ Validate query params
    $validated = $request->validate([
        'q'        => 'nullable|string|max:255',
        'per_page' => 'nullable|integer|min:1|max:50',
    ]);

    $perPage = $validated['per_page'] ?? 10;

    // ✅ Base query
    $query = Post::with(['user', 'category', 'comments']);

    // ✅ Search filter
    if (!empty($validated['q'])) {
        $query->where(function ($q) use ($validated) {
            $q->where('title', 'like', '%' . $validated['q'] . '%')
              ->orWhere('body', 'like', '%' . $validated['q'] . '%');
        });
    }

    // ✅ Pagination
    $posts = $query->latest()
                   ->paginate($perPage)
                   ->withQueryString();

    // ✅ Response (custom format + PostCollection)
    return response()->json([
        'success' => true,
        'data'    => PostCollection::make($posts)->collection,
        'meta'    => [
            'current_page' => $posts->currentPage(),
            'per_page'     => $posts->perPage(),
            'total'        => $posts->total(),
            'last_page'    => $posts->lastPage(),
        ],
    ]);
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
