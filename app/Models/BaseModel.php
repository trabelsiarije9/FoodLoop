<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    protected function nextId(string $table, string $column): int
    {
        $stmt = $this->pdo->prepare("SELECT NVL(MAX($column), 0) + 1 AS NEXT_ID FROM $table");
        $stmt->execute();
        $row = $stmt->fetch();

        return (int) ($row['NEXT_ID'] ?? 1);
    }
}
