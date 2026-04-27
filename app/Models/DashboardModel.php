<?php

declare(strict_types=1);

namespace App\Models;

final class DashboardModel extends BaseModel
{
    public function adminStats(): array
    {
        $db = $this->requireDb();

        return [
            'users' => (int) $db->query('SELECT COUNT(*) FROM UTILISATEURS')->fetchColumn(),
            'businesses' => (int) $db->query('SELECT COUNT(*) FROM PROPRIETAIRES_COMMERCE')->fetchColumn(),
            'food_items' => (int) $db->query('SELECT COUNT(*) FROM ANNONCES')->fetchColumn(),
            'reservations' => (int) $db->query('SELECT COUNT(*) FROM RESERVATIONS')->fetchColumn(),
        ];
    }

    public function businessStats(int $organizationId): array
    {
        $db = $this->requireDb();

        $itemStmt = $db->prepare('SELECT COUNT(*) FROM ANNONCES WHERE PROPRIETAIRE_ID = :organization_id');
        $itemStmt->execute(['organization_id' => $organizationId]);

        $availableStmt = $db->prepare(
            'SELECT COUNT(*) FROM ANNONCES
             WHERE PROPRIETAIRE_ID = :organization_id AND STATUT IN (''available'', ''priority_access'')'
        );
        $availableStmt->execute(['organization_id' => $organizationId]);

        $reservationStmt = $db->prepare(
            'SELECT COUNT(*)
             FROM RESERVATIONS r
             INNER JOIN ANNONCES a ON a.ID_ANNONCE = r.ANNONCE_ID
             WHERE a.PROPRIETAIRE_ID = :organization_id'
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

        $reservationStmt = $db->prepare('SELECT COUNT(*) FROM RESERVATIONS WHERE UTILISATEUR_ID = :user_id');
        $reservationStmt->execute(['user_id' => $userId]);

        $approvedStmt = $db->prepare(
            'SELECT COUNT(*) FROM RESERVATIONS WHERE UTILISATEUR_ID = :user_id AND STATUT IN (''approved'', ''picked_up'', ''completed'')'
        );
        $approvedStmt->execute(['user_id' => $userId]);

        return [
            'reservations' => (int) $reservationStmt->fetchColumn(),
            'approved' => (int) $approvedStmt->fetchColumn(),
            'available_items' => (int) $db->query('SELECT COUNT(*) FROM ANNONCES WHERE STATUT IN (''available'', ''priority_access'')')->fetchColumn(),
        ];
    }

    public function associationStats(int $userId): array
    {
        $db = $this->requireDb();

        $reservationStmt = $db->prepare('SELECT COUNT(*) FROM RESERVATIONS WHERE ADMIN_ASSOCIATION_ID = :user_id');
        $reservationStmt->execute(['user_id' => $userId]);

        $approvedStmt = $db->prepare(
            'SELECT COUNT(*) FROM RESERVATIONS WHERE ADMIN_ASSOCIATION_ID = :user_id AND STATUT IN (''approved'', ''picked_up'', ''completed'')'
        );
        $approvedStmt->execute(['user_id' => $userId]);

        return [
            'reservations' => (int) $reservationStmt->fetchColumn(),
            'approved' => (int) $approvedStmt->fetchColumn(),
            'available_items' => (int) $db->query('SELECT COUNT(*) FROM ANNONCES WHERE STATUT IN (''available'', ''priority_access'')')->fetchColumn(),
        ];
    }
}
