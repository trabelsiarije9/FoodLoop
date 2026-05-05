<?php
declare(strict_types=1);

namespace App\Models;

final class FoodItemModel extends BaseModel
{
    public function listFeedAnnouncements(string $frontendRole): array
    {
        $statuses = $frontendRole === 'admin_association'
            ? ['available', 'priority_access']
            : ['available'];

        $placeholders = [];
        foreach ($statuses as $index => $status) {
            $placeholders[] = ':status_' . $index;
        }

        $stmt = $this->pdo->prepare('
            SELECT
                a.ID_ANNONCE,
                a.TITRE,
                a.DESCRIPTION,
                a.TYPE_ALIMENT,
                a.PRIX,
                a.QUANTITE,
                a.UNITE,
                a.LOCALISATION,
                a.STATUT,
                TO_CHAR(a.PICKUP_START, \'HH24:MI\') AS PICKUP_START_TIME,
                TO_CHAR(a.PICKUP_END, \'HH24:MI\') AS PICKUP_END_TIME,
                NVL(c.NOM, a.TYPE_ALIMENT) AS CATEGORY_NAME,
                NVL(z.NOM, a.LOCALISATION) AS ZONE_NAME,
                NVL(z.GOUVERNORAT, a.LOCALISATION) AS ZONE_CITY
            FROM ANNONCES a
            LEFT JOIN CATEGORIES c ON c.ID_CAT = a.CATEGORIE_ID
            LEFT JOIN ZONES_GEOGRAPHIQUES z ON z.ID_ZONE = a.ZONE_ID
            WHERE a.STATUT IN (' . implode(', ', $placeholders) . ')
              AND a.QUANTITE > 0
            ORDER BY a.CREATED_AT DESC, a.ID_ANNONCE DESC
        ');

        foreach ($statuses as $index => $status) {
            $stmt->bindValue(':status_' . $index, $status);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function listOwnerAnnouncements(int $ownerId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT
                a.ID_ANNONCE,
                a.TITRE,
                a.DESCRIPTION,
                a.TYPE_ALIMENT,
                a.PRIX,
                a.QUANTITE,
                a.UNITE,
                a.LOCALISATION,
                a.STATUT,
                TO_CHAR(a.PICKUP_START, \'HH24:MI\') AS PICKUP_START_TIME,
                TO_CHAR(a.PICKUP_END, \'HH24:MI\') AS PICKUP_END_TIME,
                NVL(c.NOM, a.TYPE_ALIMENT) AS CATEGORY_NAME,
                NVL(z.NOM, a.LOCALISATION) AS ZONE_NAME
            FROM ANNONCES a
            LEFT JOIN CATEGORIES c ON c.ID_CAT = a.CATEGORIE_ID
            LEFT JOIN ZONES_GEOGRAPHIQUES z ON z.ID_ZONE = a.ZONE_ID
            WHERE a.PROPRIETAIRE_ID = :owner_id
            ORDER BY a.CREATED_AT DESC, a.ID_ANNONCE DESC
        ');
        $stmt->bindValue(':owner_id', $ownerId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findAnnonceSnapshots(array $annonceIds): array
    {
        $normalizedIds = array_values(array_unique(array_filter(array_map(static fn ($id) => (int) $id, $annonceIds), static fn (int $id) => $id > 0)));
        if ($normalizedIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($normalizedIds as $index => $id) {
            $placeholder = ':id_' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $id;
        }

        $stmt = $this->pdo->prepare('
            SELECT
                ID_ANNONCE,
                TITRE,
                DESCRIPTION,
                LOCALISATION,
                PRIX,
                QUANTITE,
                STATUT
            FROM ANNONCES
            WHERE ID_ANNONCE IN (' . implode(', ', $placeholders) . ')
        ');

        foreach ($params as $placeholder => $value) {
            $stmt->bindValue($placeholder, $value, \PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function commerceExists(int $commerceId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS TOTAL FROM PROPRIETAIRES_COMMERCE WHERE ID_COMMERCE = :id');
        $stmt->bindValue(':id', $commerceId);
        $stmt->execute();
        $row = $stmt->fetch();

        return (int) ($row['TOTAL'] ?? 0) > 0;
    }

    public function resolveZoneId(int $zoneId): int
    {
        if ($zoneId > 0) {
            return $zoneId;
        }

        $stmt = $this->pdo->prepare('SELECT MIN(ID_ZONE) AS ID_ZONE FROM ZONES_GEOGRAPHIQUES');
        $stmt->execute();
        $row = $stmt->fetch();

        return (int) ($row['ID_ZONE'] ?? 0);
    }

    public function resolveZoneIdFromLocation(string $location, int $zoneId = 0): int
    {
        if ($zoneId > 0) {
            return $zoneId;
        }

        $normalizedLocation = trim($location);
        if ($normalizedLocation !== '') {
            $stmt = $this->pdo->prepare('
                SELECT ID_ZONE
                FROM ZONES_GEOGRAPHIQUES
                WHERE UPPER(NOM) = UPPER(:location)
                   OR UPPER(VILLE_NOM) = UPPER(:location)
                   OR UPPER(GOUVERNORAT) = UPPER(:location)
                ORDER BY ID_ZONE
            ');
            $stmt->bindValue(':location', $normalizedLocation);
            $stmt->execute();
            $row = $stmt->fetch();

            if ($row !== false) {
                return (int) ($row['ID_ZONE'] ?? 0);
            }
        }

        return $this->resolveZoneId(0);
    }

    public function resolveCategoryIdFromType(string $type, int $categoryId = 0): int
    {
        if ($categoryId > 0) {
            return $categoryId;
        }

        $normalizedType = mb_strtolower(trim($type));
        $candidateNames = match (true) {
            str_contains($normalizedType, 'boulanger') => ['Boulangerie'],
            str_contains($normalizedType, 'plat'), str_contains($normalizedType, 'restauration'), str_contains($normalizedType, 'repas') => ['Plats cuisines'],
            str_contains($normalizedType, 'lait'), str_contains($normalizedType, 'yaourt'), str_contains($normalizedType, 'fromage') => ['Produits laitiers'],
            default => ['Epicerie fraiche'],
        };

        $stmt = $this->pdo->prepare('SELECT ID_CAT FROM CATEGORIES WHERE NOM = :nom');
        foreach ($candidateNames as $name) {
            $stmt->bindValue(':nom', $name);
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row !== false) {
                return (int) ($row['ID_CAT'] ?? 0);
            }
        }

        $stmtFallback = $this->pdo->prepare('SELECT MIN(ID_CAT) AS ID_CAT FROM CATEGORIES');
        $stmtFallback->execute();
        $row = $stmtFallback->fetch();

        return (int) ($row['ID_CAT'] ?? 0);
    }

    public function create(array $data): int
    {
        $annonceId = $this->nextId('ANNONCES', 'ID_ANNONCE');

        $stmt = $this->pdo->prepare('
            INSERT INTO ANNONCES (
                ID_ANNONCE,
                TITRE,
                DESCRIPTION,
                TYPE_ALIMENT,
                PRIX,
                QUANTITE,
                UNITE,
                LOCALISATION,
                STATUT,
                PUBLIC_VISIBILITY_AT,
                PICKUP_START,
                PICKUP_END,
                DATE_EXPIRATION,
                CATEGORIE_ID,
                ZONE_ID,
                PROPRIETAIRE_ID,
                CREATED_AT,
                UPDATED_AT
            ) VALUES (
                :id_annonce,
                :titre,
                :description,
                :type_aliment,
                :prix,
                :quantite,
                :unite,
                :localisation,
                :statut,
                TO_TIMESTAMP(:public_visibility_at, \'YYYY-MM-DD HH24:MI:SS\'),
                TO_TIMESTAMP(:pickup_start, \'YYYY-MM-DD HH24:MI:SS\'),
                TO_TIMESTAMP(:pickup_end, \'YYYY-MM-DD HH24:MI:SS\'),
                TO_TIMESTAMP(:date_expiration, \'YYYY-MM-DD HH24:MI:SS\'),
                :categorie_id,
                :zone_id,
                :proprietaire_id,
                CURRENT_TIMESTAMP,
                CURRENT_TIMESTAMP
            )
        ');
        $stmt->execute([
            'id_annonce' => $annonceId,
            'titre' => $data['title'],
            'description' => $data['description'],
            'type_aliment' => $data['type'],
            'prix' => $data['price'],
            'quantite' => $data['quantity'],
            'unite' => $data['unit'],
            'localisation' => $data['location'],
            'statut' => $data['status'],
            'public_visibility_at' => $data['public_visibility_at'],
            'pickup_start' => $data['pickup_start'],
            'pickup_end' => $data['pickup_end'],
            'date_expiration' => $data['expiration_date'],
            'categorie_id' => $data['category_id'],
            'zone_id' => $data['zone_id'],
            'proprietaire_id' => $data['owner_id'],
        ]);

        return $annonceId;
    }
}
