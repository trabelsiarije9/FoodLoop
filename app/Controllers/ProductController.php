<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\FoodItemModel;
use RuntimeException;

final class ProductController extends Controller
{
    public function feed(): never
    {
        $this->startSession();
        $this->ensureSessionAccountIsActive();

        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
            $this->json(405, ['status' => 'error', 'message' => 'Methode non autorisee.']);
        }

        if (!isset($_SESSION['foodloop_user_id'], $_SESSION['foodloop_role'])) {
            $this->json(401, ['status' => 'error', 'message' => 'Utilisateur non connecte.']);
        }

        $role = (string) $_SESSION['foodloop_role'];
        if (!in_array($role, ['acheteur', 'admin_association'], true)) {
            $this->json(403, ['status' => 'error', 'message' => 'Acces reserve au feed acheteur/association.']);
        }

        try {
            $items = (new FoodItemModel())->listFeedAnnouncements($role);
            $this->json(200, [
                'status' => 'success',
                'items' => array_map(static function (array $item): array {
                    $annonceId = (int) ($item['ID_ANNONCE'] ?? 0);
                    $category = trim((string) ($item['CATEGORY_NAME'] ?? ''));
                    $location = trim((string) ($item['LOCALISATION'] ?? ''));
                    $zone = trim((string) ($item['ZONE_NAME'] ?? '')) ?: $location;
                    $city = trim((string) ($item['ZONE_CITY'] ?? '')) ?: $location;
                    $pickupStart = trim((string) ($item['PICKUP_START_TIME'] ?? ''));
                    $pickupEnd = trim((string) ($item['PICKUP_END_TIME'] ?? ''));

                    return [
                        'id' => $annonceId,
                        'title' => (string) ($item['TITRE'] ?? ''),
                        'description' => (string) ($item['DESCRIPTION'] ?? ''),
                        'type' => (string) ($item['TYPE_ALIMENT'] ?? ''),
                        'category' => $category !== '' ? $category : (string) ($item['TYPE_ALIMENT'] ?? 'Produit'),
                        'location' => $location,
                        'zone' => $zone,
                        'city' => $city,
                        'price' => (float) ($item['PRIX'] ?? 0),
                        'stock' => (int) ($item['QUANTITE'] ?? 0),
                        'status_code' => (string) ($item['STATUT'] ?? ''),
                        'pickup_time' => trim($pickupStart . ' - ' . $pickupEnd, ' -'),
                        'unit' => (string) ($item['UNITE'] ?? 'unite'),
                    ];
                }, $items),
            ]);
        } catch (RuntimeException $exception) {
            $this->json(500, ['status' => 'error', 'message' => $exception->getMessage()]);
        } catch (\Throwable) {
            $this->json(500, ['status' => 'error', 'message' => 'Erreur lors du chargement du feed.']);
        }
    }

    public function availability(): never
    {
        $this->startSession();
        $this->ensureSessionAccountIsActive();

        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
            $this->json(405, ['status' => 'error', 'message' => 'Methode non autorisee.']);
        }

        if (!isset($_SESSION['foodloop_user_id'], $_SESSION['foodloop_role'])) {
            $this->json(401, ['status' => 'error', 'message' => 'Utilisateur non connecte.']);
        }

        $idsParam = trim((string) ($_GET['ids'] ?? ''));
        $ids = array_values(array_filter(array_map('intval', preg_split('/\s*,\s*/', $idsParam) ?: []), static fn (int $id) => $id > 0));

        if ($ids === []) {
            $this->json(422, ['status' => 'error', 'message' => 'La liste ids est obligatoire.']);
        }

        try {
            $items = (new FoodItemModel())->findAnnonceSnapshots($ids);
            $this->json(200, [
                'status' => 'success',
                'items' => array_map(static fn (array $item) => [
                    'annonce_id' => (int) ($item['ID_ANNONCE'] ?? 0),
                    'title' => (string) ($item['TITRE'] ?? ''),
                    'description' => (string) ($item['DESCRIPTION'] ?? ''),
                    'location' => (string) ($item['LOCALISATION'] ?? ''),
                    'price' => (float) ($item['PRIX'] ?? 0),
                    'quantity' => (int) ($item['QUANTITE'] ?? 0),
                    'status_code' => (string) ($item['STATUT'] ?? ''),
                ], $items),
            ]);
        } catch (RuntimeException $exception) {
            $this->json(500, ['status' => 'error', 'message' => $exception->getMessage()]);
        } catch (\Throwable) {
            $this->json(500, ['status' => 'error', 'message' => 'Erreur lors du chargement des disponibilites.']);
        }
    }

    public function ownerFeed(): never
    {
        $this->startSession();
        $this->ensureSessionAccountIsActive();

        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
            $this->json(405, ['status' => 'error', 'message' => 'Methode non autorisee.']);
        }

        if (!isset($_SESSION['foodloop_user_id'], $_SESSION['foodloop_role'])) {
            $this->json(401, ['status' => 'error', 'message' => 'Utilisateur non connecte.']);
        }

        if ((string) $_SESSION['foodloop_role'] !== 'commerce') {
            $this->json(403, ['status' => 'error', 'message' => 'Acces reserve aux commerces.']);
        }

        try {
            $items = (new FoodItemModel())->listOwnerAnnouncements((int) $_SESSION['foodloop_user_id']);
            $this->json(200, [
                'status' => 'success',
                'items' => array_map(static function (array $item): array {
                    return [
                        'id' => (int) ($item['ID_ANNONCE'] ?? 0),
                        'title' => (string) ($item['TITRE'] ?? ''),
                        'description' => (string) ($item['DESCRIPTION'] ?? ''),
                        'food_type' => (string) ($item['TYPE_ALIMENT'] ?? ''),
                        'category' => (string) ($item['CATEGORY_NAME'] ?? ''),
                        'quantity' => (int) ($item['QUANTITE'] ?? 0),
                        'unit' => (string) ($item['UNITE'] ?? 'unite'),
                        'location' => (string) ($item['LOCALISATION'] ?? ''),
                        'status_code' => (string) ($item['STATUT'] ?? ''),
                        'price' => (float) ($item['PRIX'] ?? 0),
                        'pickup_time' => trim((string) ($item['PICKUP_START_TIME'] ?? '') . ' - ' . (string) ($item['PICKUP_END_TIME'] ?? ''), ' -'),
                    ];
                }, $items),
            ]);
        } catch (RuntimeException $exception) {
            $this->json(500, ['status' => 'error', 'message' => $exception->getMessage()]);
        } catch (\Throwable) {
            $this->json(500, ['status' => 'error', 'message' => 'Erreur lors du chargement des annonces commerce.']);
        }
    }

    public function create(): never
    {
        $this->requirePost();
        $this->startSession();
        $this->ensureSessionAccountIsActive();

        if (!isset($_SESSION['foodloop_user_id'], $_SESSION['foodloop_role'])) {
            $this->json(401, ['status' => 'error', 'message' => 'Utilisateur non connecte.']);
        }

        if ($_SESSION['foodloop_role'] !== 'commerce') {
            $this->json(403, ['status' => 'error', 'message' => 'Acces reserve aux commerces.']);
        }

        $title = trim((string) ($_POST['titre'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $type = trim((string) ($_POST['type'] ?? ''));
        $quantity = (int) ($_POST['quantite'] ?? 0);
        $unit = trim((string) ($_POST['unite'] ?? ''));
        $location = trim((string) ($_POST['localisation'] ?? ''));
        $price = (float) ($_POST['prix'] ?? 0);
        $pickupTime = trim((string) ($_POST['pickup_time'] ?? ''));
        $categoryId = (int) ($_POST['categorie_id'] ?? 0);
        $zoneId = (int) ($_POST['zone_id'] ?? 0);

        if ($title === '' || $description === '' || $type === '' || $unit === '' || $location === '' || $pickupTime === '') {
            $this->json(422, ['status' => 'error', 'message' => 'Tous les champs du produit sont obligatoires.']);
        }

        if ($quantity <= 0) {
            $this->json(422, ['status' => 'error', 'message' => 'La quantite doit etre strictement positive.']);
        }

        if ($price < 0) {
            $this->json(422, ['status' => 'error', 'message' => 'Le prix ne peut pas etre negatif.']);
        }

        $pickupTimestamp = strtotime($pickupTime);
        if ($pickupTimestamp === false) {
            $this->json(422, ['status' => 'error', 'message' => 'Date de pickup invalide.']);
        }

        try {
            $model = new FoodItemModel();
            $ownerId = (int) $_SESSION['foodloop_user_id'];

            if (!$model->commerceExists($ownerId)) {
                $this->json(403, ['status' => 'error', 'message' => 'Profil commerce introuvable dans la base Oracle.']);
            }

            $categoryId = $model->resolveCategoryIdFromType($type, $categoryId);
            if ($categoryId <= 0) {
                $this->json(500, ['status' => 'error', 'message' => 'Aucune categorie compatible disponible.']);
            }

            $zoneId = $model->resolveZoneIdFromLocation($location, $zoneId);
            if ($zoneId <= 0) {
                $this->json(500, ['status' => 'error', 'message' => 'Aucune zone geographique disponible.']);
            }

            $annonceId = $model->create([
                'title' => $title,
                'description' => $description,
                'type' => $type,
                'price' => $price,
                'quantity' => $quantity,
                'unit' => $unit,
                'location' => $location,
                'status' => 'available',
                'public_visibility_at' => date('Y-m-d H:i:s'),
                'pickup_start' => date('Y-m-d H:i:s', $pickupTimestamp),
                'pickup_end' => date('Y-m-d H:i:s', strtotime('+1 hour', $pickupTimestamp)),
                'expiration_date' => date('Y-m-d H:i:s', strtotime('+2 hours', $pickupTimestamp)),
                'category_id' => $categoryId,
                'zone_id' => $zoneId,
                'owner_id' => $ownerId,
            ]);

            $this->json(201, [
                'status' => 'success',
                'message' => 'Produit cree avec succes.',
                'product_id' => $annonceId,
                'price' => $price,
            ]);
        } catch (RuntimeException $exception) {
            $this->json(500, ['status' => 'error', 'message' => $exception->getMessage()]);
        } catch (\Throwable) {
            $this->json(500, ['status' => 'error', 'message' => 'Erreur lors de la creation du produit.']);
        }
    }
}
