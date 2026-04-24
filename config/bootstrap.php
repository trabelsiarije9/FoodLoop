<?php
declare(strict_types=1);

return [
    'app' => [
        'timezone' => getenv('APP_TIMEZONE') ?: 'Africa/Tunis',
        'session_path' => dirname(__DIR__) . '/storage/sessions',
    ],
    'database' => [
        'driver' => 'oracle',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('DB_PORT') ?: 1521),
        'database' => getenv('DB_DATABASE') ?: 'FREEPDB1',
        'username' => getenv('DB_USERNAME') ?: 'foodloop',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => getenv('DB_CHARSET') ?: 'AL32UTF8',
        'collation' => getenv('DB_COLLATION') ?: '',
        'service_name' => getenv('DB_SERVICE_NAME') ?: 'FREEPDB1',
    ],
];
