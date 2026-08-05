<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): Response
    {
        if ($this->shouldRenderJsonError($request)) {
            return $this->jsonErrorResponse($request, $e);
        }

        $response = parent::render($request, $e);

        if ($this->shouldRenderInertiaErrorPage($request, $response)) {
            return Inertia::render('Error', [
                'status' => $response->getStatusCode(),
                'message' => $this->errorMessage($response->getStatusCode()),
            ])->toResponse($request)->setStatusCode($response->getStatusCode());
        }

        return $response;
    }

    private function shouldRenderJsonError(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    private function jsonErrorResponse(Request $request, Throwable $e): Response
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'data' => [
                    'errors' => $e->errors(),
                ],
            ], 422);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please provide a valid bearer token.',
                'data' => null,
            ], 401);
        }

        if ($e instanceof AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Forbidden. You do not have permission to perform this action.',
                'data' => null,
            ], 403);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
                'data' => null,
            ], 404);
        }

        $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
        $message = $this->errorMessage($status);

        if (config('app.debug') && $status >= 500) {
            $message = $e->getMessage() ?: $message;
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ], $status);
    }

    private function shouldRenderInertiaErrorPage(Request $request, Response $response): bool
    {
        return $request->header('X-Inertia')
            && in_array($response->getStatusCode(), [403, 404, 419, 500, 503], true)
            && ! $request->expectsJson();
    }

    private function errorMessage(int $status): string
    {
        return match ($status) {
            401 => 'Unauthenticated. Please sign in and try again.',
            403 => 'You do not have permission to access this page or perform this action.',
            404 => 'The requested page or record could not be found.',
            419 => 'Your session expired. Please refresh the page and sign in again.',
            422 => 'The submitted data was invalid.',
            429 => 'Too many requests. Please slow down and try again.',
            503 => 'BookHive is temporarily unavailable. Please try again shortly.',
            default => 'Something went wrong while processing your request.',
        };
    }
}
