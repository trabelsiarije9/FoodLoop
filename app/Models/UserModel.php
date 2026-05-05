<?php
declare(strict_types=1);

namespace App\Models;

final class UserModel extends BaseModel
{
    public function findByEmail(string $email): array|false
    {
        $sql = '
            SELECT
                u.ID_UTIL,
                u.EMAIL,
                u.MOT_DE_PASSE,
                u.PRENOM,
                u.NOM,
                r.CODE AS ROLE_CODE
            FROM UTILISATEURS u
            INNER JOIN ROLES r ON r.ID_ROLE = u.ROLE_ID
            WHERE u.EMAIL = :email
        ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS TOTAL FROM UTILISATEURS WHERE EMAIL = :email');
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch();

        return (int) ($row['TOTAL'] ?? 0) > 0;
    }

    public function getRoleId(string $roleCode): ?int
    {
        $stmt = $this->pdo->prepare('SELECT ID_ROLE FROM ROLES WHERE CODE = :role_code');
        $stmt->bindValue(':role_code', $roleCode);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row === false ? null : (int) $row['ID_ROLE'];
    }

    public function create(array $data): int
    {
        $userId = $this->nextId('UTILISATEURS', 'ID_UTIL');
        $roleId = $this->getRoleId($data['oracle_role_code']);

        if ($roleId === null) {
            throw new \RuntimeException('Role Oracle introuvable.');
        }

        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare('
            INSERT INTO UTILISATEURS (
                ID_UTIL,
                ROLE_ID,
                PRENOM,
                NOM,
                EMAIL,
                MOT_DE_PASSE,
                NUM_TEL,
                ADRESSE,
                CREATED_AT,
                UPDATED_AT
            ) VALUES (
                :id_util,
                :role_id,
                :prenom,
                :nom,
                :email,
                :mot_de_passe,
                :num_tel,
                :adresse,
                CURRENT_TIMESTAMP,
                CURRENT_TIMESTAMP
            )
        ');
        $stmt->execute([
            'id_util' => $userId,
            'role_id' => $roleId,
            'prenom' => $data['first_name'],
            'nom' => $data['last_name'],
            'email' => $data['email'],
            'mot_de_passe' => password_hash($data['password'], PASSWORD_DEFAULT),
            'num_tel' => $data['phone'],
            'adresse' => $data['address'],
        ]);

        if ($data['frontend_role'] === 'commerce') {
            $stmt = $this->pdo->prepare('
                INSERT INTO PROPRIETAIRES_COMMERCE (
                    ID_COMMERCE,
                    NOM_COMMERCE,
                    TYPE_COMMERCE,
                    BUSINESS_LICENCE,
                    CREATED_AT
                ) VALUES (
                    :id_commerce,
                    :nom_commerce,
                    :type_commerce,
                    :business_licence,
                    CURRENT_TIMESTAMP
                )
            ');
            $stmt->execute([
                'id_commerce' => $userId,
                'nom_commerce' => $data['organization_name'],
                'type_commerce' => 'Commerce',
                'business_licence' => 'LIC-' . $userId . '-AUTO',
            ]);
        }

        if ($data['frontend_role'] === 'admin_association') {
            $organizationId = $this->nextId('ORGANIZATIONS', 'ID');

            $stmtOrganization = $this->pdo->prepare('
                INSERT INTO ORGANIZATIONS (
                    ID,
                    NAME,
                    ORGANIZATION_TYPE,
                    CREATED_AT
                ) VALUES (
                    :id,
                    :name,
                    :organization_type,
                    CURRENT_TIMESTAMP
                )
            ');
            $stmtOrganization->execute([
                'id' => $organizationId,
                'name' => $data['organization_name'],
                'organization_type' => 'association',
            ]);

            $stmtAssociation = $this->pdo->prepare('
                INSERT INTO ADMINS_ASSOCIATION (
                    ID_ADMIN_ASSOCIATION,
                    ORGANIZATION_ID,
                    NOM_ASSOCIATION,
                    CREATED_AT
                ) VALUES (
                    :id_admin_association,
                    :organization_id,
                    :nom_association,
                    CURRENT_TIMESTAMP
                )
            ');
            $stmtAssociation->execute([
                'id_admin_association' => $userId,
                'organization_id' => $organizationId,
                'nom_association' => $data['organization_name'],
            ]);
        }

        $this->pdo->commit();

        return $userId;
    }
}
