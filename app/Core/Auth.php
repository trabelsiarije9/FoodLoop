<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\UserModel;

final class Auth
{
    public static function user(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $user = (new UserModel())->findById((int) $_SESSION['user_id']);

        if ($user === null) {
            self::logout();
            return null;
        }

        return $user;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = (new UserModel())->findByEmail($email);

        if ($user === null || !(bool) $user['is_active']) {
            return false;
        }

        if (!password_verify($password, (string) $user['password_hash'])) {
            return false;
        }

        $_SESSION['user_id'] = (int) $user['id'];
        (new UserModel())->touchLogin((int) $user['id']);

        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function hasRole(string ...$roles): bool
    {
        $user = self::user();

        return $user !== null && in_array($user['role_code'], $roles, true);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            flash('error', 'Connectez-vous pour acceder a cette page.');
            redirect('login');
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();

        if (!self::hasRole(...$roles)) {
            flash('error', 'Vous n avez pas acces a cette section.');
            redirect('dashboard');
        }
    }
}
