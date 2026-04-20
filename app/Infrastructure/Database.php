<?php
declare(strict_types=1);

namespace App\Infrastructure;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        if (!extension_loaded('pdo_sqlite')) {
            throw new RuntimeException('L extension pdo_sqlite est requise pour initialiser FoodLoop.');
        }

        $databaseFile = dirname(__DIR__, 2) . '/storage/foodloop.sqlite';
        DatabaseBootstrapper::ensureDatabase($databaseFile);

        $connection = new PDO('sqlite:' . $databaseFile);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $connection->exec('PRAGMA foreign_keys = ON');

        self::$connection = $connection;

        return self::$connection;
    }
}
