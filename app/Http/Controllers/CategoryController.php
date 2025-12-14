<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Traits\ResponseTrait;

class CategoryController extends Controller
{
    use ResponseTrait;

  
    public function index()
    {
        $categories = Category::all();
        return $this->sendResponse(CategoryResource::collection($categories), 'Categories retrieved successfully');
    }


    public function show($id)
    {
        $category = Category::findOrFail($id);
        return $this->sendResponse(new CategoryResource($category), 'Category retrieved successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($request->only('name', 'description'));

        return $this->sendResponse(new CategoryResource($category), 'Category created successfully', 201);
    }


    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
        ]);

        $category->update($request->only('name', 'description'));

        return $this->sendResponse(new CategoryResource($category), 'Category updated successfully');
    }


    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return $this->sendResponse(null, 'Category deleted successfully');
    }
}
