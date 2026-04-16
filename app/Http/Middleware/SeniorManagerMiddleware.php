<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeniorManagerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the logged-in user has the 'SeniorManager' role
        if (Auth::user() && Auth::user()->role === 'SeniorManager') {
            return $next($request);  // Proceed with the request if user is a Senior Manager
        }

        // Return unauthorized response if user is not a Senior Manager
        return response()->json(['message' => 'You are not authorized to perform this action'], 403);
    }
}