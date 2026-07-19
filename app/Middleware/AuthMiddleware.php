<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return Response::json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401);
        }

        $user = $this->authService->validateAccessToken($token);

        if ($user === null) {
            return Response::json([
                'success' => false,
                'message' => 'Invalid or expired token.',
            ], 401);
        }

        return $next($request->withUser($user));
    }
}
