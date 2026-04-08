<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class OrganizationModel extends BaseModel
{
    public function findByOwner(int $ownerUserId): ?array
    {
        $stmt = $this->requireDb()->prepare(
            'SELECT organizations.*, addresses.id AS address_id, addresses.address_line, addresses.city, addresses.governorate
             FROM organizations
             LEFT JOIN organization_addresses ON organization_addresses.organization_id = organizations.id AND organization_addresses.is_primary = 1
             LEFT JOIN addresses ON addresses.id = organization_addresses.address_id
             WHERE organizations.owner_user_id = :owner_user_id
             LIMIT 1'
        );
        $stmt->execute(['owner_user_id' => $ownerUserId]);

        $organization = $stmt->fetch(PDO::FETCH_ASSOC);
        return $organization ?: null;
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
        $db = $this->requireDb();
        $startedTransaction = false;

        if (!$db->inTransaction()) {
            $db->beginTransaction();
            $startedTransaction = true;
        }

        try {
            $orgStmt = $db->prepare(
                'INSERT INTO organizations (owner_user_id, organization_type, name, legal_identifier, email, phone, description, is_verified)
                 VALUES (:owner_user_id, :organization_type, :name, :legal_identifier, :email, :phone, :description, 0)'
            );
            $orgStmt->execute([
                'owner_user_id' => $ownerUserId,
                'organization_type' => $organizationType,
                'name' => $data['name'],
                'legal_identifier' => $data['legal_identifier'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            $organizationId = (int) $db->lastInsertId();

            $addressStmt = $db->prepare(
                'INSERT INTO addresses (label, address_line, city, governorate)
                 VALUES (:label, :address_line, :city, :governorate)'
            );
            $addressStmt->execute([
                'label' => 'Adresse principale',
                'address_line' => $data['address_line'] ?? 'A definir',
                'city' => $data['city'] ?? 'Tunis',
                'governorate' => $data['governorate'] ?? 'Tunis',
            ]);

            $addressId = (int) $db->lastInsertId();

            $linkStmt = $db->prepare(
                'INSERT INTO organization_addresses (organization_id, address_id, is_primary)
                 VALUES (:organization_id, :address_id, 1)'
            );
            $linkStmt->execute([
                'organization_id' => $organizationId,
                'address_id' => $addressId,
            ]);

            if ($startedTransaction) {
                $db->commit();
            }

            return $organizationId;
        } catch (\Throwable $exception) {
            if ($startedTransaction && $db->inTransaction()) {
                $db->rollBack();
            }

            throw $exception;
        }
    }
}
