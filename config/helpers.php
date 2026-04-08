<?php

declare(strict_types=1);

use App\Core\Auth;

if (!function_exists('app_config')) {
    function app_config(?string $key = null, mixed $default = null): mixed
    {
        static $config;

        if ($config === null) {
            $config = require __DIR__ . '/app.php';
        }

        if ($key === null) {
            return $config;
        }

        $segments = explode('.', $key);
        $value = $config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return ltrim($path, '/');
    }
}

if (!function_exists('route')) {
    function route(string $route, array $params = []): string
    {
        $query = array_merge(['route' => $route], $params);
        return 'index.php?' . http_build_query($query);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $route, array $params = []): never
    {
        header('Location: ' . route($route, $params));
        exit;
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string
    {
        $value = $_SESSION['old'][$key] ?? $default;
        return is_string($value) ? $value : $default;
    }
}

if (!function_exists('store_old_input')) {
    function store_old_input(array $input): void
    {
        $_SESSION['old'] = $input;
    }
}

if (!function_exists('clear_old_input')) {
    function clear_old_input(): void
    {
        unset($_SESSION['old']);
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type][] = $message;
    }
}

if (!function_exists('pull_flashes')) {
    function pull_flashes(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return is_array($messages) ? $messages : [];
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(24));
        }

        return (string) $_SESSION['_csrf'];
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(?string $token): bool
    {
        return is_string($token) && hash_equals((string) ($_SESSION['_csrf'] ?? ''), $token);
    }
}

if (!function_exists('current_user')) {
    function current_user(): ?array
    {
        return Auth::user();
    }
}

if (!function_exists('is_guest')) {
    function is_guest(): bool
    {
        return Auth::user() === null;
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(?string $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $timestamp = strtotime($value);
        return $timestamp === false ? $value : date('d/m/Y H:i', $timestamp);
    }
}

if (!function_exists('format_number')) {
    function format_number(int|float|string $value): string
    {
        return number_format((float) $value, 0, ',', ' ');
    }
}
