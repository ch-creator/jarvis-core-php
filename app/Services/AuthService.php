<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\Logger;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class AuthService
{
    public function __construct(
        private readonly Config $config,
        private readonly Database $database,
        private readonly Logger $logger,
    ) {
    }

    public function register(string $name, string $email, string $password): array
    {
        $userModel = new User($this->database);

        if ($userModel->findByEmail($email) !== null) {
            throw new \RuntimeException('Email is already registered.');
        }

        $user = $userModel->create($name, $email, password_hash($password, PASSWORD_DEFAULT));
        $tokens = $this->issueTokens((int) $user['id'], $email);

        $this->logger->info('User registered', ['email' => $email]);

        return [
            'user' => $userModel->publicProfile($user),
            'tokens' => $tokens,
        ];
    }

    public function login(string $email, string $password): ?array
    {
        $userModel = new User($this->database);
        $user = $userModel->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password'])) {
            return null;
        }

        $tokens = $this->issueTokens((int) $user['id'], $email);

        $this->logger->info('User logged in', ['email' => $email]);

        return [
            'user' => $userModel->publicProfile($user),
            'tokens' => $tokens,
        ];
    }

    public function refresh(string $refreshToken): ?array
    {
        $record = $this->database->fetch(
            'SELECT * FROM refresh_tokens WHERE token = :token AND revoked_at IS NULL AND expires_at > :now LIMIT 1',
            [
                'token' => hash('sha256', $refreshToken),
                'now' => gmdate('Y-m-d H:i:s'),
            ]
        );

        if ($record === null) {
            return null;
        }

        $payload = $this->decodeToken($refreshToken);

        if ($payload === null || ($payload['type'] ?? '') !== 'refresh') {
            return null;
        }

        $userModel = new User($this->database);
        $user = $userModel->findById((int) $record['user_id']);

        if ($user === null) {
            return null;
        }

        $this->revokeRefreshToken($refreshToken);

        return $this->issueTokens((int) $user['id'], $user['email']);
    }

    public function logout(string $refreshToken): void
    {
        $this->revokeRefreshToken($refreshToken);
    }

    public function validateAccessToken(string $token): ?object
    {
        $payload = $this->decodeToken($token);

        if ($payload === null || ($payload['type'] ?? '') !== 'access') {
            return null;
        }

        $userModel = new User($this->database);
        $user = $userModel->findById((int) ($payload['sub'] ?? 0));

        if ($user === null) {
            return null;
        }

        return (object) $userModel->publicProfile($user);
    }

    /** @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int} */
    private function issueTokens(int $userId, string $email): array
    {
        $accessTtl = (int) $this->config->get('jwt.access_ttl', 3600);
        $refreshTtl = (int) $this->config->get('jwt.refresh_ttl', 604800);
        $now = time();

        $accessToken = $this->encodeToken([
            'iss' => $this->config->get('jwt.issuer'),
            'sub' => $userId,
            'email' => $email,
            'type' => 'access',
            'iat' => $now,
            'exp' => $now + $accessTtl,
        ]);

        $refreshToken = $this->encodeToken([
            'iss' => $this->config->get('jwt.issuer'),
            'sub' => $userId,
            'email' => $email,
            'type' => 'refresh',
            'iat' => $now,
            'exp' => $now + $refreshTtl,
        ]);

        $this->storeRefreshToken($userId, $refreshToken, $now + $refreshTtl);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $accessTtl,
        ];
    }

    /** @param array<string, mixed> $claims */
    private function encodeToken(array $claims): string
    {
        $secret = (string) $this->config->get('jwt.secret', '');
        $algorithm = (string) $this->config->get('jwt.algorithm', 'HS256');

        if ($secret === '') {
            throw new \RuntimeException('JWT secret is not configured.');
        }

        return JWT::encode($claims, $secret, $algorithm);
    }

    /** @return array<string, mixed>|null */
    private function decodeToken(string $token): ?array
    {
        try {
            $secret = (string) $this->config->get('jwt.secret', '');
            $algorithm = (string) $this->config->get('jwt.algorithm', 'HS256');

            $decoded = JWT::decode($token, new Key($secret, $algorithm));

            return (array) $decoded;
        } catch (\Throwable) {
            return null;
        }
    }

    private function storeRefreshToken(int $userId, string $refreshToken, int $expiresAt): void
    {
        $this->database->query(
            'INSERT INTO refresh_tokens (user_id, token, expires_at, created_at) VALUES (:user_id, :token, :expires_at, :created_at)',
            [
                'user_id' => $userId,
                'token' => hash('sha256', $refreshToken),
                'expires_at' => gmdate('Y-m-d H:i:s', $expiresAt),
                'created_at' => gmdate('Y-m-d H:i:s'),
            ]
        );
    }

    private function revokeRefreshToken(string $refreshToken): void
    {
        $this->database->query(
            'UPDATE refresh_tokens SET revoked_at = :revoked_at WHERE token = :token',
            [
                'revoked_at' => gmdate('Y-m-d H:i:s'),
                'token' => hash('sha256', $refreshToken),
            ]
        );
    }
}
