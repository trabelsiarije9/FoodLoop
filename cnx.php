<?php
declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

use App\Core\Database;
use App\Core\JsonResponse;

try {
    $pdo = Database::getConnection();
} catch (RuntimeException $exception) {
    JsonResponse::send(500, [
        'status' => 'error',
        'message' => $exception->getMessage(),
    ]);
}
