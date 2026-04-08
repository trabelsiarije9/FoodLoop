<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class UserModel extends BaseModel
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->requireDb()->prepare(
            'SELECT users.*, roles.code AS role_code, roles.name AS role_name
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->requireDb()->prepare(
            'SELECT users.*, roles.code AS role_code, roles.name AS role_name
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function all(): array
    {
        $stmt = $this->requireDb()->query(
            'SELECT users.id, users.first_name, users.last_name, users.email, users.phone, users.is_active,
                    users.created_at, roles.code AS role_code, roles.name AS role_name
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             ORDER BY users.created_at DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $db = $this->requireDb();
        $roleId = $this->findRoleIdByCode($data['role_code']);

        $db->beginTransaction();

        try {
            $stmt = $db->prepare(
                'INSERT INTO users (role_id, first_name, last_name, email, password_hash, phone, is_active)
                 VALUES (:role_id, :first_name, :last_name, :email, :password_hash, :phone, :is_active)'
            );
            $stmt->execute([
                'role_id' => $roleId,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'phone' => $data['phone'] ?: null,
                'is_active' => $data['is_active'] ?? 1,
            ]);

            $userId = (int) $db->lastInsertId();
            $this->createRoleProfile($userId, $data['role_code']);

            if (in_array($data['role_code'], ['business_owner', 'association_admin'], true) && !empty($data['organization_name'])) {
                $organizationModel = new OrganizationModel();
                $organizationData = [
                    'name' => $data['organization_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'description' => $data['organization_description'] ?? null,
                    'legal_identifier' => $data['role_code'] === 'business_owner'
                        ? ($data['business_license'] ?? null)
                        : ($data['association_code'] ?? null),
                    'city' => $data['city'] ?? 'Tunis',
                    'governorate' => $data['governorate'] ?? 'Tunis',
                    'address_line' => $data['address_line'] ?? 'A definir',
                ];

                if ($data['role_code'] === 'business_owner') {
                    $organizationModel->createForBusinessOwner($userId, $organizationData);
                } else {
                    $organizationModel->createForAssociation($userId, $organizationData);
                }
            }

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
            'is_active' => $data['is_active'] ?? 1,
        ];

        $sql = 'UPDATE users
                SET role_id = :role_id, first_name = :first_name, last_name = :last_name,
                    email = :email, phone = :phone, is_active = :is_active';

        if (!empty($data['password'])) {
            $sql .= ', password_hash = :password_hash';
            $params['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = :id';

        $stmt = $this->requireDb()->prepare($sql);
        $stmt->execute($params);
    }

    public function delete(int $id): void
    {
        $stmt = $this->requireDb()->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function roleOptions(): array
    {
        $stmt = $this->requireDb()->query('SELECT code, name FROM roles ORDER BY name ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function touchLogin(int $id): void
    {
        $stmt = $this->requireDb()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    private function findRoleIdByCode(string $code): int
    {
        $stmt = $this->requireDb()->prepare('SELECT id FROM roles WHERE code = :code LIMIT 1');
        $stmt->execute(['code' => $code]);
        $roleId = $stmt->fetchColumn();

        if ($roleId === false) {
            throw new \RuntimeException('Role introuvable : ' . $code);
        }

        return (int) $roleId;
    }

    private function createRoleProfile(int $userId, string $roleCode): void
    {
        $tableMap = [
            'business_owner' => 'business_owners',
            'association_admin' => 'association_admins',
            'regular_user' => 'regular_users',
            'system_admin' => 'system_admins',
        ];

        if (!isset($tableMap[$roleCode])) {
            return;
        }

        $table = $tableMap[$roleCode];
        $stmt = $this->requireDb()->prepare("INSERT INTO {$table} (user_id) VALUES (:user_id)");
        $stmt->execute(['user_id' => $userId]);
    }
}
