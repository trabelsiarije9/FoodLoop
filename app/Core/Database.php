<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): ?PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = (string) app_config('db.host', '127.0.0.1');
        $port = (string) app_config('db.port', '3306');
        $dbname = (string) app_config('db.name', 'foodloop');
        $username = (string) app_config('db.user', 'root');
        $password = (string) app_config('db.password', '');
        $charset = (string) app_config('db.charset', 'utf8mb4');

        try {
            self::$connection = new PDO(
                "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException) {
            return null;
        }

        return self::$connection;
    }
}
