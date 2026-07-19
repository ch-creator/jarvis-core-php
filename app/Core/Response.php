<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    /** @param array<string, mixed> $data */
    public function __construct(
        private readonly mixed $data = null,
        private readonly int $status = 200,
        private readonly array $headers = ['Content-Type' => 'application/json'],
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function json(array $data, int $status = 200, array $headers = []): self
    {
        return new self($data, $status, array_merge(['Content-Type' => 'application/json'], $headers));
    }

    public static function noContent(int $status = 204): self
    {
        return new self(null, $status, ['Content-Type' => 'application/json']);
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        if ($this->status === 204) {
            return;
        }

        echo json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
