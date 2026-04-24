<?php
declare(strict_types=1);

$driver = getenv('DB_DRIVER') ?: 'mysql';

return [
    'app' => [
        'timezone' => getenv('APP_TIMEZONE') ?: 'Africa/Tunis',
        'session_path' => dirname(__DIR__) . '/storage/sessions',
    ],
    'database' => [
        'driver' => $driver,
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('DB_PORT') ?: ($driver === 'oracle' ? 1521 : 3306)),
        'database' => getenv('DB_DATABASE') ?: 'footloop1',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
        'collation' => getenv('DB_COLLATION') ?: 'utf8mb4_unicode_ci',
        'service_name' => getenv('DB_SERVICE_NAME') ?: '',
    ],
];
