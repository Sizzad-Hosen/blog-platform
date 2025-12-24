<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

use App\Models\User;

class EmailVerificationController extends Controller
{
 
public function verify(Request $request, $id, $hash)
{
    $user = User::findOrFail($id);

    if (! hash_equals(sha1($user->email), $hash)) {
        return response()->json(['message' => 'Invalid verification link'], 403);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified']);
    }

    $user->markEmailAsVerified();

    return response()->json(['message' => 'Email verified successfully']);
}

   public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
             return response()->json(['message' => 'Email already verified']);
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent!');
    }

}
