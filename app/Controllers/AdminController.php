<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\DashboardModel;
use App\Models\ContactMessageModel;
use App\Models\FoodItemModel;
use App\Models\ReservationModel;
use App\Models\UserModel;

final class AdminController extends Controller
{
    public function dashboard(): string
    {
        Auth::requireRole('system_admin');

        return $this->render('admin/dashboard', [
            'title' => 'Dashboard Admin | FoodLoop',
            'description' => 'Supervision complete de la plateforme FoodLoop.',
            'currentPage' => 'admin',
            'navigation' => HomeController::privateNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => Auth::user(),
            'stats' => (new DashboardModel())->adminStats(),
            'users' => array_slice((new UserModel())->all(), 0, 5),
            'items' => array_slice((new FoodItemModel())->allForAdmin(), 0, 5),
            'reservations' => array_slice((new ReservationModel())->allForAdmin(), 0, 5),
            'contacts' => array_slice((new ContactMessageModel())->all(), 0, 5),
        ]);
    }

    public function users(): string
    {
        Auth::requireRole('system_admin');

        $editUser = null;
        $editId = (int) ($_GET['edit'] ?? 0);

        if ($editId > 0) {
            $editUser = (new UserModel())->findById($editId);
        }

        return $this->render('admin/users', [
            'title' => 'Gestion Utilisateurs | FoodLoop',
            'description' => 'CRUD des comptes utilisateurs FoodLoop.',
            'currentPage' => 'admin-users',
            'navigation' => HomeController::privateNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => Auth::user(),
            'users' => (new UserModel())->all(),
            'roles' => (new UserModel())->roleOptions(),
            'editUser' => $editUser,
        ]);
    }

    public function saveUser(): string
    {
        Auth::requireRole('system_admin');
        $this->requireCsrf();

        $data = [
            'role_code' => (string) ($_POST['role_code'] ?? 'regular_user'),
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'last_name' => trim((string) ($_POST['last_name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        if ($data['first_name'] === '' || $data['last_name'] === '' || $data['email'] === '') {
            flash('error', 'Nom, prenom et email sont obligatoires.');
            redirect('admin/users');
        }

        $userModel = new UserModel();
        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $userModel->update($id, $data);
            flash('success', 'Utilisateur mis a jour.');
        } else {
            if ($data['password'] === '') {
                flash('error', 'Le mot de passe est obligatoire pour un nouveau compte.');
                redirect('admin/users');
            }

            $userModel->create($data);
            flash('success', 'Utilisateur cree.');
        }

        redirect('admin/users');
    }

    public function deleteUser(): string
    {
        Auth::requireRole('system_admin');
        $this->requireCsrf();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0 && $id !== Auth::id()) {
            (new UserModel())->delete($id);
            flash('success', 'Utilisateur supprime.');
        }

        redirect('admin/users');
    }

    public function items(): string
    {
        Auth::requireRole('system_admin');

        return $this->render('admin/items', [
            'title' => 'Gestion Lots | FoodLoop',
            'description' => 'Vue globale des lots alimentaires.',
            'currentPage' => 'admin-items',
            'navigation' => HomeController::privateNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => Auth::user(),
            'items' => (new FoodItemModel())->allForAdmin(),
        ]);
    }

    public function saveFoodItem(): string
    {
        Auth::requireRole('system_admin');
        $this->requireCsrf();
        flash('error', 'La creation admin de lots passe par les comptes business owner pour ce MVP.');
        redirect('admin/items');
    }

    public function deleteFoodItem(): string
    {
        Auth::requireRole('system_admin');
        $this->requireCsrf();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            (new FoodItemModel())->delete($id);
            flash('success', 'Lot supprime.');
        }

        redirect('admin/items');
    }

    public function reservations(): string
    {
        Auth::requireRole('system_admin');

        return $this->render('admin/reservations', [
            'title' => 'Reservations | FoodLoop',
            'description' => 'Suivi des reservations de la plateforme.',
            'currentPage' => 'admin-reservations',
            'navigation' => HomeController::privateNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => Auth::user(),
            'reservations' => (new ReservationModel())->allForAdmin(),
        ]);
    }

    public function updateReservationStatus(): string
    {
        Auth::requireRole('system_admin');
        $this->requireCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $status = (string) ($_POST['status'] ?? 'pending');

        if ($id > 0) {
            (new ReservationModel())->updateStatus($id, $status);
            flash('success', 'Statut de reservation mis a jour.');
        }

        redirect('admin/reservations');
    }

    public function contacts(): string
    {
        Auth::requireRole('system_admin');

        return $this->render('admin/contacts', [
            'title' => 'Briefs Contact | FoodLoop',
            'description' => 'Messages envoyes depuis la page de contact.',
            'currentPage' => 'admin-contacts',
            'navigation' => HomeController::privateNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => Auth::user(),
            'contacts' => (new ContactMessageModel())->all(),
        ]);
    }
}
