<?php
declare(strict_types=1);

namespace App\Infrastructure;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        if (!extension_loaded('pdo_mysql')) {
            throw new RuntimeException('L extension pdo_mysql est requise pour utiliser FoodLoop avec MySQL.');
        }

        $config = self::configuration();
        DatabaseBootstrapper::ensureDatabase($config);

        try {
            self::$connection = new PDO(
                self::dsn($config, true),
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Connexion MySQL impossible. Verifiez XAMPP, les identifiants et la base `' . $config['database'] . '`.',
                0,
                $exception
            );
        }

        return self::$connection;
    }

    public static function configuration(): array
    {
        $config = $GLOBALS['config']['database'] ?? null;

        if (!is_array($config)) {
            throw new RuntimeException('Configuration de base de donnees introuvable.');
        }

        return $config;
    }

    public static function dsn(array $config, bool $withDatabase): string
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;charset=%s',
            $config['host'],
            $config['port'],
            $config['charset']
        );

        if ($withDatabase) {
            $dsn .= ';dbname=' . $config['database'];
        }

        return $dsn;
    }
}
