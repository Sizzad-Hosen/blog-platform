<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Middleware\AdminMiddleware;


// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'send']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

// Admin access only
Route::prefix('admin')->AdminMiddleware(['auth:sanctum', 'role:admin'])->group(function () {
// Category    
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
// Posts  
        Route::patch('posts/{post}/restore', [PostController::class, 'restore']);
        Route::delete('posts/{post}/force', [PostController::class, 'forceDelete']);
// Comments
        Route::patch('comments/{comment}/restore', [CommentController::class, 'restore']);
        Route::delete('comments/{comment}/force', [CommentController::class, 'forceDelete']);
        Route::get('/users', [UserController::class, 'users']);

    });

// User access only
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {

// Posts
    Route::put('posts/{post}', [PostController::class, 'update']);
    Route::delete('posts/{post}', [PostController::class, 'softDelete']);

// Comments
    Route::patch('posts/{post}/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'softDelete']);

});

// Login user access
Route::middleware('auth:sanctum')->group(function () {

    Route::post('posts', [PostController::class, 'store']);
    Route::post('posts/{post}/comments', [CommentController::class, 'store']);
    Route::get('/profile', [UserController::class, 'profile']);

});


// Public Routes

// Category
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);

// Posts
Route::get('posts', [PostController::class, 'index']);
Route::get('posts/{post}', [PostController::class, 'show']);

// Comments
Route::get('posts/{post}/comments', [CommentController::class, 'index']);

// Add this to routes/api.php
Route::get('/debug-routes', function () {
    $routes = collect(\Route::getRoutes()->getRoutes())
        ->map(function ($route) {
            return [
                'method' => implode('|', $route->methods()),
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'middleware' => $route->gatherMiddleware()
            ];
        })
        ->filter(function ($route) {
            return str_starts_with($route['uri'], 'api/');
        })
        ->values();
    
    return response()->json($routes);
});




// routes/api.php
Route::post('/test-create', function (Request $request) {
    try {
        \Log::info('Test create route called', $request->all());
        
        // Test 1: Check authentication
        if (!auth()->check()) {
            return response()->json([
                'error' => 'Not authenticated',
                'auth_check' => false
            ], 401);
        }
        
        $user = auth()->user();
        
        // Test 2: Check user data
        \Log::info('User data', [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role ?? 'NULL',
            'has_role' => isset($user->role)
        ]);
        
        // Test 3: Try to create category directly
        $category = \App\Models\Category::create([
            'name' => 'Test Category ' . time(),
            'slug' => 'test-category-' . time(),
            'description' => 'Test description'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Category created via test route',
            'category' => $category,
            'user' => [
                'id' => $user->id,
                'role' => $user->role
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Test create error: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'error' => $e->getMessage(),
            'hint' => 'Check logs for details'
        ], 500);
    }
})->middleware('auth:sanctum');


Route::get('/debug-all', function () {
    $debug = [];
    
    try {
        // 1. Check Laravel basics
        $debug['laravel'] = [
            'version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug')
        ];
        
        // 2. Check database
        $debug['database'] = [
            'connection' => DB::connection()->getDatabaseName(),
            'connected' => DB::connection()->getPdo() ? 'YES' : 'NO',
            'categories_table_exists' => Schema::hasTable('categories') ? 'YES' : 'NO'
        ];
        
        // 3. Check categories table
        if (Schema::hasTable('categories')) {
            $debug['categories_table'] = [
                'columns' => Schema::getColumnListing('categories'),
                'row_count' => DB::table('categories')->count(),
                'sample_data' => DB::table('categories')->first()
            ];
        }
        
        // 4. Check Category model
        $debug['category_model'] = [
            'exists' => class_exists('App\Models\Category') ? 'YES' : 'NO',
            'fillable' => class_exists('App\Models\Category') ? (new \App\Models\Category)->getFillable() : 'Model not found'
        ];
        
        // 5. Check authentication
        $debug['authentication'] = [
            'user_authenticated' => auth()->check() ? 'YES' : 'NO',
            'current_user' => auth()->user() ? [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'role' => auth()->user()->role ?? 'NULL'
            ] : 'No user'
        ];
        
        // 6. Try to create a test category
        try {
            $testCategory = \App\Models\Category::create([
                'name' => 'Debug Test ' . time(),
                'slug' => 'debug-test-' . time(),
                'description' => 'Test from debug'
            ]);
            $debug['test_creation'] = [
                'success' => 'YES',
                'category_id' => $testCategory->id,
                'category_data' => $testCategory
            ];
            // Clean up
            $testCategory->delete();
        } catch (\Exception $e) {
            $debug['test_creation'] = [
                'success' => 'NO',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
        
    } catch (\Exception $e) {
        $debug['debug_error'] = $e->getMessage();
    }
    
    return response()->json($debug);
});