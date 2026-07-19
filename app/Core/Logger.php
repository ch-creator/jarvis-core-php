<?php

declare(strict_types=1);

namespace App\Core;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as MonologLogger;

final class Logger
{
    private MonologLogger $logger;

    public function __construct(Config $config)
    {
        $channel = (string) $config->get('logging.default', 'single');
        $levelName = strtoupper((string) $config->get('logging.level', 'debug'));
        $path = (string) $config->get('logging.path', 'storage/logs/jarvis.log');

        if (!str_starts_with($path, DIRECTORY_SEPARATOR) && !preg_match('/^[A-Za-z]:\\\\/', $path) && !str_starts_with($path, '/tmp/')) {
            $path = BASE_PATH . '/' . ltrim($path, '/');
        }

        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        try {
            $level = Level::fromName($levelName);
        } catch (\ValueError) {
            $level = Level::Debug;
        }

        $this->logger = new MonologLogger($channel);
        $this->logger->pushHandler(new StreamHandler($path, $level));
    }

    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function exception(\Throwable $throwable, array $context = []): void
    {
        $this->error($throwable->getMessage(), array_merge($context, [
            'exception' => get_class($throwable),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTraceAsString(),
        ]));
    }
}
