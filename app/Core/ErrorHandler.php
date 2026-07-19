<?php

declare(strict_types=1);

namespace App\Core;

final class ErrorHandler
{
    public function __construct(
        private readonly Config $config,
        private readonly Logger $logger,
    ) {
    }

    public function register(): void
    {
        $debug = (bool) $this->config->get('app.debug', false);
        $timezone = (string) $this->config->get('app.timezone', 'UTC');

        date_default_timezone_set($timezone);
        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? '1' : '0');

        set_exception_handler(function (\Throwable $throwable) use ($debug): void {
            $this->logger->exception($throwable);

            if (!headers_sent()) {
                Response::json([
                    'success' => false,
                    'message' => $debug ? $throwable->getMessage() : 'Internal server error.',
                    'error' => $debug ? [
                        'type' => get_class($throwable),
                        'file' => $throwable->getFile(),
                        'line' => $throwable->getLine(),
                    ] : null,
                ], 500)->send();
            }
        });

        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }
}
