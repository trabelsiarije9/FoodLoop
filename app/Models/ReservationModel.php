<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class ReservationModel extends BaseModel
{
    public function findAnnonceForUpdate(int $annonceId): array|false
    {
        $stmt = $this->pdo->prepare('
            SELECT ID_ANNONCE, QUANTITE, STATUT
            FROM ANNONCES
            WHERE ID_ANNONCE = :id_annonce
            FOR UPDATE
        ');
        $stmt->bindValue(':id_annonce', $annonceId);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function create(array $data): array
    {
        $this->pdo->beginTransaction();

        $annonce = $this->findAnnonceForUpdate($data['annonce_id']);
        if ($annonce === false) {
            $this->pdo->rollBack();
            throw new \RuntimeException('Annonce introuvable.', 404);
        }

        $quantityAvailable = (int) $annonce['QUANTITE'];
        $status = (string) $annonce['STATUT'];

        if (!in_array($status, ['available', 'priority_access'], true)) {
            $this->pdo->rollBack();
            throw new \RuntimeException('Cette annonce ne peut pas etre reservee actuellement.', 409);
        }

        if ($data['quantity'] > $quantityAvailable) {
            $this->pdo->rollBack();
            throw new \LengthException((string) $quantityAvailable);
        }

        $reservationId = $this->nextId('RESERVATIONS', 'ID_RESERVATION');
        $stmt = $this->pdo->prepare('
            INSERT INTO RESERVATIONS (
                ID_RESERVATION,
                ANNONCE_ID,
                UTILISATEUR_ID,
                ADMIN_ASSOCIATION_ID,
                QUANTITE_RESERVEE,
                STATUT,
                DATE_RESERVATION,
                DATE_PICKUP,
                CREATED_AT
            ) VALUES (
                :id_reservation,
                :annonce_id,
                :utilisateur_id,
                :admin_association_id,
                :quantite_reservee,
                :statut,
                TO_TIMESTAMP(:date_reservation, \'YYYY-MM-DD HH24:MI:SS\'),
                TO_TIMESTAMP(:date_pickup, \'YYYY-MM-DD HH24:MI:SS\'),
                CURRENT_TIMESTAMP
            )
        ');
        $stmt->bindValue(':id_reservation', $reservationId, PDO::PARAM_INT);
        $stmt->bindValue(':annonce_id', $data['annonce_id'], PDO::PARAM_INT);
        $stmt->bindValue(':utilisateur_id', $data['user_id'], $data['user_id'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':admin_association_id', $data['association_admin_id'], $data['association_admin_id'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':quantite_reservee', $data['quantity'], PDO::PARAM_INT);
        $stmt->bindValue(':statut', 'pending');
        $stmt->bindValue(':date_reservation', $data['reservation_date']);
        $stmt->bindValue(':date_pickup', $data['pickup_date']);
        $stmt->execute();

        $remainingQuantity = $quantityAvailable - $data['quantity'];
        $newStatus = $remainingQuantity === 0 ? 'reserved' : $status;

        $stmtUpdate = $this->pdo->prepare('
            UPDATE ANNONCES
            SET QUANTITE = :quantite_restante,
                STATUT = :nouveau_statut,
                UPDATED_AT = CURRENT_TIMESTAMP
            WHERE ID_ANNONCE = :id_annonce
        ');
        $stmtUpdate->execute([
            'quantite_restante' => $remainingQuantity,
            'nouveau_statut' => $newStatus,
            'id_annonce' => $data['annonce_id'],
        ]);

        $this->pdo->commit();

        return [
            'reservation_id' => $reservationId,
            'quantite_restante' => $remainingQuantity,
        ];
    }
}
