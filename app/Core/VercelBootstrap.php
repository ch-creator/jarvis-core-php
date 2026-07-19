<?php

declare(strict_types=1);

namespace App\Core;

final class VercelBootstrap
{
    public static function apply(): void
    {
        if (!self::isVercel()) {
            return;
        }

        self::setEnv('DB_CONNECTION', 'sqlite');
        self::setEnv('DB_SQLITE_PATH', '/tmp/jarvis.sqlite');
        self::setEnv('LOG_PATH', '/tmp/jarvis.log');
        self::setEnv('APP_DEBUG', 'false');
    }

    public static function isVercel(): bool
    {
        return filter_var(env('VERCEL', false), FILTER_VALIDATE_BOOL)
            || getenv('VERCEL') === '1';
    }

    private static function setEnv(string $key, string $value): void
    {
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("{$key}={$value}");
    }
}
