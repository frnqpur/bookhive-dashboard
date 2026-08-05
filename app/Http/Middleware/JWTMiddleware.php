<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return $this->error('Authenticated user could not be found for this token.', 401);
            }

            if (($user->status ?? 'active') !== 'active') {
                return $this->error('This account is disabled. Please contact the demo owner.', 403);
            }

            auth()->setUser($user);
            $request->setUserResolver(fn () => $user);
        } catch (TokenExpiredException) {
            return $this->error('JWT token has expired. Please log in again.', 401, ['code' => 'token_expired']);
        } catch (TokenInvalidException) {
            return $this->error('JWT token is invalid.', 401, ['code' => 'token_invalid']);
        } catch (JWTException) {
            return $this->error('JWT token is missing. Send an Authorization: Bearer <token> header.', 401, ['code' => 'token_missing']);
        }

        return $next($request);
    }

    /**
     * @param array<string, mixed>|null $data
     */
    private function error(string $message, int $status, ?array $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
