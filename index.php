<?php

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

use App\Controllers\AdminController;
use App\Controllers\AssociationController;
use App\Controllers\AuthController;
use App\Controllers\BusinessController;
use App\Controllers\ContactController;
use App\Controllers\HomeController;
use App\Controllers\UserController;

$route = trim((string) ($_GET['route'] ?? $_GET['page'] ?? 'home'), '/');
$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

$homeController = new HomeController();
$authController = new AuthController();
$adminController = new AdminController();
$associationController = new AssociationController();
$businessController = new BusinessController();
$contactController = new ContactController();
$userController = new UserController();

switch ($route) {
    case '':
    case 'home':
        echo $homeController->home();
        break;
    case 'platform':
        echo $homeController->platform();
        break;
    case 'actors':
        echo $homeController->actors();
        break;
    case 'contact':
        echo $method === 'POST' ? $contactController->submit() : $homeController->contact();
        break;
    case 'login':
        echo $method === 'POST' ? $authController->loginSubmit() : $authController->login();
        break;
    case 'register':
        echo $method === 'POST' ? $authController->registerSubmit() : $authController->register();
        break;
    case 'logout':
        echo $authController->logout();
        break;
    case 'dashboard':
        echo $homeController->dashboard();
        break;
    case 'admin':
        echo $adminController->dashboard();
        break;
    case 'admin/users':
        echo $method === 'POST' ? $adminController->saveUser() : $adminController->users();
        break;
    case 'admin/users/delete':
        echo $adminController->deleteUser();
        break;
    case 'admin/items':
        echo $method === 'POST' ? $adminController->saveFoodItem() : $adminController->items();
        break;
    case 'admin/items/delete':
        echo $adminController->deleteFoodItem();
        break;
    case 'admin/reservations':
        echo $method === 'POST' ? $adminController->updateReservationStatus() : $adminController->reservations();
        break;
    case 'admin/contacts':
        echo $adminController->contacts();
        break;
    case 'business':
        echo $businessController->dashboard();
        break;
    case 'association':
        echo $associationController->dashboard();
        break;
    case 'association/catalog':
        echo $associationController->catalog();
        break;
    case 'association/reservations':
        echo $associationController->reservations();
        break;
    case 'association/reserve':
        echo $associationController->reserve();
        break;
    case 'business/items':
        echo $method === 'POST' ? $businessController->saveFoodItem() : $businessController->items();
        break;
    case 'business/items/delete':
        echo $businessController->deleteFoodItem();
        break;
    case 'business/reservations':
        echo $method === 'POST' ? $businessController->updateReservationStatus() : $businessController->reservations();
        break;
    case 'catalog':
        echo $userController->catalog();
        break;
    case 'reservations':
        echo $userController->reservations();
        break;
    case 'reserve':
        echo $userController->reserve();
        break;
    default:
        http_response_code(404);
        echo $homeController->notFound();
        break;
}
