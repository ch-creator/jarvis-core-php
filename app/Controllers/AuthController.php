<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

final class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function register(Request $request): Response
    {
        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email is required.';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($errors !== []) {
            return $this->error('Validation failed.', 422, $errors);
        }

        try {
            $result = $this->authService->register($name, $email, $password);
        } catch (\RuntimeException $exception) {
            return $this->error($exception->getMessage(), 409);
        }

        return $this->success($result, 'Registration successful.', 201);
    }

    public function login(Request $request): Response
    {
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            return $this->error('Email and password are required.', 422);
        }

        $result = $this->authService->login($email, $password);

        if ($result === null) {
            return $this->error('Invalid credentials.', 401);
        }

        return $this->success($result, 'Login successful.');
    }

    public function refresh(Request $request): Response
    {
        $refreshToken = (string) $request->input('refresh_token', '');

        if ($refreshToken === '') {
            return $this->error('Refresh token is required.', 422);
        }

        $result = $this->authService->refresh($refreshToken);

        if ($result === null) {
            return $this->error('Invalid or expired refresh token.', 401);
        }

        return $this->success($result, 'Token refreshed.');
    }

    public function logout(Request $request): Response
    {
        $refreshToken = (string) $request->input('refresh_token', '');

        if ($refreshToken !== '') {
            $this->authService->logout($refreshToken);
        }

        return $this->success([], 'Logged out.');
    }

    public function me(Request $request): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $this->error('Authentication required.', 401);
        }

        return $this->success([
            'user' => (array) $user,
        ]);
    }
}
