<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and not active
        if ($request->user() && $request->user()->status !== 'active') {
            // Revoke current token
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'message' => 'Your account is ' . $request->user()->status . '.'
            ], 403);
        }

        return $next($request);
    }
}