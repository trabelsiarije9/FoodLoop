<?php
declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\PageController;

$route = trim((string) ($_GET['route'] ?? $_GET['page'] ?? 'home'), '/');
$pageController = new PageController();

switch ($route) {
    case '':
    case 'home':
        $pageController->render('home');
        break;
    case 'login':
    case 'register':
        $pageController->render('login');
        break;
    case 'dashboard':
    case 'catalog':
    case 'buyer':
        $pageController->render('buyer_dashboard');
        break;
    case 'business':
        $pageController->render('merchant_dashboard');
        break;
    case 'association':
        $pageController->render('admin_association_dashboard');
        break;
    case 'payment':
        $pageController->render('payment');
        break;
    case 'admin':
        $pageController->render('superadmin_dashboard');
        break;
    case 'admin-access':
        $pageController->render('admin_access');
        break;
    case 'logout':
        (new AuthController())->logout();
        break;
    default:
        http_response_code(404);
        echo '404 - Page introuvable';
        break;
}
