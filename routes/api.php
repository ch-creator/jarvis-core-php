<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HealthController;
use App\Middleware\AuthMiddleware;

/** @var App\Core\Router $router */

$router->group(['prefix' => '/api/v1'], function (App\Core\Router $router): void {
    $router->get('/health', [HealthController::class, 'index']);

    $router->group(['prefix' => '/auth'], function (App\Core\Router $router): void {
        $router->post('/register', [AuthController::class, 'register']);
        $router->post('/login', [AuthController::class, 'login']);
        $router->post('/refresh', [AuthController::class, 'refresh']);
        $router->post('/logout', [AuthController::class, 'logout']);
        $router->get('/me', [AuthController::class, 'me'], [AuthMiddleware::class]);
    });
});
