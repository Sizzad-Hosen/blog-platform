<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthenticated. Please login first.'
            ], 401);
        }


        if (Auth::user()->role === 'admin') {
            return $next($request);
        }

 
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.',
                'user_role' => Auth::user()->role
            ], 403);
        }

   
        flash()->error('You do not have permission to access this route');
        return redirect()->back();
    }
}