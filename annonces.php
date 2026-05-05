<?php
declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

$controller = new App\Controllers\ProductController();

if (isset($_GET['feed'])) {
    $controller->feed();
}

if (isset($_GET['owner_feed'])) {
    $controller->ownerFeed();
}

$controller->availability();
