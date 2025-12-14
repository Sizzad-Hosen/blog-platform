<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{

    //  Create category (admin only)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

     
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $category = Category::create($request->only('name','description'));

       return response()->json([
        'message' => 'Category created successfully',
        'category' => $category
    ], 201);
         
    }

   // List all categories
    public function index()
    {
        $AllCategory = Category::all();

        return response()->json($AllCategory);
    }

    // Show single category
    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return response()->json($category);
    }

    public function destroy(){

    }
}
