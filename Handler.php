<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * Adds custom JSON responses for common exception types.
     */
    public function register(): void
    {
        // Handle unauthenticated requests
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'status' => 401
                ], 401);
            }
        });

        // Handle validation exceptions
        $this->renderable(function (ValidationException $e, $request) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
                'status' => 422
            ], 422);
        });

        // Handle model not found exceptions
        $this->renderable(function (ModelNotFoundException $e, $request) {
            return response()->json([
                'message' => 'Resource not found.',
                'status' => 404
            ], 404);
        });

        // Handle not found HTTP exceptions
        $this->renderable(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'message' => 'The requested resource was not found.',
                'status' => 404
            ], 404);
        });

        // Handle method not allowed HTTP exceptions
        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return response()->json([
                'message' => 'Method not allowed.',
                'status' => 405
            ], 405);
        });

        // Handle generic HTTP exceptions
        $this->renderable(function (HttpExceptionInterface $e, $request) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => $e->getStatusCode()
            ], $e->getStatusCode());
        });
    }
}
