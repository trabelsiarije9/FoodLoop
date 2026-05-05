<?php
declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

(new App\Controllers\ProductController())->create();
