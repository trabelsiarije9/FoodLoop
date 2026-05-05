<?php
declare(strict_types=1);

namespace App\Models;

final class PaymentModel extends BaseModel
{
    public function generatePickupCode(): string
    {
        return strtoupper('PK' . bin2hex(random_bytes(4)));
    }

    public function findReservationForUpdate(int $reservationId): array|false
    {
        $stmt = $this->pdo->prepare('
            SELECT
                ID_RESERVATION,
                UTILISATEUR_ID,
                ADMIN_ASSOCIATION_ID,
                STATUT,
                DATE_PICKUP
            FROM RESERVATIONS
            WHERE ID_RESERVATION = :reservation_id
            FOR UPDATE
        ');
        $stmt->bindValue(':reservation_id', $reservationId);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function paymentExists(int $reservationId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS TOTAL FROM PAIEMENTS WHERE RESERVATION_ID = :reservation_id');
        $stmt->bindValue(':reservation_id', $reservationId);
        $stmt->execute();
        $row = $stmt->fetch();

        return (int) ($row['TOTAL'] ?? 0) > 0;
    }

    public function create(array $data): array
    {
        $this->pdo->beginTransaction();

        try {
            $paymentId = $this->nextId('PAIEMENTS', 'ID_PAIEMENT');
            $stmtPayment = $this->pdo->prepare('
                INSERT INTO PAIEMENTS (
                    ID_PAIEMENT,
                    RESERVATION_ID,
                    MONTANT,
                    METHODE_PAIEMENT,
                    DATE_PAIEMENT,
                    STATUT,
                    CREATED_AT
                ) VALUES (
                    :id_paiement,
                    :reservation_id,
                    :montant,
                    :methode_paiement,
                    CURRENT_TIMESTAMP,
                    :statut,
                    CURRENT_TIMESTAMP
                )
            ');
            try {
                $stmtPayment->execute([
                    'id_paiement' => $paymentId,
                    'reservation_id' => $data['reservation_id'],
                    'montant' => $data['amount'],
                    'methode_paiement' => $data['oracle_method'],
                    'statut' => 'confirme',
                ]);
            } catch (\Throwable $exception) {
                throw new \RuntimeException('Echec insertion paiement: ' . $exception->getMessage(), 0, $exception);
            }

            $stmtReservation = $this->pdo->prepare('UPDATE RESERVATIONS SET STATUT = :statut WHERE ID_RESERVATION = :reservation_id');
            try {
                $stmtReservation->execute([
                    'statut' => 'approved',
                    'reservation_id' => $data['reservation_id'],
                ]);
            } catch (\Throwable $exception) {
                throw new \RuntimeException('Echec mise a jour reservation: ' . $exception->getMessage(), 0, $exception);
            }

            $pickupCode = null;
            if ($data['method'] === 'onsite') {
                $pickupId = $this->nextId('PICKUPS', 'ID');
                $pickupCode = $this->generatePickupCode();
                $stmtPickup = $this->pdo->prepare('
                    INSERT INTO PICKUPS (
                        ID,
                        RESERVATION_ID,
                        SCHEDULED_AT,
                        PICKED_UP_AT,
                        RECEIVER_NAME,
                        RECEIVER_PHONE,
                        PICKUP_CODE,
                        STATUS,
                        CREATED_AT
                    ) VALUES (
                        :id_pickup,
                        :reservation_id,
                        (SELECT DATE_PICKUP FROM RESERVATIONS WHERE ID_RESERVATION = :pickup_reservation_id),
                        NULL,
                        :receiver_name,
                        :receiver_phone,
                        :pickup_code,
                        :status,
                        CURRENT_TIMESTAMP
                    )
                ');
                try {
                    $stmtPickup->execute([
                        'id_pickup' => $pickupId,
                        'reservation_id' => $data['reservation_id'],
                        'pickup_reservation_id' => $data['reservation_id'],
                        'receiver_name' => $data['receiver_name'],
                        'receiver_phone' => $data['receiver_phone'],
                        'pickup_code' => $pickupCode,
                        'status' => 'scheduled',
                    ]);
                } catch (\Throwable $exception) {
                    throw new \RuntimeException('Echec creation code pickup: ' . $exception->getMessage(), 0, $exception);
                }
            }

            $this->pdo->commit();

            return [
                'payment_id' => $paymentId,
                'pickup_code' => $pickupCode,
            ];
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
