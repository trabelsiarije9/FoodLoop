<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        if (!extension_loaded('pdo_oci')) {
            throw new RuntimeException('Extension PHP Oracle non chargee. Verifiez la compatibilite x64/x86 entre PHP et Oracle Instant Client.');
        }

        $host = (string) app_config('db.host');
        $port = (string) app_config('db.port');
        $service = (string) app_config('db.service');
        $username = (string) app_config('db.username');
        $password = (string) app_config('db.password');
        $charset = (string) app_config('db.charset');

        $dsn = sprintf(
            'oci:dbname=//%s:%s/%s;charset=%s',
            $host,
            $port,
            $service,
            $charset
        );

        try {
            self::$connection = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $exception) {
            throw new RuntimeException('Connexion a la base Oracle impossible.', 0, $exception);
        }

        return self::$connection;
    }
}
