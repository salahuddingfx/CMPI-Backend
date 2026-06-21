<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_origins' => array_filter([
        env('CLIENT_URL'),
        env('ADMIN_URL'),
        env('LOCAL_CLIENT_URL', 'http://localhost:5173'),
        env('LOCAL_ADMIN_URL', 'http://localhost:5175'),
    ]),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept'],
    'exposed_headers' => [],
    'max_age' => 86400,
    'supports_credentials' => true,
];
