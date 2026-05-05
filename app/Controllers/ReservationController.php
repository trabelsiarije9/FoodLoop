<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ReservationModel;
use LengthException;
use RuntimeException;

final class ReservationController extends Controller
{
    public function reserve(): never
    {
        $this->requirePost();
        $this->startSession();
        $this->ensureSessionAccountIsActive();

        if (!isset($_SESSION['foodloop_user_id'], $_SESSION['foodloop_role'])) {
            $this->json(401, ['status' => 'error', 'message' => 'Utilisateur non connecte.']);
        }

        $role = (string) $_SESSION['foodloop_role'];
        if (!in_array($role, ['acheteur', 'admin_association'], true)) {
            $this->json(403, ['status' => 'error', 'message' => 'Seuls acheteur et admin_association peuvent reserver.']);
        }

        $annonceId = (int) ($_POST['annonce_id'] ?? 0);
        $quantity = (int) ($_POST['quantite'] ?? 0);
        $pickupDate = trim((string) ($_POST['pickup_date'] ?? ''));

        if ($annonceId <= 0 || $quantity <= 0) {
            $this->json(422, ['status' => 'error', 'message' => 'Annonce et quantite sont obligatoires.']);
        }

        if ($pickupDate === '') {
            $pickupDate = date('Y-m-d H:i:s', strtotime('+2 hours'));
        } else {
            $pickupTimestamp = strtotime($pickupDate);
            if ($pickupTimestamp === false) {
                $this->json(422, ['status' => 'error', 'message' => 'Date de pickup invalide.']);
            }
            $pickupDate = date('Y-m-d H:i:s', $pickupTimestamp);
        }

        try {
            $result = (new ReservationModel())->create([
                'annonce_id' => $annonceId,
                'user_id' => $role === 'acheteur' ? (int) $_SESSION['foodloop_user_id'] : null,
                'association_admin_id' => $role === 'admin_association' ? (int) $_SESSION['foodloop_user_id'] : null,
                'quantity' => $quantity,
                'reservation_date' => date('Y-m-d H:i:s'),
                'pickup_date' => $pickupDate,
            ]);

            $this->json(201, [
                'status' => 'success',
                'message' => 'Reservation enregistree avec succes.',
                'annonce_id' => $annonceId,
                'reservation_id' => $result['reservation_id'],
                'quantite_restante' => $result['quantite_restante'],
            ]);
        } catch (LengthException $exception) {
            $this->json(409, [
                'status' => 'error',
                'message' => 'Quantite demandee superieure au stock disponible.',
                'quantite_disponible' => (int) $exception->getMessage(),
            ]);
        } catch (RuntimeException $exception) {
            $code = $exception->getCode();
            $this->json(in_array($code, [404, 409], true) ? $code : 500, [
                'status' => 'error',
                'message' => $exception->getMessage(),
            ]);
        } catch (\Throwable) {
            $this->json(500, ['status' => 'error', 'message' => 'Erreur lors de la reservation.']);
        }
    }
}
