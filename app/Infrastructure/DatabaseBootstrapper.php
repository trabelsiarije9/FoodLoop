<?php
declare(strict_types=1);

namespace App\Infrastructure;

use PDO;
use RuntimeException;

final class DatabaseBootstrapper
{
    public static function ensureDatabase(string $databaseFile): void
    {
        $storageDirectory = dirname($databaseFile);

        if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0777, true) && !is_dir($storageDirectory)) {
            throw new RuntimeException('Impossible de creer le dossier de stockage de la base de donnees.');
        }

        $needsBootstrap = !is_file($databaseFile);

        $bootstrapConnection = new PDO('sqlite:' . $databaseFile);
        $bootstrapConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $bootstrapConnection->exec('PRAGMA foreign_keys = ON');

        if (!$needsBootstrap && !self::databaseIsEmpty($bootstrapConnection)) {
            return;
        }

        self::runSqlFile($bootstrapConnection, dirname(__DIR__, 2) . '/database/schema.sql');
        self::runSqlFile($bootstrapConnection, dirname(__DIR__, 2) . '/database/seed.sql');
    }

    private static function databaseIsEmpty(PDO $connection): bool
    {
        $count = (int) $connection
            ->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'")
            ->fetchColumn();

        return $count === 0;
    }

    private static function runSqlFile(PDO $connection, string $path): void
    {
        $sql = file_get_contents($path);

        if ($sql === false) {
            throw new RuntimeException('Impossible de lire le fichier SQL: ' . $path);
        }

        $connection->exec($sql);
    }
}
