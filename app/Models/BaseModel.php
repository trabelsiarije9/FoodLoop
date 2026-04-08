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
            throw new \RuntimeException('Connexion MySQL indisponible.');
        }

        return $this->db;
    }
}
