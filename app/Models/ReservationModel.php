<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class ReservationModel extends BaseModel
{
    public function create(array $data): int
    {
        $db = $this->requireDb();
        $stmt = $db->prepare(
            'INSERT INTO reservations (food_item_id, user_id, organization_id, reserved_quantity, status, reserved_at, notes)
             VALUES (:food_item_id, :user_id, :organization_id, :reserved_quantity, :status, NOW(), :notes)'
        );
        $stmt->execute([
            'food_item_id' => $data['food_item_id'],
            'user_id' => $data['user_id'],
            'organization_id' => $data['organization_id'] ?: null,
            'reserved_quantity' => $data['reserved_quantity'],
            'status' => $data['status'] ?? 'pending',
            'notes' => $data['notes'] ?: null,
        ]);

        return (int) $db->lastInsertId();
    }

    public function allForUser(int $userId): array
    {
        $stmt = $this->requireDb()->prepare(
            'SELECT reservations.*, food_items.title, food_items.unit, organizations.name AS business_name
             FROM reservations
             INNER JOIN food_items ON food_items.id = reservations.food_item_id
             INNER JOIN organizations ON organizations.id = food_items.organization_id
             WHERE reservations.user_id = :user_id
             ORDER BY reservations.created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allForOrganization(int $organizationId): array
    {
        $stmt = $this->requireDb()->prepare(
            'SELECT reservations.*, food_items.title, food_items.unit, users.first_name, users.last_name
             FROM reservations
             INNER JOIN food_items ON food_items.id = reservations.food_item_id
             INNER JOIN users ON users.id = reservations.user_id
             WHERE food_items.organization_id = :organization_id
             ORDER BY reservations.created_at DESC'
        );
        $stmt->execute(['organization_id' => $organizationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allForAdmin(): array
    {
        $stmt = $this->requireDb()->query(
            'SELECT reservations.*, food_items.title, organizations.name AS organization_name,
                    users.first_name, users.last_name
             FROM reservations
             INNER JOIN food_items ON food_items.id = reservations.food_item_id
             INNER JOIN organizations ON organizations.id = food_items.organization_id
             INNER JOIN users ON users.id = reservations.user_id
             ORDER BY reservations.created_at DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->requireDb()->prepare('UPDATE reservations SET status = :status WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);
    }
}
