<?php

declare(strict_types=1);

namespace App\Core;

final class MigrationRunner
{
    public static function runIfNeeded(Database $database, Config $config): void
    {
        try {
            $database->query('SELECT 1 FROM users LIMIT 1');

            return;
        } catch (\Throwable) {
            self::run($database, $config);
        }
    }

    public static function run(Database $database, Config $config): void
    {
        $driver = (string) $config->get('database.default', 'sqlite');
        $migrationPath = BASE_PATH . "/database/migrations/001_initial_schema.{$driver}.sql";

        if (!is_file($migrationPath)) {
            throw new \RuntimeException("Migration file not found for driver [{$driver}].");
        }

        $sql = file_get_contents($migrationPath);

        if ($sql === false) {
            throw new \RuntimeException('Migration file could not be read.');
        }

        $statements = array_filter(array_map('trim', explode(';', $sql)));
        $database->migrate($statements);
    }
}
