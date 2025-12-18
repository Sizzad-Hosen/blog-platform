<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    use ResponseTrait;

  

    public function index(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $perPage = $request->get('per_page', 10);

        $query = Category::query()->with(['posts.user']);

        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%");
               
            });
        }

        $categories = $query->latest()->paginate($perPage)->withQueryString();

        return new CategoryCollection($categories);
    }


    public function show(Category $category)
    {
        return $this->sendResponse(new CategoryResource($category), 'Category retrieved successfully');
    }

  
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($request->only(['name', 'description']));

        return $this->sendResponse(new CategoryResource($category), 'Category created successfully', Response::HTTP_CREATED);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
        ]);

        $category->update($request->only(['name', 'description']));

        return $this->sendResponse(new CategoryResource($category), 'Category updated successfully');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return $this->sendResponse(null, 'Category deleted successfully', Response::HTTP_NO_CONTENT);
    }
}
