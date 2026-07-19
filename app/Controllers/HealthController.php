<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Config;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

final class HealthController extends Controller
{
    public function __construct(
        private readonly Config $config,
        private readonly Database $database,
    ) {
    }

    public function index(Request $request): Response
    {
        $databaseStatus = 'ok';

        try {
            $this->database->query('SELECT 1');
        } catch (\Throwable) {
            $databaseStatus = 'error';
        }

        return $this->success([
            'service' => $this->config->get('app.name'),
            'version' => $this->config->get('app.version'),
            'environment' => $this->config->get('app.env'),
            'database' => $databaseStatus,
            'timestamp' => gmdate('c'),
        ], 'Jarvis Core is running.');
    }
}
