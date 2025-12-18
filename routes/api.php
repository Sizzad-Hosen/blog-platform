<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmailVerificationController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'send']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');


Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {

   
        Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);


        Route::patch('posts/{post}/restore', [PostController::class, 'restore']);
        Route::delete('posts/{post}/force', [PostController::class, 'forceDelete']);

        Route::patch('comments/{comment}/restore', [CommentController::class, 'restore']);

        Route::delete('comments/{comment}/force', [CommentController::class, 'forceDelete']);

        Route::get('/users', [UserController::class, 'users']);

        
    });


Route::middleware(['auth:sanctum', 'role:user'])->group(function () {


    Route::put('posts/{post}', [PostController::class, 'update']);
    Route::delete('posts/{post}', [PostController::class, 'softDelete']);


    Route::patch('posts/{post}/comments/{comment}', [CommentController::class, 'update']);

    Route::delete('comments/{comment}', [CommentController::class, 'softDelete']);



});


Route::middleware('auth:sanctum')->group(function () {


    Route::post('posts', [PostController::class, 'store']);

    Route::post('posts/{post}/comments', [CommentController::class, 'store']);
    Route::get('/profile', [UserController::class, 'profile']);

});



Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);


Route::get('posts', [PostController::class, 'index']);
Route::get('posts/{post}', [PostController::class, 'show']);

Route::get('posts/{post}/comments', [CommentController::class, 'index']);
