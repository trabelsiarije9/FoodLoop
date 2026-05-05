<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use RuntimeException;

final class SuperAdminModel extends BaseModel
{
    private ?bool $moderationTableExists = null;

    public function moderationTableExists(): bool
    {
        if ($this->moderationTableExists !== null) {
            return $this->moderationTableExists;
        }

        $stmt = $this->pdo->query("SELECT COUNT(*) AS TOTAL FROM USER_TABLES WHERE TABLE_NAME = 'ACCOUNT_MODERATION'");
        $row = $stmt->fetch();
        $this->moderationTableExists = (int) ($row['TOTAL'] ?? 0) > 0;

        return $this->moderationTableExists;
    }

    public function isSuspended(int $userId): bool
    {
        if ($userId <= 0 || !$this->moderationTableExists()) {
            return false;
        }

        $stmt = $this->pdo->prepare('
            SELECT STATUS
            FROM ACCOUNT_MODERATION
            WHERE UTILISATEUR_ID = :utilisateur_id
        ');
        $stmt->execute(['utilisateur_id' => $userId]);
        $row = $stmt->fetch();

        return $row !== false && strtolower((string) ($row['STATUS'] ?? 'active')) === 'suspended';
    }

    public function getDashboardPayload(): array
    {
        $buyers = $this->listBuyers();
        $commerces = $this->listCommerces();
        $associations = $this->listAssociations();

        $reservationsTotal = (int) ($this->pdo->query('SELECT COUNT(*) AS TOTAL FROM RESERVATIONS')->fetch()['TOTAL'] ?? 0);
        $reservationsByStatus = $this->fetchReservationsByStatus();

        return [
            'summary' => [
                'buyers_active' => count(array_filter($buyers, static fn (array $item): bool => $item['status'] === 'Actif')),
                'commerces_active' => count(array_filter($commerces, static fn (array $item): bool => $item['status'] === 'Actif')),
                'associations_active' => count(array_filter($associations, static fn (array $item): bool => $item['status'] === 'Actif')),
                'reservations_total' => $reservationsTotal,
            ],
            'buyers' => $buyers,
            'commerces' => $commerces,
            'associations' => $associations,
            'reservations_by_status' => $reservationsByStatus,
            'account_mix' => [
                ['label' => 'Acheteurs', 'value' => count($buyers)],
                ['label' => 'Commerces', 'value' => count($commerces)],
                ['label' => 'Associations', 'value' => count($associations)],
            ],
            'reports' => $this->listReports(),
            'system' => $this->getSystemSnapshot($buyers, $commerces, $associations),
        ];
    }

    public function listBuyers(): array
    {
        $sql = '
            SELECT
                u.ID_UTIL,
                u.PRENOM,
                u.NOM,
                u.EMAIL' . $this->moderationStatusSelect() . '
            FROM UTILISATEURS u
            INNER JOIN ROLES r ON r.ID_ROLE = u.ROLE_ID
            ' . $this->moderationJoin() . '
            WHERE r.CODE = :role_code
            ORDER BY LOWER(u.NOM), LOWER(u.PRENOM), LOWER(u.EMAIL)
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['role_code' => 'citizen']);

        return array_map(static function (array $row): array {
            $name = trim((string) ($row['PRENOM'] ?? '') . ' ' . (string) ($row['NOM'] ?? ''));

            return [
                'id' => (int) $row['ID_UTIL'],
                'name' => $name !== '' ? $name : (string) $row['EMAIL'],
                'email' => (string) $row['EMAIL'],
                'status' => strtolower((string) ($row['ACCOUNT_STATUS'] ?? 'active')) === 'suspended' ? 'Suspendu' : 'Actif',
            ];
        }, $stmt->fetchAll());
    }

    public function listCommerces(): array
    {
        $sql = '
            SELECT
                u.ID_UTIL,
                u.EMAIL,
                pc.NOM_COMMERCE,
                pc.TYPE_COMMERCE' . $this->moderationStatusSelect() . '
            FROM UTILISATEURS u
            INNER JOIN ROLES r ON r.ID_ROLE = u.ROLE_ID
            INNER JOIN PROPRIETAIRES_COMMERCE pc ON pc.ID_COMMERCE = u.ID_UTIL
            ' . $this->moderationJoin() . '
            WHERE r.CODE = :role_code
            ORDER BY LOWER(pc.NOM_COMMERCE), LOWER(u.EMAIL)
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['role_code' => 'business_owner']);

        return array_map(static function (array $row): array {
            return [
                'id' => (int) $row['ID_UTIL'],
                'name' => (string) $row['NOM_COMMERCE'],
                'email' => (string) $row['EMAIL'],
                'type' => (string) $row['TYPE_COMMERCE'],
                'status' => strtolower((string) ($row['ACCOUNT_STATUS'] ?? 'active')) === 'suspended' ? 'Suspendu' : 'Actif',
            ];
        }, $stmt->fetchAll());
    }

    public function listAssociations(): array
    {
        $sql = '
            SELECT
                u.ID_UTIL,
                u.EMAIL,
                aa.NOM_ASSOCIATION,
                o.NAME AS ORGANIZATION_NAME' . $this->moderationStatusSelect() . '
            FROM UTILISATEURS u
            INNER JOIN ROLES r ON r.ID_ROLE = u.ROLE_ID
            INNER JOIN ADMINS_ASSOCIATION aa ON aa.ID_ADMIN_ASSOCIATION = u.ID_UTIL
            INNER JOIN ORGANIZATIONS o ON o.ID = aa.ORGANIZATION_ID
            ' . $this->moderationJoin() . '
            WHERE r.CODE = :role_code
            ORDER BY LOWER(aa.NOM_ASSOCIATION), LOWER(u.EMAIL)
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['role_code' => 'association_admin']);

        return array_map(static function (array $row): array {
            return [
                'id' => (int) $row['ID_UTIL'],
                'name' => (string) ($row['NOM_ASSOCIATION'] ?: $row['ORGANIZATION_NAME']),
                'email' => (string) $row['EMAIL'],
                'status' => strtolower((string) ($row['ACCOUNT_STATUS'] ?? 'active')) === 'suspended' ? 'Suspendu' : 'Actif',
            ];
        }, $stmt->fetchAll());
    }

    public function suspendAccount(int $userId, string $reason = ''): void
    {
        $this->assertUserExists($userId);

        if (!$this->moderationTableExists()) {
            throw new RuntimeException('La table ACCOUNT_MODERATION est absente. Lancez la migration superadmin.');
        }

        $stmt = $this->pdo->prepare('
            MERGE INTO ACCOUNT_MODERATION m
            USING (SELECT :utilisateur_id AS UTILISATEUR_ID FROM dual) src
            ON (m.UTILISATEUR_ID = src.UTILISATEUR_ID)
            WHEN MATCHED THEN UPDATE SET
                m.STATUS = :status,
                m.REASON = :reason,
                m.UPDATED_AT = CURRENT_TIMESTAMP
            WHEN NOT MATCHED THEN INSERT (
                ID,
                UTILISATEUR_ID,
                STATUS,
                REASON,
                CREATED_AT,
                UPDATED_AT
            ) VALUES (
                :id,
                :utilisateur_id_insert,
                :status_insert,
                :reason_insert,
                CURRENT_TIMESTAMP,
                CURRENT_TIMESTAMP
            )
        ');
        $nextId = $this->nextId('ACCOUNT_MODERATION', 'ID');
        $stmt->execute([
            'utilisateur_id' => $userId,
            'status' => 'suspended',
            'reason' => $reason !== '' ? $reason : 'Suspendu par le superadmin.',
            'id' => $nextId,
            'utilisateur_id_insert' => $userId,
            'status_insert' => 'suspended',
            'reason_insert' => $reason !== '' ? $reason : 'Suspendu par le superadmin.',
        ]);
    }

    public function unsuspendAccount(int $userId): void
    {
        if (!$this->moderationTableExists()) {
            return;
        }

        $stmt = $this->pdo->prepare('
            DELETE FROM ACCOUNT_MODERATION
            WHERE UTILISATEUR_ID = :utilisateur_id
        ');
        $stmt->execute(['utilisateur_id' => $userId]);
    }

    public function deleteBuyer(int $userId): void
    {
        $this->assertRole($userId, 'citizen');

        $this->pdo->beginTransaction();
        try {
            $this->deleteReservationsForUserColumn('UTILISATEUR_ID', $userId);
            $this->pdo->prepare('DELETE FROM UTILISATEUR_CATEGORIES WHERE UTILISATEUR_ID = :id')->execute(['id' => $userId]);
            $this->deleteModerationRecord($userId);
            $this->pdo->prepare('DELETE FROM UTILISATEUR_ZONES WHERE UTILISATEUR_ID = :id')->execute(['id' => $userId]);
            $this->pdo->prepare('UPDATE ZONES_GEOGRAPHIQUES SET USER_ID = NULL WHERE USER_ID = :id')->execute(['id' => $userId]);
            $this->pdo->prepare('DELETE FROM UTILISATEURS WHERE ID_UTIL = :id')->execute(['id' => $userId]);
            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function deleteCommerce(int $userId): void
    {
        $this->assertRole($userId, 'business_owner');

        $this->pdo->beginTransaction();
        try {
            $annonceIds = $this->fetchAnnonceIdsForCommerce($userId);
            $this->deleteReservationsForAnnonceIds($annonceIds);
            $this->deleteSuggestionsForAnnonceIds($annonceIds);
            $this->pdo->prepare('DELETE FROM ANNONCES WHERE PROPRIETAIRE_ID = :id')->execute(['id' => $userId]);
            $this->pdo->prepare('DELETE FROM REPORT_CONSULTATIONS_COMMERCE WHERE PROPRIETAIRE_ID = :id')->execute(['id' => $userId]);
            $this->pdo->prepare('DELETE FROM PROPRIETAIRES_COMMERCE WHERE ID_COMMERCE = :id')->execute(['id' => $userId]);
            $this->deleteModerationRecord($userId);
            $this->pdo->prepare('UPDATE ZONES_GEOGRAPHIQUES SET USER_ID = NULL WHERE USER_ID = :id')->execute(['id' => $userId]);
            $this->pdo->prepare('DELETE FROM UTILISATEURS WHERE ID_UTIL = :id')->execute(['id' => $userId]);
            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function deleteAssociation(int $userId): void
    {
        $this->assertRole($userId, 'association_admin');

        $stmt = $this->pdo->prepare('SELECT ORGANIZATION_ID FROM ADMINS_ASSOCIATION WHERE ID_ADMIN_ASSOCIATION = :id');
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();
        if ($row === false) {
            throw new RuntimeException('Association introuvable.');
        }

        $organizationId = (int) $row['ORGANIZATION_ID'];

        $this->pdo->beginTransaction();
        try {
            $this->deleteReservationsForUserColumn('ADMIN_ASSOCIATION_ID', $userId);
            $addressIds = $this->fetchAssociationAddressIds($organizationId);
            $this->pdo->prepare('DELETE FROM ASSOCIATION_ADDRESSES WHERE ORGANIZATION_ID = :organization_id')->execute(['organization_id' => $organizationId]);
            $this->deleteAddressesByIds($addressIds);
            $this->pdo->prepare('DELETE FROM ADMINS_ASSOCIATION WHERE ID_ADMIN_ASSOCIATION = :id')->execute(['id' => $userId]);
            $this->pdo->prepare('DELETE FROM ORGANIZATIONS WHERE ID = :organization_id')->execute(['organization_id' => $organizationId]);
            $this->deleteModerationRecord($userId);
            $this->pdo->prepare('UPDATE ZONES_GEOGRAPHIQUES SET USER_ID = NULL WHERE USER_ID = :id')->execute(['id' => $userId]);
            $this->pdo->prepare('DELETE FROM UTILISATEURS WHERE ID_UTIL = :id')->execute(['id' => $userId]);
            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function fetchReservationsByStatus(): array
    {
        $stmt = $this->pdo->query('
            SELECT STATUT, COUNT(*) AS TOTAL
            FROM RESERVATIONS
            GROUP BY STATUT
            ORDER BY STATUT
        ');

        return array_map(static fn (array $row): array => [
            'label' => (string) $row['STATUT'],
            'value' => (int) $row['TOTAL'],
        ], $stmt->fetchAll());
    }

    private function listReports(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                r.ID,
                r.REPORT_TYPE,
                TO_CHAR(r.PERIOD_START, 'YYYY-MM-DD') AS PERIOD_START_LABEL,
                TO_CHAR(r.PERIOD_END, 'YYYY-MM-DD') AS PERIOD_END_LABEL,
                TO_CHAR(r.CREATED_AT, 'YYYY-MM-DD HH24:MI') AS CREATED_AT_LABEL,
                r.TOTAL_FOOD_SAVED_KG,
                r.TOTAL_RESERVATIONS,
                r.TOTAL_DISTRIBUTIONS,
                (SELECT COUNT(*) FROM REPORT_CONSULTATIONS_COMMERCE rc WHERE rc.REPORT_ID = r.ID) AS COMMERCE_VIEWS,
                (SELECT COUNT(*) FROM REPORT_CONSULT_SUPER_ADMIN rsa WHERE rsa.REPORT_ID = r.ID) AS SUPERADMIN_VIEWS
            FROM REPORTS r
            ORDER BY r.CREATED_AT DESC, r.ID DESC
        ");

        return array_map(static function (array $row): array {
            return [
                'id' => (int) $row['ID'],
                'type' => (string) $row['REPORT_TYPE'],
                'period' => (string) $row['PERIOD_START_LABEL'] . ' au ' . (string) $row['PERIOD_END_LABEL'],
                'created_at' => (string) $row['CREATED_AT_LABEL'],
                'food_saved_kg' => (float) str_replace(',', '.', (string) ($row['TOTAL_FOOD_SAVED_KG'] ?? '0')),
                'total_reservations' => (int) ($row['TOTAL_RESERVATIONS'] ?? 0),
                'total_distributions' => (int) ($row['TOTAL_DISTRIBUTIONS'] ?? 0),
                'commerce_views' => (int) ($row['COMMERCE_VIEWS'] ?? 0),
                'superadmin_views' => (int) ($row['SUPERADMIN_VIEWS'] ?? 0),
            ];
        }, $stmt->fetchAll());
    }

    private function getSystemSnapshot(array $buyers, array $commerces, array $associations): array
    {
        $suspendedTotal = 0;
        if ($this->moderationTableExists()) {
            $row = $this->pdo->query("SELECT COUNT(*) AS TOTAL FROM ACCOUNT_MODERATION WHERE STATUS = 'suspended'")->fetch();
            $suspendedTotal = (int) ($row['TOTAL'] ?? 0);
        }

        $pendingNotifications = (int) ($this->pdo->query("SELECT COUNT(*) AS TOTAL FROM NOTIFICATIONS WHERE STATUS = 'queued'")->fetch()['TOTAL'] ?? 0);
        $scheduledPickups = (int) ($this->pdo->query("SELECT COUNT(*) AS TOTAL FROM PICKUPS WHERE STATUS IN ('scheduled', 'in_progress')")->fetch()['TOTAL'] ?? 0);
        $reportsTotal = (int) ($this->pdo->query('SELECT COUNT(*) AS TOTAL FROM REPORTS')->fetch()['TOTAL'] ?? 0);

        return [
            'database' => 'Oracle FOODLOOP',
            'accounts_loaded' => count($buyers) + count($commerces) + count($associations),
            'suspended_accounts' => $suspendedTotal,
            'queued_notifications' => $pendingNotifications,
            'scheduled_pickups' => $scheduledPickups,
            'reports_total' => $reportsTotal,
            'moderation_table' => $this->moderationTableExists() ? 'ACCOUNT_MODERATION active' : 'ACCOUNT_MODERATION absente',
        ];
    }

    private function assertUserExists(int $userId): void
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS TOTAL FROM UTILISATEURS WHERE ID_UTIL = :id');
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();

        if ((int) ($row['TOTAL'] ?? 0) === 0) {
            throw new RuntimeException('Utilisateur introuvable.');
        }
    }

    private function assertRole(int $userId, string $roleCode): void
    {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) AS TOTAL
            FROM UTILISATEURS u
            INNER JOIN ROLES r ON r.ID_ROLE = u.ROLE_ID
            WHERE u.ID_UTIL = :id
              AND r.CODE = :role_code
        ');
        $stmt->execute([
            'id' => $userId,
            'role_code' => $roleCode,
        ]);
        $row = $stmt->fetch();

        if ((int) ($row['TOTAL'] ?? 0) === 0) {
            throw new RuntimeException('Le compte ne correspond pas au role attendu.');
        }
    }

    private function deleteModerationRecord(int $userId): void
    {
        if (!$this->moderationTableExists()) {
            return;
        }

        $this->pdo->prepare('DELETE FROM ACCOUNT_MODERATION WHERE UTILISATEUR_ID = :id')->execute(['id' => $userId]);
    }

    private function moderationJoin(): string
    {
        if (!$this->moderationTableExists()) {
            return '';
        }

        return 'LEFT JOIN ACCOUNT_MODERATION am ON am.UTILISATEUR_ID = u.ID_UTIL';
    }

    private function moderationStatusSelect(): string
    {
        if (!$this->moderationTableExists()) {
            return ", 'active' AS ACCOUNT_STATUS";
        }

        return ", NVL(am.STATUS, 'active') AS ACCOUNT_STATUS";
    }

    private function fetchAnnonceIdsForCommerce(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT ID_ANNONCE FROM ANNONCES WHERE PROPRIETAIRE_ID = :id');
        $stmt->execute(['id' => $userId]);

        return array_map(static fn (array $row): int => (int) $row['ID_ANNONCE'], $stmt->fetchAll());
    }

    private function fetchAssociationAddressIds(int $organizationId): array
    {
        $stmt = $this->pdo->prepare('SELECT ADDRESS_ID FROM ASSOCIATION_ADDRESSES WHERE ORGANIZATION_ID = :organization_id');
        $stmt->execute(['organization_id' => $organizationId]);

        return array_map(static fn (array $row): int => (int) $row['ADDRESS_ID'], $stmt->fetchAll());
    }

    private function deleteReservationsForUserColumn(string $column, int $userId): void
    {
        $allowedColumns = ['UTILISATEUR_ID', 'ADMIN_ASSOCIATION_ID'];
        if (!in_array($column, $allowedColumns, true)) {
            throw new RuntimeException('Colonne de reservation invalide.');
        }

        $stmt = $this->pdo->prepare("SELECT ID_RESERVATION FROM RESERVATIONS WHERE {$column} = :id");
        $stmt->execute(['id' => $userId]);
        $reservationIds = array_map(static fn (array $row): int => (int) $row['ID_RESERVATION'], $stmt->fetchAll());

        $this->deleteReservationsByIds($reservationIds);
    }

    private function deleteReservationsForAnnonceIds(array $annonceIds): void
    {
        $annonceIds = array_values(array_filter(array_map('intval', $annonceIds), static fn (int $id): bool => $id > 0));
        if ($annonceIds === []) {
            return;
        }

        $placeholders = $this->buildNamedPlaceholders('annonce', $annonceIds);
        $stmt = $this->pdo->prepare(
            'SELECT ID_RESERVATION FROM RESERVATIONS WHERE ANNONCE_ID IN (' . implode(', ', array_keys($placeholders)) . ')'
        );
        $stmt->execute($placeholders);
        $reservationIds = array_map(static fn (array $row): int => (int) $row['ID_RESERVATION'], $stmt->fetchAll());

        $this->deleteReservationsByIds($reservationIds);
    }

    private function deleteReservationsByIds(array $reservationIds): void
    {
        $reservationIds = array_values(array_filter(array_map('intval', $reservationIds), static fn (int $id): bool => $id > 0));
        if ($reservationIds === []) {
            return;
        }

        $reservationPlaceholders = $this->buildNamedPlaceholders('reservation', $reservationIds);
        $paymentStmt = $this->pdo->prepare(
            'SELECT ID_PAIEMENT FROM PAIEMENTS WHERE RESERVATION_ID IN (' . implode(', ', array_keys($reservationPlaceholders)) . ')'
        );
        $paymentStmt->execute($reservationPlaceholders);
        $paymentIds = array_map(static fn (array $row): int => (int) $row['ID_PAIEMENT'], $paymentStmt->fetchAll());

        $this->pdo->prepare(
            'DELETE FROM PICKUPS WHERE RESERVATION_ID IN (' . implode(', ', array_keys($reservationPlaceholders)) . ')'
        )->execute($reservationPlaceholders);

        $this->pdo->prepare(
            'DELETE FROM RESERVATION_DISTRIBUTIONS WHERE RESERVATION_ID IN (' . implode(', ', array_keys($reservationPlaceholders)) . ')'
        )->execute($reservationPlaceholders);

        $this->pdo->prepare(
            'DELETE FROM NOTIFICATIONS WHERE RESERVATION_ID IN (' . implode(', ', array_keys($reservationPlaceholders)) . ')'
        )->execute($reservationPlaceholders);

        if ($paymentIds !== []) {
            $paymentPlaceholders = $this->buildNamedPlaceholders('payment', $paymentIds);
            $this->pdo->prepare(
                'DELETE FROM RECUS WHERE PAIEMENT_ID IN (' . implode(', ', array_keys($paymentPlaceholders)) . ')'
            )->execute($paymentPlaceholders);

            $this->pdo->prepare(
                'DELETE FROM PAIEMENTS WHERE ID_PAIEMENT IN (' . implode(', ', array_keys($paymentPlaceholders)) . ')'
            )->execute($paymentPlaceholders);
        }

        $this->pdo->prepare(
            'DELETE FROM RESERVATIONS WHERE ID_RESERVATION IN (' . implode(', ', array_keys($reservationPlaceholders)) . ')'
        )->execute($reservationPlaceholders);
    }

    private function deleteSuggestionsForAnnonceIds(array $annonceIds): void
    {
        $annonceIds = array_values(array_filter(array_map('intval', $annonceIds), static fn (int $id): bool => $id > 0));
        if ($annonceIds === []) {
            return;
        }

        $placeholders = $this->buildNamedPlaceholders('annonce_suggestion', $annonceIds);
        $this->pdo->prepare(
            'DELETE FROM SUGGESTIONS_IA WHERE FOOD_ITEM_ID IN (' . implode(', ', array_keys($placeholders)) . ')'
        )->execute($placeholders);
    }

    private function deleteAddressesByIds(array $addressIds): void
    {
        $addressIds = array_values(array_filter(array_map('intval', $addressIds), static fn (int $id): bool => $id > 0));
        if ($addressIds === []) {
            return;
        }

        $placeholders = $this->buildNamedPlaceholders('address', $addressIds);
        $this->pdo->prepare(
            'DELETE FROM ADDRESSES WHERE ID IN (' . implode(', ', array_keys($placeholders)) . ')'
        )->execute($placeholders);
    }

    private function buildNamedPlaceholders(string $prefix, array $values): array
    {
        $bindings = [];
        foreach (array_values($values) as $index => $value) {
            $bindings[':' . $prefix . '_' . $index] = (int) $value;
        }

        return $bindings;
    }
}
