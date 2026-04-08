<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\DashboardModel;
use App\Models\FoodItemModel;
use App\Models\OrganizationModel;
use App\Models\ReservationModel;

final class BusinessController extends Controller
{
    public function dashboard(): string
    {
        Auth::requireRole('business_owner');
        $organization = $this->organization();

        return $this->render('business/dashboard', [
            'title' => 'Dashboard Business | FoodLoop',
            'description' => 'Espace business owner pour gerer les surplus.',
            'currentPage' => 'business',
            'navigation' => HomeController::privateNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => Auth::user(),
            'organization' => $organization,
            'stats' => (new DashboardModel())->businessStats((int) $organization['id']),
            'items' => array_slice((new FoodItemModel())->allForOrganization((int) $organization['id']), 0, 5),
            'reservations' => array_slice((new ReservationModel())->allForOrganization((int) $organization['id']), 0, 5),
        ]);
    }

    public function items(): string
    {
        Auth::requireRole('business_owner');
        $organization = $this->organization();
        $editItem = null;
        $editId = (int) ($_GET['edit'] ?? 0);

        if ($editId > 0) {
            $editItem = (new FoodItemModel())->find($editId);
        }

        return $this->render('business/items', [
            'title' => 'Mes Lots | FoodLoop',
            'description' => 'CRUD des lots business owner.',
            'currentPage' => 'business-items',
            'navigation' => HomeController::privateNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => Auth::user(),
            'organization' => $organization,
            'items' => (new FoodItemModel())->allForOrganization((int) $organization['id']),
            'categories' => (new FoodItemModel())->categories(),
            'editItem' => $editItem,
        ]);
    }

    public function saveFoodItem(): string
    {
        Auth::requireRole('business_owner');
        $this->requireCsrf();
        $organization = $this->organization();

        $data = [
            'organization_id' => (int) $organization['id'],
            'created_by' => (int) Auth::id(),
            'pickup_address_id' => (int) ($organization['address_id'] ?? 0),
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'quantity' => (float) ($_POST['quantity'] ?? 0),
            'unit' => trim((string) ($_POST['unit'] ?? 'portion')),
            'expiration_date' => $this->normalizeDateTime($_POST['expiration_date'] ?? null),
            'pickup_start' => $this->normalizeDateTime($_POST['pickup_start'] ?? null),
            'pickup_end' => $this->normalizeDateTime($_POST['pickup_end'] ?? null),
            'status' => (string) ($_POST['status'] ?? 'draft'),
            'priority_until' => $this->normalizeDateTime($_POST['priority_until'] ?? null),
        ];

        if ($data['title'] === '' || $data['category_id'] <= 0 || $data['pickup_address_id'] <= 0) {
            flash('error', 'Titre, categorie et adresse sont obligatoires.');
            redirect('business/items');
        }

        $model = new FoodItemModel();
        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $model->update($id, $data);
            flash('success', 'Lot mis a jour.');
        } else {
            $model->create($data);
            flash('success', 'Lot cree avec succes.');
        }

        redirect('business/items');
    }

    public function deleteFoodItem(): string
    {
        Auth::requireRole('business_owner');
        $this->requireCsrf();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            (new FoodItemModel())->delete($id);
            flash('success', 'Lot supprime.');
        }

        redirect('business/items');
    }

    public function reservations(): string
    {
        Auth::requireRole('business_owner');
        $organization = $this->organization();

        return $this->render('business/reservations', [
            'title' => 'Reservations Recues | FoodLoop',
            'description' => 'Suivi des demandes sur les lots de votre entreprise.',
            'currentPage' => 'business-reservations',
            'navigation' => HomeController::privateNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => Auth::user(),
            'organization' => $organization,
            'reservations' => (new ReservationModel())->allForOrganization((int) $organization['id']),
        ]);
    }

    public function updateReservationStatus(): string
    {
        Auth::requireRole('business_owner');
        $this->requireCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $status = (string) ($_POST['status'] ?? 'pending');

        if ($id > 0) {
            (new ReservationModel())->updateStatus($id, $status);
            flash('success', 'Reservation mise a jour.');
        }

        redirect('business/reservations');
    }

    private function organization(): array
    {
        $organization = (new OrganizationModel())->findByOwner((int) Auth::id());

        if ($organization === null) {
            flash('error', 'Aucune organisation n est rattachee a ce compte business.');
            redirect('dashboard');
        }

        return $organization;
    }

    private function normalizeDateTime(mixed $value): string
    {
        $string = trim((string) $value);
        return $string === '' ? '' : str_replace('T', ' ', $string);
    }
}
