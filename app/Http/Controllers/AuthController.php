<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Password;
class AuthController extends Controller
{
  
    // REGISTER

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'nullable|in:admin,user',
        ]);

        $user = User::create($data);

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error sending verification email',
                'error'   => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'User registered successfully. Please check your email to verify your account.',
            'user'    => new UserResource($user)
        ], 201);
    }


    // LOGIN

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid email or password'
            ], 401);
        }

        $user = Auth::user();

        // if (!$user->hasVerifiedEmail()) {
        //     return response()->json([
        //         'message' => 'Please verify your email before login'
        //     ], 403);
        // }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token
        ]);
    }



  public function send(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send reset link',
                'error'   => $e->getMessage()
            ], 500);
        }

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent to your email.'
            ]);
        }

        return response()->json([
            'message' => 'Unable to send password reset link.'
        ], 400);
    }

  
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
              
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password reset successful. You can now login with your new password.'
            ]);
        }

        return response()->json([
            'message' => 'Invalid or expired token.'
        ], 400);
    }


    // LOGOUT

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
