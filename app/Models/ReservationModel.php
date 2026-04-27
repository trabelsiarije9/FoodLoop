<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class ReservationModel extends BaseModel
{
    public function create(array $data): int
    {
        $db = $this->requireDb();
        $id = $this->nextId('RESERVATIONS', 'ID_RESERVATION');
        $stmt = $db->prepare(
            'INSERT INTO RESERVATIONS (
                ID_RESERVATION, ANNONCE_ID, UTILISATEUR_ID, ADMIN_ASSOCIATION_ID, QUANTITE_RESERVEE,
                STATUT, DATE_RESERVATION, DATE_PICKUP, CREATED_AT
             ) VALUES (
                :id, :food_item_id, :user_id, :organization_id, :reserved_quantity,
                :status, CURRENT_TIMESTAMP, :pickup_date, CURRENT_TIMESTAMP
             )'
        );
        $stmt->execute([
            'id' => $id,
            'food_item_id' => $data['food_item_id'],
            'user_id' => $data['user_id'] ?: null,
            'organization_id' => $data['organization_id'] ?: null,
            'reserved_quantity' => $data['reserved_quantity'],
            'status' => $data['status'] ?? 'pending',
            'pickup_date' => $data['pickup_date'] ?? date('Y-m-d H:i:s', strtotime('+2 hours')),
        ]);

        return $id;
    }

    public function allForUser(int $userId): array
    {
        $stmt = $this->requireDb()->prepare(
            'SELECT
                r.ID_RESERVATION AS id,
                r.ANNONCE_ID AS food_item_id,
                r.UTILISATEUR_ID AS user_id,
                r.ADMIN_ASSOCIATION_ID AS organization_id,
                r.QUANTITE_RESERVEE AS reserved_quantity,
                r.STATUT AS status,
                r.DATE_RESERVATION AS reserved_at,
                r.DATE_PICKUP AS pickup_confirmed_at,
                r.CREATED_AT AS created_at,
                a.TITRE AS title,
                a.UNITE AS unit,
                p.NOM_COMMERCE AS business_name
             FROM RESERVATIONS r
             INNER JOIN ANNONCES a ON a.ID_ANNONCE = r.ANNONCE_ID
             LEFT JOIN PROPRIETAIRES_COMMERCE p ON p.ID_COMMERCE = a.PROPRIETAIRE_ID
             WHERE r.UTILISATEUR_ID = :user_id
             ORDER BY r.CREATED_AT DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allForOrganization(int $organizationId): array
    {
        $stmt = $this->requireDb()->prepare(
            'SELECT
                r.ID_RESERVATION AS id,
                r.ANNONCE_ID AS food_item_id,
                r.UTILISATEUR_ID AS user_id,
                r.ADMIN_ASSOCIATION_ID AS organization_id,
                r.QUANTITE_RESERVEE AS reserved_quantity,
                r.STATUT AS status,
                r.DATE_RESERVATION AS reserved_at,
                r.DATE_PICKUP AS pickup_confirmed_at,
                r.CREATED_AT AS created_at,
                a.TITRE AS title,
                a.UNITE AS unit,
                u.PRENOM AS first_name,
                u.NOM AS last_name
             FROM RESERVATIONS r
             INNER JOIN ANNONCES a ON a.ID_ANNONCE = r.ANNONCE_ID
             LEFT JOIN UTILISATEURS u ON u.ID_UTIL = r.UTILISATEUR_ID
             WHERE a.PROPRIETAIRE_ID = :organization_id
             ORDER BY r.CREATED_AT DESC'
        );
        $stmt->execute(['organization_id' => $organizationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allForAssociation(int $associationAdminId): array
    {
        $stmt = $this->requireDb()->prepare(
            'SELECT
                r.ID_RESERVATION AS id,
                r.ANNONCE_ID AS food_item_id,
                r.UTILISATEUR_ID AS user_id,
                r.ADMIN_ASSOCIATION_ID AS organization_id,
                r.QUANTITE_RESERVEE AS reserved_quantity,
                r.STATUT AS status,
                r.DATE_RESERVATION AS reserved_at,
                r.DATE_PICKUP AS pickup_confirmed_at,
                r.CREATED_AT AS created_at,
                a.TITRE AS title,
                a.UNITE AS unit,
                p.NOM_COMMERCE AS business_name
             FROM RESERVATIONS r
             INNER JOIN ANNONCES a ON a.ID_ANNONCE = r.ANNONCE_ID
             LEFT JOIN PROPRIETAIRES_COMMERCE p ON p.ID_COMMERCE = a.PROPRIETAIRE_ID
             WHERE r.ADMIN_ASSOCIATION_ID = :association_id
             ORDER BY r.CREATED_AT DESC'
        );
        $stmt->execute(['association_id' => $associationAdminId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allForAdmin(): array
    {
        $stmt = $this->requireDb()->query(
            'SELECT
                r.ID_RESERVATION AS id,
                r.ANNONCE_ID AS food_item_id,
                r.UTILISATEUR_ID AS user_id,
                r.ADMIN_ASSOCIATION_ID AS organization_id,
                r.QUANTITE_RESERVEE AS reserved_quantity,
                r.STATUT AS status,
                r.DATE_RESERVATION AS reserved_at,
                r.DATE_PICKUP AS pickup_confirmed_at,
                r.CREATED_AT AS created_at,
                a.TITRE AS title,
                COALESCE(p.NOM_COMMERCE, aa.NOM_ASSOCIATION) AS organization_name,
                u.PRENOM AS first_name,
                u.NOM AS last_name
             FROM RESERVATIONS r
             INNER JOIN ANNONCES a ON a.ID_ANNONCE = r.ANNONCE_ID
             LEFT JOIN PROPRIETAIRES_COMMERCE p ON p.ID_COMMERCE = a.PROPRIETAIRE_ID
             LEFT JOIN ADMINS_ASSOCIATION aa ON aa.ID_ADMIN_ASSOCIATION = r.ADMIN_ASSOCIATION_ID
             LEFT JOIN UTILISATEURS u ON u.ID_UTIL = r.UTILISATEUR_ID
             ORDER BY r.CREATED_AT DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->requireDb()->prepare('UPDATE RESERVATIONS SET STATUT = :status WHERE ID_RESERVATION = :id');
        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);
    }
}
