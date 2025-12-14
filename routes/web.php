<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::get('/', function () {
    return view('welcome');
});
// Route::get('/test', function () {
//     return response()->json(['message' => 'Hello World']);
// });


// Route::post('/register', function () {
//     return response()->json(['message' => 'API working']);
// });