<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function handle(Request $request, Closure $next, $role)
{
    // 1. Is the user logged in?
    if (!auth()->check()) {
        return response()->json(['message' => 'Please login first.'], 401);
    }

    // 2. Does their role match the one required by the route?
    if (auth()->user()->role !== $role) {
        return response()->json([
            'message' => "Access denied. Only {$role}s allowed."
        ], 403);
    }

    return $next($request);
}
}
