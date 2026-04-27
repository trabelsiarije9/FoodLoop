<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class OrganizationModel extends BaseModel
{
    public function findByOwner(int $ownerUserId): ?array
    {
        $userStmt = $this->requireDb()->prepare(
            'SELECT r.CODE
             FROM UTILISATEURS u
             INNER JOIN ROLES r ON r.ID_ROLE = u.ROLE_ID
             WHERE u.ID_UTIL = :owner_user_id
             FETCH FIRST 1 ROWS ONLY'
        );
        $userStmt->execute(['owner_user_id' => $ownerUserId]);
        $roleCode = $userStmt->fetchColumn();

        if ($roleCode === 'business_owner') {
            $stmt = $this->requireDb()->prepare(
                'SELECT
                    p.ID_COMMERCE AS id,
                    p.NOM_COMMERCE AS name,
                    ''business'' AS organization_type,
                    p.BUSINESS_LICENCE AS legal_identifier,
                    COALESCE(z.ID_ZONE, (SELECT MIN(ID_ZONE) FROM ZONES_GEOGRAPHIQUES)) AS address_id,
                    z.NOM AS address_label,
                    z.VILLE_NOM AS city,
                    z.GOUVERNORAT AS governorate
                 FROM PROPRIETAIRES_COMMERCE p
                 LEFT JOIN ZONES_GEOGRAPHIQUES z ON z.USER_ID = :owner_user_id
                 WHERE p.ID_COMMERCE = :owner_user_id
                 FETCH FIRST 1 ROWS ONLY'
            );
            $stmt->execute(['owner_user_id' => $ownerUserId]);
            $organization = $stmt->fetch(PDO::FETCH_ASSOC);
            return $organization ?: null;
        }

        if ($roleCode === 'association_admin') {
            $stmt = $this->requireDb()->prepare(
                'SELECT
                    aa.ID_ADMIN_ASSOCIATION AS id,
                    aa.ORGANIZATION_ID AS organization_record_id,
                    aa.NOM_ASSOCIATION AS name,
                    ''association'' AS organization_type,
                    aa.ORGANIZATION_ID AS address_id,
                    o.NAME AS organization_name
                 FROM ADMINS_ASSOCIATION aa
                 LEFT JOIN ORGANIZATIONS o ON o.ID = aa.ORGANIZATION_ID
                 WHERE aa.ID_ADMIN_ASSOCIATION = :owner_user_id
                 FETCH FIRST 1 ROWS ONLY'
            );
            $stmt->execute(['owner_user_id' => $ownerUserId]);
            $organization = $stmt->fetch(PDO::FETCH_ASSOC);
            return $organization ?: null;
        }

        return null;
    }

    public function createForBusinessOwner(int $ownerUserId, array $data): int
    {
        return $this->createForOwner($ownerUserId, 'business', $data);
    }

    public function createForAssociation(int $ownerUserId, array $data): int
    {
        return $this->createForOwner($ownerUserId, 'association', $data);
    }

    public function createForOwner(int $ownerUserId, string $organizationType, array $data): int
    {
        return $ownerUserId;
    }
}
