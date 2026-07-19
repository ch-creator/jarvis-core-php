<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Config;
use App\Core\Database;
use App\Core\Env;

Env::load(dirname(__DIR__));

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

$config = new Config(BASE_PATH . '/config');
$database = new Database($config);

$driver = (string) $config->get('database.default', 'mysql');
$migrationPath = __DIR__ . "/migrations/001_initial_schema.{$driver}.sql";

if (!is_file($migrationPath)) {
    fwrite(STDERR, "Migration file not found for driver [{$driver}].\n");
    exit(1);
}

$sql = file_get_contents($migrationPath);

if ($sql === false) {
    fwrite(STDERR, "Migration file could not be read.\n");
    exit(1);
}

$statements = array_filter(array_map('trim', explode(';', $sql)));

try {
    $database->migrate($statements);
    echo "Migrations completed successfully using [{$driver}].\n";
} catch (Throwable $exception) {
    fwrite(STDERR, 'Migration failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
