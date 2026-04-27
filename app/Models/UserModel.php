<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class UserModel extends BaseModel
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->requireDb()->prepare(
            'SELECT
                u.ID_UTIL AS id,
                u.ROLE_ID AS role_id,
                u.PRENOM AS first_name,
                u.NOM AS last_name,
                u.EMAIL AS email,
                u.MOT_DE_PASSE AS password_hash,
                u.NUM_TEL AS phone,
                u.ADRESSE AS address_line,
                1 AS is_active,
                u.CREATED_AT AS created_at,
                u.UPDATED_AT AS updated_at,
                CASE
                    WHEN r.CODE = ''citizen'' THEN ''regular_user''
                    WHEN r.CODE = ''super_admin'' THEN ''system_admin''
                    ELSE r.CODE
                END AS role_code,
                r.NOM AS role_name
             FROM UTILISATEURS u
             INNER JOIN ROLES r ON r.ID_ROLE = u.ROLE_ID
             WHERE u.EMAIL = :email
             FETCH FIRST 1 ROWS ONLY'
        );
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->requireDb()->prepare(
            'SELECT
                u.ID_UTIL AS id,
                u.ROLE_ID AS role_id,
                u.PRENOM AS first_name,
                u.NOM AS last_name,
                u.EMAIL AS email,
                u.MOT_DE_PASSE AS password_hash,
                u.NUM_TEL AS phone,
                u.ADRESSE AS address_line,
                1 AS is_active,
                u.CREATED_AT AS created_at,
                u.UPDATED_AT AS updated_at,
                CASE
                    WHEN r.CODE = ''citizen'' THEN ''regular_user''
                    WHEN r.CODE = ''super_admin'' THEN ''system_admin''
                    ELSE r.CODE
                END AS role_code,
                r.NOM AS role_name
             FROM UTILISATEURS u
             INNER JOIN ROLES r ON r.ID_ROLE = u.ROLE_ID
             WHERE u.ID_UTIL = :id
             FETCH FIRST 1 ROWS ONLY'
        );
        $stmt->execute(['id' => $id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function all(): array
    {
        $stmt = $this->requireDb()->query(
            'SELECT
                u.ID_UTIL AS id,
                u.PRENOM AS first_name,
                u.NOM AS last_name,
                u.EMAIL AS email,
                u.NUM_TEL AS phone,
                1 AS is_active,
                u.CREATED_AT AS created_at,
                CASE
                    WHEN r.CODE = ''citizen'' THEN ''regular_user''
                    WHEN r.CODE = ''super_admin'' THEN ''system_admin''
                    ELSE r.CODE
                END AS role_code,
                r.NOM AS role_name
             FROM UTILISATEURS u
             INNER JOIN ROLES r ON r.ID_ROLE = u.ROLE_ID
             ORDER BY u.CREATED_AT DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $db = $this->requireDb();
        $roleId = $this->findRoleIdByCode($data['role_code']);
        $userId = $this->nextId('UTILISATEURS', 'ID_UTIL');

        $db->beginTransaction();

        try {
            $stmt = $db->prepare(
                'INSERT INTO UTILISATEURS (
                    ID_UTIL, ROLE_ID, PRENOM, NOM, EMAIL, MOT_DE_PASSE, NUM_TEL, ADRESSE, CREATED_AT, UPDATED_AT
                 ) VALUES (
                    :id, :role_id, :first_name, :last_name, :email, :password_hash, :phone, :address_line, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                 )'
            );
            $stmt->execute([
                'id' => $userId,
                'role_id' => $roleId,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'phone' => $data['phone'] ?: null,
                'address_line' => $data['address_line'] ?: null,
            ]);

            $this->createRoleProfile($userId, $data['role_code'], $data);

            $db->commit();
            return $userId;
        } catch (\Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw $exception;
        }
    }

    public function update(int $id, array $data): void
    {
        $roleId = $this->findRoleIdByCode($data['role_code']);
        $params = [
            'id' => $id,
            'role_id' => $roleId,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
        ];

        $sql = 'UPDATE UTILISATEURS
                SET ROLE_ID = :role_id, PRENOM = :first_name, NOM = :last_name,
                    EMAIL = :email, NUM_TEL = :phone, UPDATED_AT = CURRENT_TIMESTAMP';

        if (!empty($data['password'])) {
            $sql .= ', MOT_DE_PASSE = :password_hash';
            $params['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE ID_UTIL = :id';

        $stmt = $this->requireDb()->prepare($sql);
        $stmt->execute($params);
    }

    public function delete(int $id): void
    {
        $stmt = $this->requireDb()->prepare('DELETE FROM UTILISATEURS WHERE ID_UTIL = :id');
        $stmt->execute(['id' => $id]);
    }

    public function roleOptions(): array
    {
        $stmt = $this->requireDb()->query(
            'SELECT
                CASE
                    WHEN CODE = ''citizen'' THEN ''regular_user''
                    WHEN CODE = ''super_admin'' THEN ''system_admin''
                    ELSE CODE
                END AS code,
                NOM AS name
             FROM ROLES
             ORDER BY NOM ASC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function touchLogin(int $id): void
    {
        $stmt = $this->requireDb()->prepare('UPDATE UTILISATEURS SET UPDATED_AT = CURRENT_TIMESTAMP WHERE ID_UTIL = :id');
        $stmt->execute(['id' => $id]);
    }

    private function findRoleIdByCode(string $code): int
    {
        $normalized = match ($code) {
            'regular_user' => 'citizen',
            'system_admin' => 'super_admin',
            default => $code,
        };

        $stmt = $this->requireDb()->prepare('SELECT ID_ROLE FROM ROLES WHERE CODE = :code FETCH FIRST 1 ROWS ONLY');
        $stmt->execute(['code' => $normalized]);
        $roleId = $stmt->fetchColumn();

        if ($roleId === false) {
            throw new \RuntimeException('Role introuvable : ' . $code);
        }

        return (int) $roleId;
    }

    private function createRoleProfile(int $userId, string $roleCode, array $data): void
    {
        if ($roleCode === 'business_owner') {
            $stmt = $this->requireDb()->prepare(
                'INSERT INTO PROPRIETAIRES_COMMERCE (ID_COMMERCE, NOM_COMMERCE, TYPE_COMMERCE, BUSINESS_LICENCE, CREATED_AT)
                 VALUES (:id, :name, :type, :business_licence, CURRENT_TIMESTAMP)'
            );
            $stmt->execute([
                'id' => $userId,
                'name' => $data['organization_name'] ?? (($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
                'type' => 'Commerce',
                'business_licence' => $data['business_license'] ?? null,
            ]);

            $zoneId = $this->nextId('ZONES_GEOGRAPHIQUES', 'ID_ZONE');
            $zoneStmt = $this->requireDb()->prepare(
                'INSERT INTO ZONES_GEOGRAPHIQUES (ID_ZONE, NOM, CODE_POSTAL, VILLE_NOM, GOUVERNORAT, USER_ID, CREATED_AT)
                 VALUES (:id, :name, :postal_code, :city, :governorate, :user_id, CURRENT_TIMESTAMP)'
            );
            $zoneStmt->execute([
                'id' => $zoneId,
                'name' => $data['city'] ?? 'Zone FoodLoop',
                'postal_code' => null,
                'city' => $data['city'] ?? 'Tunis',
                'governorate' => $data['governorate'] ?? 'Tunis',
                'user_id' => $userId,
            ]);
            return;
        }

        if ($roleCode === 'association_admin') {
            $organizationId = $this->nextId('ORGANIZATIONS', 'ID');

            $orgStmt = $this->requireDb()->prepare(
                'INSERT INTO ORGANIZATIONS (ID, NAME, ORGANIZATION_TYPE, CREATED_AT)
                 VALUES (:id, :name, ''association'', CURRENT_TIMESTAMP)'
            );
            $orgStmt->execute([
                'id' => $organizationId,
                'name' => $data['organization_name'] ?? (($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
            ]);

            $stmt = $this->requireDb()->prepare(
                'INSERT INTO ADMINS_ASSOCIATION (ID_ADMIN_ASSOCIATION, ORGANIZATION_ID, NOM_ASSOCIATION, CREATED_AT)
                 VALUES (:id, :organization_id, :name, CURRENT_TIMESTAMP)'
            );
            $stmt->execute([
                'id' => $userId,
                'organization_id' => $organizationId,
                'name' => $data['organization_name'] ?? (($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
            ]);
        }
    }
}
