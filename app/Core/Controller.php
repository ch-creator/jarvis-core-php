<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /** @param array<string, mixed> $data */
    protected function success(array $data = [], string $message = 'OK', int $status = 200): Response
    {
        return Response::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /** @param array<string, mixed> $errors */
    protected function error(string $message, int $status = 400, array $errors = []): Response
    {
        return Response::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors ?: null,
        ], $status);
    }
}
