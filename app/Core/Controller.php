<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\SuperAdminModel;

abstract class Controller
{
    protected function json(int $statusCode, array $payload): never
    {
        JsonResponse::send($statusCode, $payload);
    }

    protected function requirePost(): void
    {
        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            $this->json(405, [
                'status' => 'error',
                'message' => 'Methode non autorisee.',
            ]);
        }
    }

    protected function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    protected function ensureSessionAccountIsActive(): void
    {
        $this->startSession();

        if ((string) ($_SESSION['foodloop_role'] ?? '') === 'superadmin') {
            return;
        }

        $userId = (int) ($_SESSION['foodloop_user_id'] ?? 0);
        if ($userId <= 0) {
            return;
        }

        if ((new SuperAdminModel())->isSuspended($userId)) {
            $_SESSION = [];
            session_destroy();
            $this->json(403, [
                'status' => 'error',
                'message' => 'Ce compte est suspendu. Contactez le superadmin.',
            ]);
        }
    }
}
