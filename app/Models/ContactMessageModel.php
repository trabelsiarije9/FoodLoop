<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class ContactMessageModel extends BaseModel
{
    public function create(array $data): int
    {
        $db = $this->requireDb();
        $stmt = $db->prepare(
            'INSERT INTO contact_messages (fullname, email, organization, role_label, message)
             VALUES (:fullname, :email, :organization, :role_label, :message)'
        );
        $stmt->execute([
            'fullname' => $data['fullname'],
            'email' => $data['email'],
            'organization' => $data['organization'] ?: null,
            'role_label' => $data['role'] ?: null,
            'message' => $data['message'],
        ]);

        return (int) $db->lastInsertId();
    }

    public function all(): array
    {
        $stmt = $this->requireDb()->query(
            'SELECT * FROM contact_messages ORDER BY created_at DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
