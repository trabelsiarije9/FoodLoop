<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SuperAdminModel;
use RuntimeException;

final class SuperAdminController extends Controller
{
    public function loginWithAccessCode(): never
    {
        $this->requirePost();
        $this->startSession();

        $submittedCode = trim((string) ($_POST['adminCode'] ?? ''));
        if ($submittedCode === '') {
            $this->json(422, ['status' => 'error', 'message' => 'Code administrateur obligatoire.']);
        }

        if ($submittedCode !== 'FOODLOOP-ADMIN-2026') {
            $this->json(401, ['status' => 'error', 'message' => 'Code incorrect.']);
        }

        $_SESSION['foodloop_superadmin'] = true;
        $_SESSION['foodloop_role'] = 'superadmin';
        $_SESSION['foodloop_superadmin_name'] = 'Superadmin FoodLoop';

        $this->json(200, [
            'status' => 'success',
            'role' => 'superadmin',
            'name' => 'Superadmin FoodLoop',
        ]);
    }

    public function dashboard(): never
    {
        $this->startSession();
        $this->requireSuperAdminSession();

        try {
            $payload = (new SuperAdminModel())->getDashboardPayload();
            $this->json(200, ['status' => 'success'] + $payload);
        } catch (RuntimeException $exception) {
            $this->json(500, ['status' => 'error', 'message' => $exception->getMessage()]);
        } catch (\Throwable) {
            $this->json(500, ['status' => 'error', 'message' => 'Erreur lors du chargement du dashboard superadmin.']);
        }
    }

    public function mutateAccount(): never
    {
        $this->requirePost();
        $this->startSession();
        $this->requireSuperAdminSession();

        $action = trim((string) ($_POST['action'] ?? ''));
        $accountType = trim((string) ($_POST['account_type'] ?? ''));
        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($action === '' || $accountType === '' || $userId <= 0) {
            $this->json(422, ['status' => 'error', 'message' => 'action, account_type et user_id sont obligatoires.']);
        }

        try {
            $model = new SuperAdminModel();

            if ($action === 'suspend') {
                $model->suspendAccount($userId);
            } elseif ($action === 'unsuspend') {
                $model->unsuspendAccount($userId);
            } elseif ($action === 'delete') {
                match ($accountType) {
                    'buyer' => $model->deleteBuyer($userId),
                    'commerce' => $model->deleteCommerce($userId),
                    'association' => $model->deleteAssociation($userId),
                    default => throw new RuntimeException('Type de compte invalide.'),
                };
            } else {
                throw new RuntimeException('Action invalide.');
            }

            $payload = $model->getDashboardPayload();
            $this->json(200, [
                'status' => 'success',
                'message' => 'Action superadmin appliquee.',
            ] + $payload);
        } catch (RuntimeException $exception) {
            $this->json(500, ['status' => 'error', 'message' => $exception->getMessage()]);
        } catch (\Throwable) {
            $this->json(500, ['status' => 'error', 'message' => 'Erreur lors de l action superadmin.']);
        }
    }

    private function requireSuperAdminSession(): void
    {
        if (!isset($_SESSION['foodloop_superadmin']) || $_SESSION['foodloop_superadmin'] !== true) {
            $this->json(403, ['status' => 'error', 'message' => 'Acces reserve au superadmin.']);
        }
    }
}
