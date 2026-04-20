<?php
declare(strict_types=1);

namespace App\Infrastructure;

use PDO;
use PDOException;
use RuntimeException;

final class DatabaseBootstrapper
{
    public static function ensureDatabase(array $config): void
    {
        self::assertDatabaseName($config['database']);

        try {
            $serverConnection = new PDO(
                Database::dsn($config, false),
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $exception) {
            throw new RuntimeException('Impossible de joindre le serveur MySQL pour initialiser FoodLoop.', 0, $exception);
        }

        $serverConnection->exec(sprintf(
            'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s',
            $config['database'],
            $config['charset'],
            $config['collation']
        ));

        $databaseConnection = new PDO(
            Database::dsn($config, true),
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        $databaseConnection->exec('SET NAMES ' . $config['charset']);

        if (!self::databaseIsEmpty($databaseConnection, $config['database'])) {
            return;
        }

        self::runSqlFile($databaseConnection, dirname(__DIR__, 2) . '/database/schema.sql');
        self::runSqlFile($databaseConnection, dirname(__DIR__, 2) . '/database/seed.sql');
    }

    private static function databaseIsEmpty(PDO $connection, string $databaseName): bool
    {
        $statement = $connection->prepare(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = :database_name'
        );
        $statement->execute(['database_name' => $databaseName]);

        return (int) $statement->fetchColumn() === 0;
    }

    private static function runSqlFile(PDO $connection, string $path): void
    {
        $sql = file_get_contents($path);

        if ($sql === false) {
            throw new RuntimeException('Impossible de lire le fichier SQL: ' . $path);
        }

        $connection->exec($sql);
    }

    private static function assertDatabaseName(string $databaseName): void
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $databaseName)) {
            throw new RuntimeException('Le nom de la base MySQL contient des caracteres non supportes.');
        }
    }
}
