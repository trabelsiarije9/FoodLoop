<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PaymentModel;
use RuntimeException;

final class PaymentController extends Controller
{
    public function process(): never
    {
        $this->requirePost();
        $this->startSession();
        $this->ensureSessionAccountIsActive();

        if (!isset($_SESSION['foodloop_user_id'], $_SESSION['foodloop_role'])) {
            $this->json(401, ['status' => 'error', 'message' => 'Utilisateur non connecte.']);
        }

        $role = (string) $_SESSION['foodloop_role'];
        if (!in_array($role, ['acheteur', 'admin_association'], true)) {
            $this->json(403, ['status' => 'error', 'message' => 'Paiement reserve aux reservations acheteur ou association.']);
        }

        $reservationId = (int) ($_POST['reservation_id'] ?? 0);
        $amount = (float) ($_POST['montant'] ?? 0);
        $method = trim((string) ($_POST['method'] ?? ''));
        $cardNumber = preg_replace('/\s+/', '', (string) ($_POST['card_number'] ?? ''));
        $cardHolder = trim((string) ($_POST['card_holder'] ?? ''));
        $cardExpiry = trim((string) ($_POST['card_expiry'] ?? ''));
        $cardCvc = trim((string) ($_POST['card_cvc'] ?? ''));
        $receiverName = trim((string) ($_POST['receiver_name'] ?? ''));
        $receiverPhone = trim((string) ($_POST['receiver_phone'] ?? ''));

        if ($reservationId <= 0 || $amount <= 0 || $method === '') {
            $this->json(422, ['status' => 'error', 'message' => 'reservation_id, montant et method sont obligatoires.']);
        }

        $oracleMethod = match ($method) {
            'card' => 'par carte',
            'onsite' => 'espece',
            default => null,
        };

        if ($oracleMethod === null) {
            $this->json(422, ['status' => 'error', 'message' => 'Methode de paiement invalide.']);
        }

        $maskedCard = null;
        if ($method === 'card') {
            if ($cardHolder === '' || $cardExpiry === '' || $cardNumber === '' || $cardCvc === '') {
                $this->json(422, ['status' => 'error', 'message' => 'Les informations carte sont obligatoires.']);
            }

            if (!preg_match('/^\d{13,19}$/', $cardNumber)) {
                $this->json(422, ['status' => 'error', 'message' => 'Numero de carte invalide.']);
            }

            if (!preg_match('/^\d{3,4}$/', $cardCvc)) {
                $this->json(422, ['status' => 'error', 'message' => 'CVC invalide.']);
            }

            $maskedCard = '**** **** **** ' . substr($cardNumber, -4);
        }

        try {
            $model = new PaymentModel();
            $reservation = $model->findReservationForUpdate($reservationId);
            if ($reservation === false) {
                $this->json(404, ['status' => 'error', 'message' => 'Reservation introuvable.']);
            }

            $sessionUserId = (int) $_SESSION['foodloop_user_id'];
            if (
                ($role === 'acheteur' && (int) ($reservation['UTILISATEUR_ID'] ?? 0) !== $sessionUserId) ||
                ($role === 'admin_association' && (int) ($reservation['ADMIN_ASSOCIATION_ID'] ?? 0) !== $sessionUserId)
            ) {
                $this->json(403, ['status' => 'error', 'message' => 'Cette reservation ne vous appartient pas.']);
            }

            if ($model->paymentExists($reservationId)) {
                $this->json(409, ['status' => 'error', 'message' => 'Cette reservation a deja ete payee.']);
            }

            if ($receiverName === '') {
                $receiverName = (string) ($_SESSION['foodloop_name'] ?? 'Client FoodLoop');
            }

            if ($receiverPhone === '') {
                $receiverPhone = '00000000';
            }

            $result = $model->create([
                'reservation_id' => $reservationId,
                'amount' => $amount,
                'oracle_method' => $oracleMethod,
                'payment_date' => date('Y-m-d H:i:s'),
                'method' => $method,
                'scheduled_at' => (string) $reservation['DATE_PICKUP'],
                'receiver_name' => $receiverName,
                'receiver_phone' => $receiverPhone,
            ]);

            $this->json(201, [
                'status' => 'success',
                'message' => 'Paiement enregistre avec succes.',
                'payment_id' => $result['payment_id'],
                'role' => $role,
                'payment_method' => $method,
                'masked_card' => $maskedCard,
                'pickup_code' => $result['pickup_code'],
            ]);
        } catch (RuntimeException $exception) {
            $this->json(500, ['status' => 'error', 'message' => $exception->getMessage()]);
        } catch (\Throwable) {
            $this->json(500, ['status' => 'error', 'message' => 'Erreur lors du paiement.']);
        }
    }
}
