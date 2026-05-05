<?php
declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

$controller = new App\Controllers\SuperAdminController();
$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

if ($method === 'GET') {
    $controller->dashboard();
}

$controller->mutateAccount();
