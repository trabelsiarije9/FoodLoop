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

        $config = self::configuration();
        self::assertPdoExtension($config['driver']);
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
            $platform = self::driverLabel($config['driver']);

            throw new RuntimeException(
                'Connexion ' . $platform . ' impossible. Verifiez les identifiants et le schema `' . $config['database'] . '`.',
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
        if ($config['driver'] !== 'oracle') {
            throw new RuntimeException('FoodLoop est configure uniquement pour Oracle.');
        }

        return self::oracleDsn($config, $withDatabase);
    }

    public static function driver(): string
    {
        $config = self::configuration();

        return $config['driver'];
    }

    public static function driverLabel(string $driver): string
    {
        return $driver === 'oracle' ? 'Oracle' : strtoupper($driver);
    }

    private static function assertPdoExtension(string $driver): void
    {
        if ($driver !== 'oracle') {
            throw new RuntimeException('FoodLoop est configure uniquement pour Oracle.');
        }

        $extension = 'pdo_oci';

        if (!extension_loaded($extension)) {
            throw new RuntimeException(
                'L extension ' . $extension . ' est requise pour utiliser FoodLoop avec ' . self::driverLabel($driver) . '.'
            );
        }
    }

    private static function oracleDsn(array $config, bool $withDatabase): string
    {
        $service = $config['service_name'] !== '' ? $config['service_name'] : $config['database'];

        if ($withDatabase) {
            return sprintf(
                'oci:dbname=//%s:%d/%s;charset=%s',
                $config['host'],
                $config['port'],
                $service,
                $config['charset']
            );
        }

        return sprintf(
            'oci:dbname=//%s:%d/%s;charset=%s',
            $config['host'],
            $config['port'],
            $service,
            $config['charset']
        );
    }
}
