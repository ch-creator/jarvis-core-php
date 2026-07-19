<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private ?PDO $connection = null;

    public function __construct(private readonly Config $config)
    {
    }

    public function connection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $driver = $this->config->get('database.default', 'sqlite');
        $settings = $this->config->get("database.connections.{$driver}");

        if (!is_array($settings)) {
            throw new RuntimeException("Database connection [{$driver}] is not configured.");
        }

        try {
            $this->connection = match ($settings['driver']) {
                'sqlite' => $this->connectSqlite($settings),
                'mysql' => $this->connectMysql($settings),
                'pgsql' => $this->connectPgsql($settings),
                default => throw new RuntimeException("Unsupported database driver [{$settings['driver']}]."),
            };
        } catch (PDOException $exception) {
            throw new RuntimeException('Database connection failed: ' . $exception->getMessage(), 0, $exception);
        }

        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $this->connection;
    }

    public function query(string $sql, array $bindings = []): \PDOStatement
    {
        $statement = $this->connection()->prepare($sql);
        $statement->execute($bindings);

        return $statement;
    }

    public function fetch(string $sql, array $bindings = []): ?array
    {
        $result = $this->query($sql, $bindings)->fetch();

        return $result === false ? null : $result;
    }

    public function fetchAll(string $sql, array $bindings = []): array
    {
        return $this->query($sql, $bindings)->fetchAll();
    }

    public function execute(string $sql, array $bindings = []): bool
    {
        return $this->query($sql, $bindings)->rowCount() >= 0;
    }

    public function lastInsertId(): string
    {
        return (string) $this->connection()->lastInsertId();
    }

    /** @param array<int, string> $sqlStatements */
    public function migrate(array $sqlStatements): void
    {
        $pdo = $this->connection();

        foreach ($sqlStatements as $sql) {
            $pdo->exec($sql);
        }
    }

    private function connectSqlite(array $settings): PDO
    {
        $path = $settings['path'];

        if (!str_starts_with($path, DIRECTORY_SEPARATOR) && !preg_match('/^[A-Za-z]:\\\\/', $path)) {
            $path = BASE_PATH . '/' . ltrim($path, '/');
        }

        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        return new PDO('sqlite:' . $path);
    }

    private function connectMysql(array $settings): PDO
    {
        $this->ensureMysqlDatabaseExists($settings);

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $settings['host'],
            $settings['port'],
            $settings['database'],
            $settings['charset'] ?? 'utf8mb4'
        );

        return new PDO($dsn, $settings['username'], $settings['password']);
    }

    private function ensureMysqlDatabaseExists(array $settings): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;charset=%s',
            $settings['host'],
            $settings['port'],
            $settings['charset'] ?? 'utf8mb4'
        );

        $pdo = new PDO($dsn, $settings['username'], $settings['password']);
        $database = str_replace('`', '``', (string) $settings['database']);
        $charset = $settings['charset'] ?? 'utf8mb4';

        $pdo->exec(sprintf(
            'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s_unicode_ci',
            $database,
            $charset,
            $charset
        ));
    }

    private function connectPgsql(array $settings): PDO
    {
        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $settings['host'],
            $settings['port'],
            $settings['database']
        );

        return new PDO($dsn, $settings['username'], $settings['password']);
    }
}
