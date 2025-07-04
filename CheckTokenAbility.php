<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenAbility
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        if (!$request->user() || !$request->user()->tokenCan($ability)) {
            return response()->json([
                'message' => 'You do not have the required permissions for this action.'
            ], 403);
        }

        return $next($request);
    }
}
