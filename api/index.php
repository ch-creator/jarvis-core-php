<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Core\VercelBootstrap;

VercelBootstrap::apply();

$app = new Application(dirname(__DIR__));
$app->run();
