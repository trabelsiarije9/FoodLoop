<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\UserModel;

final class AuthController extends Controller
{
    public function login(): string
    {
        if (Auth::check()) {
            redirect('dashboard');
        }

        return $this->render('auth/login', [
            'title' => 'Connexion | FoodLoop',
            'description' => 'Connectez-vous a votre espace FoodLoop.',
            'currentPage' => 'login',
            'navigation' => HomeController::publicNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => null,
        ]);
    }

    public function loginSubmit(): string
    {
        $this->requireCsrf();

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        store_old_input(['email' => $email]);

        if ($email === '' || $password === '') {
            flash('error', 'Email et mot de passe obligatoires.');
            redirect('login');
        }

        if (!Auth::attempt($email, $password)) {
            flash('error', 'Identifiants invalides ou compte inactif.');
            redirect('login');
        }

        clear_old_input();
        flash('success', 'Connexion reussie.');
        redirect('dashboard');
    }

    public function register(): string
    {
        if (Auth::check()) {
            redirect('dashboard');
        }

        return $this->render('auth/register', [
            'title' => 'Creer un compte | FoodLoop',
            'description' => 'Inscription user simple, association ou business owner sur FoodLoop.',
            'currentPage' => 'register',
            'navigation' => HomeController::publicNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => null,
        ]);
    }

    public function registerSubmit(): string
    {
        $this->requireCsrf();

        $data = [
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'last_name' => trim((string) ($_POST['last_name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
            'role_code' => (string) ($_POST['role_code'] ?? 'regular_user'),
            'organization_name' => trim((string) ($_POST['organization_name'] ?? '')),
            'organization_description' => trim((string) ($_POST['organization_description'] ?? '')),
            'business_license' => trim((string) ($_POST['business_license'] ?? '')),
            'association_code' => trim((string) ($_POST['association_code'] ?? '')),
            'address_line' => trim((string) ($_POST['address_line'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'governorate' => trim((string) ($_POST['governorate'] ?? '')),
        ];

        if ($data['role_code'] === 'association_admin') {
            $data['organization_name'] = $data['organization_name'] !== '' ? $data['organization_name'] : trim((string) ($_POST['association_organization_name'] ?? ''));
            $data['organization_description'] = $data['organization_description'] !== '' ? $data['organization_description'] : trim((string) ($_POST['association_organization_description'] ?? ''));
            $data['address_line'] = $data['address_line'] !== '' ? $data['address_line'] : trim((string) ($_POST['association_address_line'] ?? ''));
            $data['city'] = $data['city'] !== '' ? $data['city'] : trim((string) ($_POST['association_city'] ?? ''));
            $data['governorate'] = $data['governorate'] !== '' ? $data['governorate'] : trim((string) ($_POST['association_governorate'] ?? ''));
        }

        if ($data['role_code'] === 'business_owner') {
            $data['organization_name'] = $data['organization_name'] !== '' ? $data['organization_name'] : trim((string) ($_POST['business_organization_name'] ?? ''));
            $data['organization_description'] = $data['organization_description'] !== '' ? $data['organization_description'] : trim((string) ($_POST['business_organization_description'] ?? ''));
            $data['address_line'] = $data['address_line'] !== '' ? $data['address_line'] : trim((string) ($_POST['business_address_line'] ?? ''));
            $data['city'] = $data['city'] !== '' ? $data['city'] : trim((string) ($_POST['business_city'] ?? ''));
            $data['governorate'] = $data['governorate'] !== '' ? $data['governorate'] : trim((string) ($_POST['business_governorate'] ?? ''));
        }

        store_old_input($data);

        if ($data['first_name'] === '' || $data['last_name'] === '' || $data['email'] === '' || $data['password'] === '') {
            flash('error', 'Merci de remplir les champs obligatoires.');
            redirect('register');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Adresse email invalide.');
            redirect('register');
        }

        if ($data['password'] !== $data['password_confirmation']) {
            flash('error', 'La confirmation du mot de passe ne correspond pas.');
            redirect('register');
        }

        if (strlen($data['password']) < 6) {
            flash('error', 'Le mot de passe doit contenir au moins 6 caracteres.');
            redirect('register');
        }

        if (!in_array($data['role_code'], ['regular_user', 'association_admin', 'business_owner'], true)) {
            flash('error', 'Role d inscription non autorise.');
            redirect('register');
        }

        if (in_array($data['role_code'], ['business_owner', 'association_admin'], true) && $data['organization_name'] === '') {
            flash('error', 'Le nom de l organisation est obligatoire pour ce role.');
            redirect('register');
        }

        if ($data['role_code'] === 'association_admin' && $data['association_code'] === '') {
            flash('error', 'Le code association est obligatoire pour une association.');
            redirect('register');
        }

        if ($data['role_code'] === 'business_owner' && $data['business_license'] === '') {
            flash('error', 'La licence business est obligatoire pour un business owner.');
            redirect('register');
        }

        $userModel = new UserModel();

        if ($userModel->findByEmail($data['email']) !== null) {
            flash('error', 'Cet email existe deja.');
            redirect('register');
        }

        try {
            $userModel->create($data);
        } catch (\Throwable $exception) {
            flash('error', 'Inscription impossible pour le moment: ' . $exception->getMessage());
            redirect('register');
        }

        clear_old_input();
        flash('success', 'Compte cree avec succes. Connectez-vous maintenant.');
        redirect('login');
    }

    public function logout(): string
    {
        Auth::logout();
        flash('success', 'Vous etes deconnecte.');
        redirect('home');
    }
}
