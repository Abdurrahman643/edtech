<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonResponseMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force accept header to application/json
        $request->headers->set('Accept', 'application/json');

        // Handle preflight requests
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }

        // Set content type to application/json
        $response->headers->set('Content-Type', 'application/json');

        // Add CORS headers
        $response->headers->set('Access-Control-Allow-Origin', env('FRONTEND_URL', '*'));
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        // Convert non-JSON responses to JSON
        if (!$this->isJsonResponse($response)) {
            $originalContent = $response->getContent();
            $statusCode = $response->getStatusCode();
            
            $jsonContent = [
                'message' => $originalContent,
                'status' => $statusCode
            ];
            
            $response->setContent(json_encode($jsonContent));
        }

        return $response;
    }

    /**
     * Check if the response is already JSON
     */
    private function isJsonResponse($response): bool
    {
        if (empty($response->getContent())) {
            return true;
        }

        json_decode($response->getContent());
        return json_last_error() === JSON_ERROR_NONE;
    }
}
