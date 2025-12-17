<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EmailVerificationController;
use Illuminate\Support\Facades\Route;



// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


Route::post('/forgot-password', [AuthController::class, 'send']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);


// Admin  only access
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Categories
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
});




    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');


// Authenticated user 
Route::middleware('auth:sanctum')->group(function () {


    // Categories (read-only for all  users)
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    // Posts
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::delete('/posts/{id}', [PostController::class, 'softDelete']);



    // Comments
    Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
    Route::post('/posts/{postId}/comments', [CommentController::class, 'store']);
    Route::patch('/posts/{postId}/comments/{commentId}', [CommentController::class, 'update']);
    Route::delete('/comments/{commentId}', [CommentController::class, 'destroy']);
});



    Route::get('/search', [PostController::class, 'search']);