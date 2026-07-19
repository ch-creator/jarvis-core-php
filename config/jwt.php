<?php

declare(strict_types=1);

return [
    'secret' => env('JWT_SECRET', ''),
    'access_ttl' => (int) env('JWT_ACCESS_TTL', 3600),
    'refresh_ttl' => (int) env('JWT_REFRESH_TTL', 604800),
    'issuer' => env('APP_URL', 'http://localhost'),
    'algorithm' => 'HS256',
];
