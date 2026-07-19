<?php

declare(strict_types=1);

return [
    'default' => env('LOG_CHANNEL', 'single'),
    'level' => env('LOG_LEVEL', 'debug'),
    'path' => env('LOG_PATH', 'storage/logs/jarvis.log'),
];
