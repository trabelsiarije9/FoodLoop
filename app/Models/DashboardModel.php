<?php

declare(strict_types=1);

namespace App\Models;

final class DashboardModel extends BaseModel
{
    public function adminStats(): array
    {
        $db = $this->requireDb();

        return [
            'users' => (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'businesses' => (int) $db->query('SELECT COUNT(*) FROM organizations WHERE organization_type = "business"')->fetchColumn(),
            'food_items' => (int) $db->query('SELECT COUNT(*) FROM food_items')->fetchColumn(),
            'reservations' => (int) $db->query('SELECT COUNT(*) FROM reservations')->fetchColumn(),
        ];
    }

    public function businessStats(int $organizationId): array
    {
        $db = $this->requireDb();

        $itemStmt = $db->prepare('SELECT COUNT(*) FROM food_items WHERE organization_id = :organization_id');
        $itemStmt->execute(['organization_id' => $organizationId]);

        $availableStmt = $db->prepare(
            'SELECT COUNT(*) FROM food_items
             WHERE organization_id = :organization_id AND status IN ("available", "priority_access")'
        );
        $availableStmt->execute(['organization_id' => $organizationId]);

        $reservationStmt = $db->prepare(
            'SELECT COUNT(*)
             FROM reservations
             INNER JOIN food_items ON food_items.id = reservations.food_item_id
             WHERE food_items.organization_id = :organization_id'
        );
        $reservationStmt->execute(['organization_id' => $organizationId]);

        return [
            'items' => (int) $itemStmt->fetchColumn(),
            'available_items' => (int) $availableStmt->fetchColumn(),
            'reservations' => (int) $reservationStmt->fetchColumn(),
        ];
    }

    public function userStats(int $userId): array
    {
        $db = $this->requireDb();

        $reservationStmt = $db->prepare('SELECT COUNT(*) FROM reservations WHERE user_id = :user_id');
        $reservationStmt->execute(['user_id' => $userId]);

        $approvedStmt = $db->prepare(
            'SELECT COUNT(*) FROM reservations WHERE user_id = :user_id AND status IN ("approved", "picked_up", "completed")'
        );
        $approvedStmt->execute(['user_id' => $userId]);

        return [
            'reservations' => (int) $reservationStmt->fetchColumn(),
            'approved' => (int) $approvedStmt->fetchColumn(),
            'available_items' => (int) $db->query('SELECT COUNT(*) FROM food_items WHERE status IN ("available", "priority_access")')->fetchColumn(),
        ];
    }

    public function associationStats(int $userId): array
    {
        return $this->userStats($userId);
    }
}
