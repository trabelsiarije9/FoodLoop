<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\FoodItemModel;
use App\Models\HomeModel;

final class HomeController extends Controller
{
    private HomeModel $model;

    public function __construct()
    {
        $this->model = new HomeModel();
    }

    public static function publicNavigation(): array
    {
        return [
            ['label' => 'Accueil', 'page' => 'home'],
            ['label' => 'Plateforme', 'page' => 'platform'],
            ['label' => 'Interfaces', 'page' => 'actors'],
            ['label' => 'Contact', 'page' => 'contact'],
            ['label' => 'Connexion', 'page' => 'login'],
            ['label' => 'Inscription', 'page' => 'register'],
        ];
    }

    public static function privateNavigation(): array
    {
        $user = Auth::user();
        $base = [
            ['label' => 'Accueil', 'page' => 'home'],
            ['label' => 'Dashboard', 'page' => 'dashboard'],
        ];

        if ($user !== null && $user['role_code'] === 'system_admin') {
            return array_merge($base, [
                ['label' => 'Utilisateurs', 'page' => 'admin/users'],
                ['label' => 'Lots', 'page' => 'admin/items'],
                ['label' => 'Reservations', 'page' => 'admin/reservations'],
                ['label' => 'Briefs', 'page' => 'admin/contacts'],
            ]);
        }

        if ($user !== null && $user['role_code'] === 'business_owner') {
            return array_merge($base, [
                ['label' => 'Mes lots', 'page' => 'business/items'],
                ['label' => 'Demandes', 'page' => 'business/reservations'],
            ]);
        }

        if ($user !== null && $user['role_code'] === 'association_admin') {
            return array_merge($base, [
                ['label' => 'Catalogue', 'page' => 'association/catalog'],
                ['label' => 'Mes reservations', 'page' => 'association/reservations'],
            ]);
        }

        return array_merge($base, [
            ['label' => 'Catalogue', 'page' => 'catalog'],
            ['label' => 'Mes reservations', 'page' => 'reservations'],
        ]);
    }

    public function home(): string
    {
        return $this->render('public/home_app', $this->buildData('home', $this->model->getHomeData()));
    }

    public function platform(): string
    {
        return $this->render('public/platform_app', $this->buildData('platform', $this->model->getPlatformData()));
    }

    public function actors(): string
    {
        return $this->render('public/actors_app', $this->buildData('actors', $this->model->getActorsData()));
    }

    public function contact(): string
    {
        return $this->render('public/contact_app', $this->buildData('contact', $this->model->getContactData()));
    }

    public function dashboard(): string
    {
        Auth::requireLogin();
        $user = Auth::user();

        return match ($user['role_code']) {
            'system_admin' => (new AdminController())->dashboard(),
            'business_owner' => (new BusinessController())->dashboard(),
            'association_admin' => (new AssociationController())->dashboard(),
            default => (new UserController())->catalog(),
        };
    }

    public function notFound(): string
    {
        return $this->render('errors/404', [
            'title' => 'Page introuvable | FoodLoop',
            'description' => 'La page demandee est introuvable.',
            'currentPage' => '',
            'navigation' => Auth::check() ? self::privateNavigation() : self::publicNavigation(),
            'flashMessages' => pull_flashes(),
            'user' => Auth::user(),
        ]);
    }

    private function buildData(string $page, array $data): array
    {
        return array_merge(
            [
                'currentPage' => $page,
                'navigation' => Auth::check() ? self::privateNavigation() : self::publicNavigation(),
                'flashMessages' => pull_flashes(),
                'featuredItems' => $this->safeFeaturedItems(),
                'user' => Auth::user(),
            ],
            $data
        );
    }

    private function safeFeaturedItems(): array
    {
        try {
            return (new FoodItemModel())->latestAvailable(3);
        } catch (\Throwable) {
            return [];
        }
    }
}
