<?php

namespace App\Http\Controllers;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Resources\PostResource;

class PostController extends Controller
{
    use ResponseTrait;

    public function index()
    {
        $posts = Post::with(['user', 'category', 'comments.user'])->get();
        return $this->sendResponse(PostResource::collection($posts), 'Posts retrieved successfully');
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

        return $this->sendResponse(new PostResource($post), 'Post created successfully', 201);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

       

        $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'body'        => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);

        $post->update($request->only(['title', 'body', 'category_id']));

        return $this->sendResponse(new PostResource($post), 'Post updated successfully');
    }

    public function destroy(Request $request, $id)
    {
      $post = Post::withTrashed()->findOrFail($id);
      $post->forceDelete();

    return response()->json([
        'status' => true,
        'message' => 'Post permanently deleted'
    ], 200);
    }


    public function softDelete($id)
{
    $post = Post::findOrFail($id);
    $post->delete();

    return response()->json([
        'status' => true,
        'message' => 'Post soft deleted successfully'
    ], 200);
}



}
