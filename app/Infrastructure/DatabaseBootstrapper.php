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
        if ($config['driver'] !== 'oracle') {
            throw new RuntimeException('FoodLoop est configure uniquement pour Oracle.');
        }

        self::bootstrapOracle($config);
    }

    private static function bootstrapOracle(array $config): void
    {
        try {
            $connection = self::createPdo($config, true);
        } catch (PDOException $exception) {
            throw new RuntimeException('Impossible de joindre le schema Oracle pour initialiser FoodLoop.', 0, $exception);
        }

        if (!self::databaseIsEmpty($connection, $config)) {
            return;
        }

        self::runSqlFile($connection, self::schemaPath($config['driver']));
        self::runSqlFile($connection, self::seedPath($config['driver']));
    }

    private static function createPdo(array $config, bool $withDatabase): PDO
    {
        return new PDO(
            Database::dsn($config, $withDatabase),
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    private static function databaseIsEmpty(PDO $connection, array $config): bool
    {
        $statement = $connection->query(
            "SELECT COUNT(*) FROM user_tables WHERE table_name IN ('ROLES', 'CATEGORIES', 'UTILISATEURS')"
        );

        return (int) $statement->fetchColumn() === 0;
    }

    private static function runSqlFile(PDO $connection, string $path): void
    {
        $sql = file_get_contents($path);

        if ($sql === false) {
            throw new RuntimeException('Impossible de lire le fichier SQL: ' . $path);
        }

        foreach (self::splitStatements($sql) as $statement) {
            $connection->exec($statement);
        }
    }

    private static function schemaPath(string $driver): string
    {
        return dirname(__DIR__, 2) . '/database/schema.oracle.sql';
    }

    private static function seedPath(string $driver): string
    {
        return dirname(__DIR__, 2) . '/database/seed.oracle.sql';
    }

    private static function splitStatements(string $sql): array
    {
        $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
        $statements = [];
        $buffer = '';
        $length = strlen($sql);
        $inSingleQuote = false;
        $inDoubleQuote = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($char === "'" && !$inDoubleQuote) {
                $escaped = $i > 0 && $sql[$i - 1] === '\\';
                if (!$escaped) {
                    $inSingleQuote = !$inSingleQuote;
                }
            } elseif ($char === '"' && !$inSingleQuote) {
                $escaped = $i > 0 && $sql[$i - 1] === '\\';
                if (!$escaped) {
                    $inDoubleQuote = !$inDoubleQuote;
                }
            }

            if ($char === ';' && !$inSingleQuote && !$inDoubleQuote) {
                $statement = trim($buffer);
                if ($statement !== '') {
                    $statements[] = $statement;
                }
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $statement = trim($buffer);
        if ($statement !== '') {
            $statements[] = $statement;
        }

        return $statements;
    }
}
