<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CategoryController extends Controller
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

            $query = Category::query();

            if ($request->filled('q')) {
                $keyword = $request->q;
                $query->where('name', 'like', "%{$keyword}%");
            }

            $categories = $query->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => CategoryResource::collection($categories),
                'meta' => [
                    'current_page' => $categories->currentPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                    'last_page' => $categories->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching categories'
            ], 500);
        }
    }

    public function show(Category $category)
    {
        return $this->sendResponse(new CategoryResource($category), 'Category retrieved successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
        ]);
        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return $this->sendResponse(
            new CategoryResource($category), 
            'Category created successfully', 
            Response::HTTP_CREATED
        );
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'sometimes|nullable|string',
        ]);

        $data = $request->only(['name', 'description']);
        
        // Update slug if name changed
        if ($request->has('name') && $request->name !== $category->name) {
            $data['slug'] = Str::slug($request->name);
        }

        $category->update($data);

        return $this->sendResponse(new CategoryResource($category), 'Category updated successfully');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return $this->sendResponse(null, 'Category deleted successfully', Response::HTTP_NO_CONTENT);
    }
}