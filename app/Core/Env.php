<?php

declare(strict_types=1);

namespace App\Core;

use Dotenv\Dotenv;

final class Env
{
    private static bool $loaded = false;

    public static function load(string $basePath): void
    {
        if (self::$loaded) {
            return;
        }

        $envFile = $basePath . '/.env';

        if (is_file($envFile)) {
            Dotenv::createImmutable($basePath)->safeLoad();
        }

        self::$loaded = true;
    }
}
