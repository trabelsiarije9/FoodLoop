<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

$config = require __DIR__ . '/app.php';

function app_config(string $key, mixed $default = null): mixed
{
    global $config;

    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
    $file = BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $relativePath . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});
