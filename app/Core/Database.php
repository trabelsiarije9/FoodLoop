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

        $driver = (string) app_config('db.driver', 'mysql');
        $host = (string) app_config('db.host', '127.0.0.1');
        $port = (string) app_config('db.port', '3306');
        $dbname = (string) app_config('db.name', 'foodloop');
        $serviceName = (string) app_config('db.service_name', '');
        $username = (string) app_config('db.user', 'root');
        $password = (string) app_config('db.password', '');
        $charset = (string) app_config('db.charset', 'utf8mb4');

        try {
            $dsn = $driver === 'oci'
                ? self::buildOracleDsn($host, $port, $serviceName, $dbname, $charset)
                : "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

            self::$connection = new PDO(
                $dsn,
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

    private static function buildOracleDsn(
        string $host,
        string $port,
        string $serviceName,
        string $dbname,
        string $charset
    ): string {
        $target = $serviceName !== '' ? $serviceName : $dbname;
        return "oci:dbname=//{$host}:{$port}/{$target};charset={$charset}";
    }
}
