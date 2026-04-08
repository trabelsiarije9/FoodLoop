<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class FoodItemModel extends BaseModel
{
    public function categories(): array
    {
        $stmt = $this->requireDb()->query('SELECT id, name FROM food_categories ORDER BY name ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function latestAvailable(int $limit = 6): array
    {
        $stmt = $this->requireDb()->prepare(
            'SELECT food_items.*, organizations.name AS organization_name, food_categories.name AS category_name,
                    addresses.city, addresses.governorate
             FROM food_items
             INNER JOIN organizations ON organizations.id = food_items.organization_id
             INNER JOIN food_categories ON food_categories.id = food_items.category_id
             INNER JOIN addresses ON addresses.id = food_items.pickup_address_id
             WHERE food_items.status IN ("available", "priority_access")
             ORDER BY food_items.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allForAdmin(): array
    {
        $stmt = $this->requireDb()->query(
            'SELECT food_items.*, organizations.name AS organization_name, food_categories.name AS category_name
             FROM food_items
             INNER JOIN organizations ON organizations.id = food_items.organization_id
             INNER JOIN food_categories ON food_categories.id = food_items.category_id
             ORDER BY food_items.created_at DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allForOrganization(int $organizationId): array
    {
        $stmt = $this->requireDb()->prepare(
            'SELECT food_items.*, food_categories.name AS category_name
             FROM food_items
             INNER JOIN food_categories ON food_categories.id = food_items.category_id
             WHERE food_items.organization_id = :organization_id
             ORDER BY food_items.created_at DESC'
        );
        $stmt->execute(['organization_id' => $organizationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->requireDb()->prepare('SELECT * FROM food_items WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        return $item ?: null;
    }

    public function create(array $data): int
    {
        $db = $this->requireDb();
        $stmt = $db->prepare(
            'INSERT INTO food_items (
                organization_id, category_id, created_by, title, description, quantity, unit,
                expiration_date, pickup_start, pickup_end, pickup_address_id, status, priority_until
             ) VALUES (
                :organization_id, :category_id, :created_by, :title, :description, :quantity, :unit,
                :expiration_date, :pickup_start, :pickup_end, :pickup_address_id, :status, :priority_until
             )'
        );
        $stmt->execute([
            'organization_id' => $data['organization_id'],
            'category_id' => $data['category_id'],
            'created_by' => $data['created_by'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'expiration_date' => $data['expiration_date'],
            'pickup_start' => $data['pickup_start'],
            'pickup_end' => $data['pickup_end'],
            'pickup_address_id' => $data['pickup_address_id'],
            'status' => $data['status'],
            'priority_until' => $data['priority_until'] ?: null,
        ]);

        return (int) $db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->requireDb()->prepare(
            'UPDATE food_items
             SET category_id = :category_id, title = :title, description = :description, quantity = :quantity,
                 unit = :unit, expiration_date = :expiration_date, pickup_start = :pickup_start,
                 pickup_end = :pickup_end, status = :status, priority_until = :priority_until
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'expiration_date' => $data['expiration_date'],
            'pickup_start' => $data['pickup_start'],
            'pickup_end' => $data['pickup_end'],
            'status' => $data['status'],
            'priority_until' => $data['priority_until'] ?: null,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->requireDb()->prepare('DELETE FROM food_items WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
