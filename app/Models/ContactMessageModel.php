<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class ContactMessageModel extends BaseModel
{
    public function create(array $data): int
    {
        $db = $this->requireDb();
        $id = $this->nextId('CONTACT_MESSAGES', 'ID');
        $stmt = $db->prepare(
            'INSERT INTO CONTACT_MESSAGES (ID, FULLNAME, EMAIL, ORGANIZATION, ROLE_LABEL, MESSAGE, CREATED_AT)
             VALUES (:id, :fullname, :email, :organization, :role_label, :message, CURRENT_TIMESTAMP)'
        );
        $stmt->execute([
            'id' => $id,
            'fullname' => $data['fullname'],
            'email' => $data['email'],
            'organization' => $data['organization'] ?: null,
            'role_label' => $data['role'] ?: null,
            'message' => $data['message'],
        ]);

        return $id;
    }

    public function all(): array
    {
        $stmt = $this->requireDb()->query(
            'SELECT
                ID AS id,
                FULLNAME AS fullname,
                EMAIL AS email,
                ORGANIZATION AS organization,
                ROLE_LABEL AS role_label,
                MESSAGE AS message,
                CREATED_AT AS created_at
             FROM CONTACT_MESSAGES
             ORDER BY CREATED_AT DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
