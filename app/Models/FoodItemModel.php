<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class FoodItemModel extends BaseModel
{
    public function categories(): array
    {
        $stmt = $this->requireDb()->query('SELECT ID_CAT AS id, NOM AS name FROM CATEGORIES ORDER BY NOM ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function latestAvailable(int $limit = 6): array
    {
        $limit = max(1, $limit);
        $stmt = $this->requireDb()->query(
            'SELECT
                a.ID_ANNONCE AS id,
                a.PROPRIETAIRE_ID AS organization_id,
                a.CATEGORIE_ID AS category_id,
                a.TITRE AS title,
                a.DESCRIPTION AS description,
                a.TYPE_ALIMENT AS food_type,
                a.QUANTITE AS quantity,
                a.UNITE AS unit,
                a.LOCALISATION AS location,
                a.STATUT AS status,
                a.PUBLIC_VISIBILITY_AT AS priority_until,
                a.PICKUP_START AS pickup_start,
                a.PICKUP_END AS pickup_end,
                a.DATE_EXPIRATION AS expiration_date,
                a.ZONE_ID AS pickup_address_id,
                a.CREATED_AT AS created_at,
                a.UPDATED_AT AS updated_at,
                p.NOM_COMMERCE AS organization_name,
                c.NOM AS category_name,
                z.VILLE_NOM AS city,
                z.GOUVERNORAT AS governorate
             FROM ANNONCES a
             INNER JOIN PROPRIETAIRES_COMMERCE p ON p.ID_COMMERCE = a.PROPRIETAIRE_ID
             INNER JOIN CATEGORIES c ON c.ID_CAT = a.CATEGORIE_ID
             LEFT JOIN ZONES_GEOGRAPHIQUES z ON z.ID_ZONE = a.ZONE_ID
             WHERE a.STATUT IN (''available'', ''priority_access'')
             ORDER BY a.CREATED_AT DESC
             FETCH FIRST ' . $limit . ' ROWS ONLY'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allForAdmin(): array
    {
        $stmt = $this->requireDb()->query(
            'SELECT
                a.ID_ANNONCE AS id,
                a.PROPRIETAIRE_ID AS organization_id,
                a.CATEGORIE_ID AS category_id,
                a.TITRE AS title,
                a.DESCRIPTION AS description,
                a.TYPE_ALIMENT AS food_type,
                a.QUANTITE AS quantity,
                a.UNITE AS unit,
                a.LOCALISATION AS location,
                a.STATUT AS status,
                a.PUBLIC_VISIBILITY_AT AS priority_until,
                a.PICKUP_START AS pickup_start,
                a.PICKUP_END AS pickup_end,
                a.DATE_EXPIRATION AS expiration_date,
                a.ZONE_ID AS pickup_address_id,
                a.CREATED_AT AS created_at,
                a.UPDATED_AT AS updated_at,
                p.NOM_COMMERCE AS organization_name,
                c.NOM AS category_name
             FROM ANNONCES a
             INNER JOIN PROPRIETAIRES_COMMERCE p ON p.ID_COMMERCE = a.PROPRIETAIRE_ID
             INNER JOIN CATEGORIES c ON c.ID_CAT = a.CATEGORIE_ID
             ORDER BY a.CREATED_AT DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allForOrganization(int $organizationId): array
    {
        $stmt = $this->requireDb()->prepare(
            'SELECT
                a.ID_ANNONCE AS id,
                a.PROPRIETAIRE_ID AS organization_id,
                a.CATEGORIE_ID AS category_id,
                a.TITRE AS title,
                a.DESCRIPTION AS description,
                a.TYPE_ALIMENT AS food_type,
                a.QUANTITE AS quantity,
                a.UNITE AS unit,
                a.LOCALISATION AS location,
                a.STATUT AS status,
                a.PUBLIC_VISIBILITY_AT AS priority_until,
                a.PICKUP_START AS pickup_start,
                a.PICKUP_END AS pickup_end,
                a.DATE_EXPIRATION AS expiration_date,
                a.ZONE_ID AS pickup_address_id,
                a.CREATED_AT AS created_at,
                a.UPDATED_AT AS updated_at,
                c.NOM AS category_name
             FROM ANNONCES a
             INNER JOIN CATEGORIES c ON c.ID_CAT = a.CATEGORIE_ID
             WHERE a.PROPRIETAIRE_ID = :organization_id
             ORDER BY a.CREATED_AT DESC'
        );
        $stmt->execute(['organization_id' => $organizationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->requireDb()->prepare(
            'SELECT
                a.ID_ANNONCE AS id,
                a.PROPRIETAIRE_ID AS organization_id,
                a.CATEGORIE_ID AS category_id,
                a.TITRE AS title,
                a.DESCRIPTION AS description,
                a.TYPE_ALIMENT AS food_type,
                a.QUANTITE AS quantity,
                a.UNITE AS unit,
                a.LOCALISATION AS location,
                a.STATUT AS status,
                a.PUBLIC_VISIBILITY_AT AS priority_until,
                a.PICKUP_START AS pickup_start,
                a.PICKUP_END AS pickup_end,
                a.DATE_EXPIRATION AS expiration_date,
                a.ZONE_ID AS pickup_address_id,
                a.CREATED_AT AS created_at,
                a.UPDATED_AT AS updated_at
             FROM ANNONCES a
             WHERE a.ID_ANNONCE = :id
             FETCH FIRST 1 ROWS ONLY'
        );
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        return $item ?: null;
    }

    public function create(array $data): int
    {
        $db = $this->requireDb();
        $id = $this->nextId('ANNONCES', 'ID_ANNONCE');
        $stmt = $db->prepare(
            'INSERT INTO ANNONCES (
                ID_ANNONCE, TITRE, DESCRIPTION, TYPE_ALIMENT, QUANTITE, UNITE, LOCALISATION, STATUT,
                PUBLIC_VISIBILITY_AT, PICKUP_START, PICKUP_END, DATE_EXPIRATION, CATEGORIE_ID, ZONE_ID,
                PROPRIETAIRE_ID, CREATED_AT, UPDATED_AT
             ) VALUES (
                :id, :title, :description, :food_type, :quantity, :unit, :location, :status,
                :priority_until, :pickup_start, :pickup_end, :expiration_date, :category_id, :pickup_address_id,
                :organization_id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
             )'
        );
        $stmt->execute([
            'id' => $id,
            'organization_id' => $data['organization_id'],
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'food_type' => $data['food_type'] ?? $data['title'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'location' => $data['location'] ?? null,
            'expiration_date' => $data['expiration_date'],
            'pickup_start' => $data['pickup_start'],
            'pickup_end' => $data['pickup_end'],
            'pickup_address_id' => $data['pickup_address_id'],
            'status' => $data['status'],
            'priority_until' => ($data['priority_until'] ?? '') !== '' ? $data['priority_until'] : $data['pickup_start'],
        ]);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->requireDb()->prepare(
            'UPDATE ANNONCES
             SET CATEGORIE_ID = :category_id, TITRE = :title, DESCRIPTION = :description, TYPE_ALIMENT = :food_type,
                 QUANTITE = :quantity, UNITE = :unit, LOCALISATION = :location, DATE_EXPIRATION = :expiration_date,
                 PICKUP_START = :pickup_start, PICKUP_END = :pickup_end, STATUT = :status,
                 PUBLIC_VISIBILITY_AT = :priority_until, ZONE_ID = :pickup_address_id, UPDATED_AT = CURRENT_TIMESTAMP
             WHERE ID_ANNONCE = :id'
        );
        $stmt->execute([
            'id' => $id,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'food_type' => $data['food_type'] ?? $data['title'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'location' => $data['location'] ?? null,
            'expiration_date' => $data['expiration_date'],
            'pickup_start' => $data['pickup_start'],
            'pickup_end' => $data['pickup_end'],
            'status' => $data['status'],
            'priority_until' => ($data['priority_until'] ?? '') !== '' ? $data['priority_until'] : $data['pickup_start'],
            'pickup_address_id' => $data['pickup_address_id'],
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->requireDb()->prepare('DELETE FROM ANNONCES WHERE ID_ANNONCE = :id');
        $stmt->execute(['id' => $id]);
    }
}
