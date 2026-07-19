<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Config;
use App\Core\Database;
use App\Core\Env;
use App\Core\MigrationRunner;
use App\Core\VercelBootstrap;

Env::load(dirname(__DIR__));
VercelBootstrap::apply();

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

$config = new Config(BASE_PATH . '/config');
$database = new Database($config);

try {
    MigrationRunner::run($database, $config);
    echo 'Migrations completed successfully using [' . $config->get('database.default', 'sqlite') . "].\n";
} catch (Throwable $exception) {
    fwrite(STDERR, 'Migration failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
