<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserController;

// Admin access only
Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // Categories
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
    // Comments
    Route::patch('comments/{comment}/restore', [CommentController::class, 'restore']);
    Route::delete('comments/{comment}/force', [CommentController::class, 'forceDelete']);

    // Users
    Route::get('users', [UserController::class, 'users']);
});
