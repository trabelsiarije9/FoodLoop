<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SuperAdminModel;
use App\Models\UserModel;
use RuntimeException;

final class AuthController extends Controller
{
    public function sessionStatus(): never
    {
        $this->startSession();

        if (isset($_SESSION['foodloop_superadmin']) && $_SESSION['foodloop_superadmin'] === true) {
            $this->json(200, [
                'status' => 'success',
                'authenticated' => true,
                'user' => [
                    'id' => 1,
                    'role' => 'superadmin',
                    'email' => 'superadmin@foodloop.local',
                    'name' => (string) ($_SESSION['foodloop_superadmin_name'] ?? 'Superadmin FoodLoop'),
                ],
            ]);
        }

        if (!isset($_SESSION['foodloop_user_id'], $_SESSION['foodloop_role'])) {
            $this->json(401, [
                'status' => 'error',
                'message' => 'Utilisateur non connecte.',
                'authenticated' => false,
            ]);
        }

        $userId = (int) $_SESSION['foodloop_user_id'];
        if ((new SuperAdminModel())->isSuspended($userId)) {
            $_SESSION = [];
            session_destroy();
            $this->json(403, [
                'status' => 'error',
                'message' => 'Ce compte est suspendu. Contactez le superadmin.',
                'authenticated' => false,
            ]);
        }

        $this->json(200, [
            'status' => 'success',
            'authenticated' => true,
            'user' => [
                'id' => (int) $_SESSION['foodloop_user_id'],
                'role' => (string) $_SESSION['foodloop_role'],
                'email' => (string) ($_SESSION['foodloop_email'] ?? ''),
                'name' => (string) ($_SESSION['foodloop_name'] ?? ''),
            ],
        ]);
    }

    public function loginSubmit(): never
    {
        $this->requirePost();
        $this->startSession();

        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->json(422, ['status' => 'error', 'message' => 'Email et mot de passe obligatoires.']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(422, ['status' => 'error', 'message' => 'Adresse email invalide.']);
        }

        try {
            $user = (new UserModel())->findByEmail($email);
            if ($user === false || !password_verify($password, (string) $user['MOT_DE_PASSE'])) {
                $this->json(401, ['status' => 'error', 'message' => 'Identifiants invalides.']);
            }

            $frontendRole = match ((string) $user['ROLE_CODE']) {
                'citizen' => 'acheteur',
                'business_owner' => 'commerce',
                'association_admin' => 'admin_association',
                default => null,
            };

            if ($frontendRole === null) {
                $this->json(403, ['status' => 'error', 'message' => 'Role non autorise pour cette interface.']);
            }

            if ((new SuperAdminModel())->isSuspended((int) $user['ID_UTIL'])) {
                $this->json(403, ['status' => 'error', 'message' => 'Ce compte est suspendu. Contactez le superadmin.']);
            }

            $_SESSION['foodloop_user_id'] = (int) $user['ID_UTIL'];
            $_SESSION['foodloop_role'] = $frontendRole;
            $_SESSION['foodloop_email'] = (string) $user['EMAIL'];
            $_SESSION['foodloop_name'] = trim((string) $user['PRENOM'] . ' ' . (string) $user['NOM']);

            $this->json(200, [
                'status' => 'success',
                'message' => 'Connexion reussie.',
                'role' => $frontendRole,
                'user_id' => (int) $user['ID_UTIL'],
                'name' => $_SESSION['foodloop_name'],
            ]);
        } catch (RuntimeException $exception) {
            $this->json(500, ['status' => 'error', 'message' => $exception->getMessage()]);
        } catch (\Throwable) {
            $this->json(500, ['status' => 'error', 'message' => 'Erreur lors de la connexion.']);
        }
    }

    public function registerSubmit(): never
    {
        $this->requirePost();
        $this->startSession();

        $roleMap = [
            'acheteur' => 'citizen',
            'commerce' => 'business_owner',
            'admin_association' => 'association_admin',
        ];

        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $frontendRole = trim((string) ($_POST['role'] ?? ''));
        $firstName = trim((string) ($_POST['prenom'] ?? ''));
        $lastName = trim((string) ($_POST['nom'] ?? ''));
        $phone = trim((string) ($_POST['telephone'] ?? ''));
        $address = trim((string) ($_POST['adresse'] ?? ''));
        $organizationName = trim((string) ($_POST['nom_organisation'] ?? ''));

        if ($email === '' || $password === '' || $frontendRole === '') {
            $this->json(422, ['status' => 'error', 'message' => 'Les champs email, password et role sont obligatoires.']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(422, ['status' => 'error', 'message' => 'Adresse email invalide.']);
        }

        if (mb_strlen($password) < 6) {
            $this->json(422, ['status' => 'error', 'message' => 'Le mot de passe doit contenir au moins 6 caracteres.']);
        }

        if (!isset($roleMap[$frontendRole])) {
            $this->json(422, ['status' => 'error', 'message' => 'Role invalide.']);
        }

        $firstName = $firstName !== '' ? $firstName : 'Utilisateur';
        $lastName = $lastName !== '' ? $lastName : 'FoodLoop';
        $phone = $phone !== '' ? $phone : '00000000';
        $address = $address !== '' ? $address : 'Adresse a renseigner';
        if ($organizationName === '') {
            $organizationName = match ($frontendRole) {
                'commerce' => 'Commerce FoodLoop',
                'admin_association' => 'Association FoodLoop',
                default => '',
            };
        }

        try {
            $userModel = new UserModel();
            if ($userModel->emailExists($email)) {
                $this->json(409, ['status' => 'error', 'message' => 'Cet email existe deja.']);
            }

            $userId = $userModel->create([
                'email' => $email,
                'password' => $password,
                'frontend_role' => $frontendRole,
                'oracle_role_code' => $roleMap[$frontendRole],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'address' => $address,
                'organization_name' => $organizationName,
            ]);

            $this->json(201, [
                'status' => 'success',
                'message' => 'Inscription reussie.',
                'role' => $frontendRole,
                'user_id' => $userId,
            ]);
        } catch (RuntimeException $exception) {
            $this->json(500, ['status' => 'error', 'message' => $exception->getMessage()]);
        } catch (\Throwable) {
            $this->json(500, ['status' => 'error', 'message' => 'Erreur lors de l inscription.']);
        }
    }

    public function logout(): never
    {
        $this->startSession();
        $_SESSION = [];
        session_destroy();
        header('Location: index.php?route=home');
        exit;
    }
}
