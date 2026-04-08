<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\DashboardModel;
use App\Models\FoodItemModel;
use App\Models\ReservationModel;

final class UserController extends Controller
{
    public function catalog(): string
    {
        Auth::requireRole('regular_user', 'system_admin');

        return $this->render('user/catalog', [
            'title' => 'Catalogue | FoodLoop',
            'description' => 'Lots disponibles pour reservation.',
            'currentPage' => 'catalog',
            'navigation' => HomeController::privateNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => Auth::user(),
            'items' => (new FoodItemModel())->latestAvailable(50),
            'stats' => (new DashboardModel())->userStats((int) Auth::id()),
        ]);
    }

    public function reservations(): string
    {
        Auth::requireRole('regular_user', 'system_admin');

        return $this->render('user/reservations', [
            'title' => 'Mes Reservations | FoodLoop',
            'description' => 'Suivi des demandes FoodLoop.',
            'currentPage' => 'reservations',
            'navigation' => HomeController::privateNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => Auth::user(),
            'stats' => (new DashboardModel())->userStats((int) Auth::id()),
            'reservations' => (new ReservationModel())->allForUser((int) Auth::id()),
        ]);
    }

    public function reserve(): string
    {
        Auth::requireRole('regular_user', 'system_admin');
        $this->requireCsrf();

        $itemId = (int) ($_POST['food_item_id'] ?? 0);
        $quantity = (float) ($_POST['reserved_quantity'] ?? 1);
        $item = (new FoodItemModel())->find($itemId);

        if ($item === null) {
            flash('error', 'Lot introuvable.');
            redirect('catalog');
        }

        (new ReservationModel())->create([
            'food_item_id' => $itemId,
            'user_id' => (int) Auth::id(),
            'organization_id' => $item['organization_id'] ?? null,
            'reserved_quantity' => max(1, $quantity),
            'status' => 'pending',
            'notes' => trim((string) ($_POST['notes'] ?? '')),
        ]);

        flash('success', 'Reservation envoyee.');
        redirect('reservations');
    }
}
