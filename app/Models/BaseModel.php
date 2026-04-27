<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel
{
    protected ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    protected function requireDb(): PDO
    {
        if (!$this->db instanceof PDO) {
            throw new \RuntimeException('Connexion base de donnees indisponible.');
        }

        return $this->db;
    }

    protected function nextId(string $table, string $column): int
    {
        $stmt = $this->requireDb()->query("SELECT COALESCE(MAX({$column}), 0) + 1 AS NEXT_ID FROM {$table}");
        return (int) $stmt->fetchColumn();
    }
}
