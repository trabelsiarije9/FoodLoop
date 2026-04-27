<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\DashboardModel;
use App\Models\FoodItemModel;
use App\Models\OrganizationModel;
use App\Models\ReservationModel;

final class AssociationController extends Controller
{
    public function dashboard(): string
    {
        Auth::requireRole('association_admin');
        $organization = $this->organization();

        return $this->render('association/dashboard', [
            'title' => 'Dashboard Association | FoodLoop',
            'description' => 'Espace association pour suivre les lots et les reservations.',
            'currentPage' => 'association',
            'navigation' => HomeController::privateNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => Auth::user(),
            'organization' => $organization,
            'stats' => (new DashboardModel())->associationStats((int) Auth::id()),
        ]);
    }

    public function catalog(): string
    {
        Auth::requireRole('association_admin');
        $organization = $this->organization();

        return $this->render('association/catalog', [
            'title' => 'Catalogue Association | FoodLoop',
            'description' => 'Lots visibles pour les associations.',
            'currentPage' => 'association/catalog',
            'navigation' => HomeController::privateNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => Auth::user(),
            'organization' => $organization,
            'items' => (new FoodItemModel())->latestAvailable(50),
        ]);
    }

    public function reservations(): string
    {
        Auth::requireRole('association_admin');

        return $this->render('association/reservations', [
            'title' => 'Reservations Association | FoodLoop',
            'description' => 'Historique des demandes de votre association.',
            'currentPage' => 'association/reservations',
            'navigation' => HomeController::privateNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => Auth::user(),
            'reservations' => (new ReservationModel())->allForAssociation((int) Auth::id()),
        ]);
    }

    public function reserve(): string
    {
        Auth::requireRole('association_admin');
        $this->requireCsrf();

        $itemId = (int) ($_POST['food_item_id'] ?? 0);
        $quantity = (float) ($_POST['reserved_quantity'] ?? 1);
        $item = (new FoodItemModel())->find($itemId);
        $organization = $this->organization();

        if ($item === null) {
            flash('error', 'Lot introuvable.');
            redirect('association/catalog');
        }

        (new ReservationModel())->create([
            'food_item_id' => $itemId,
            'user_id' => null,
            'organization_id' => (int) $organization['id'],
            'reserved_quantity' => max(1, $quantity),
            'status' => 'pending',
            'notes' => trim((string) ($_POST['notes'] ?? '')),
        ]);

        flash('success', 'Reservation association envoyee.');
        redirect('association/reservations');
    }

    private function organization(): array
    {
        $organization = (new OrganizationModel())->findByOwner((int) Auth::id());

        if ($organization === null) {
            flash('error', 'Aucune organisation n est rattachee a ce compte association.');
            redirect('dashboard');
        }

        return $organization;
    }
}
