<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    /** @param array<string, mixed> $query */
    /** @param array<string, mixed> $body */
    /** @param array<string, mixed> $headers */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query = [],
        private readonly array $body = [],
        private readonly array $headers = [],
        private readonly array $server = [],
        private readonly ?object $user = null,
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $headers = self::normalizeHeaders();
        $body = self::parseBody($method, $headers);

        return new self(
            method: $method,
            path: rtrim($uri, '/') ?: '/',
            query: $_GET,
            body: $body,
            headers: $headers,
            server: $_SERVER,
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->body;
    }

    public function bearerToken(): ?string
    {
        $authorization = $this->header('Authorization');

        if ($authorization !== null && preg_match('/Bearer\s+(\S+)/i', $authorization, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $normalized = strtolower($key);

        foreach ($this->headers as $name => $value) {
            if (strtolower($name) === $normalized) {
                return $value;
            }
        }

        return $default;
    }

    public function withUser(?object $user): self
    {
        return new self(
            method: $this->method,
            path: $this->path,
            query: $this->query,
            body: $this->body,
            headers: $this->headers,
            server: $this->server,
            user: $user,
        );
    }

    public function user(): ?object
    {
        return $this->user;
    }

    /** @return array<string, string> */
    private static function normalizeHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = (string) $value;
            }
        }

        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = (string) $_SERVER['CONTENT_TYPE'];
        }

        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['Content-Length'] = (string) $_SERVER['CONTENT_LENGTH'];
        }

        return $headers;
    }

    /** @return array<string, mixed> */
    private static function parseBody(string $method, array $headers): array
    {
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return [];
        }

        $contentType = strtolower((string) ($headers['Content-Type'] ?? ''));

        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input') ?: '';
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return $_POST;
    }
}
