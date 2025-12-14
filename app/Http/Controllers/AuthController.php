<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    //     public function register(Request $request)
    // {
    //     return response()->json(['message' => 'Hello world']);
    // }

    // public function login(Request $request)
    // {
    //     return response()->json(['message' => 'Logged in']);
    // }
 
    public function register(Request $request){
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users',
            'password'=>'required|string|min:6',
            'role'=>'in:admin,user'
        ]);

        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        

          return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function login(Request $request){
        $credentials = $request->validate([
            'email'=>'required|email',
            'password'=>'required|string'
        ]);

      if(!Auth::attempt($credentials)){
    $userExists = User::where('email', $request->email)->exists();
    if(!$userExists){
        return response()->json(['message'=>'User not found'], 404);
    }
    return response()->json(['message'=>'Password incorrect'], 401);
}


        $user = Auth::user();
        $token = $user->createToken('web-app')->plainTextToken;
     

        return response()->json([
            'user'=>$user,
            'token'=>$token
        ]);
    }

    // Logout user
    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json(['message'=>'Logged out']);
    }
}
