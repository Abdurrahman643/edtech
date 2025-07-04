<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CustomAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->bearerToken()) {
            return response()->json([
                'message' => 'No token provided',
                'status' => 401
            ], 401);
        }

        $token = PersonalAccessToken::findToken($request->bearerToken());

        if (!$token) {
            return response()->json([
                'message' => 'Invalid token',
                'status' => 401
            ], 401);
        }

        // Check token expiration (24 hours)
        if (Carbon::parse($token->created_at)->addHours(24)->isPast()) {
            $token->delete();
            return response()->json([
                'message' => 'Token has expired',
                'status' => 401
            ], 401);
        }

        // Update last used timestamp
        $token->last_used_at = Carbon::now();
        $token->save();

        return $next($request);
    }
}
