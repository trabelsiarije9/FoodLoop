<?php

declare(strict_types=1);

namespace App\Models;

final class HomeModel
{
    public function getNavigation(): array
    {
        return [
            ['label' => 'Accueil', 'page' => 'home'],
            ['label' => 'Plateforme', 'page' => 'platform'],
            ['label' => 'Acteurs', 'page' => 'actors'],
            ['label' => 'Contact', 'page' => 'contact'],
        ];
    }

    public function getHomeData(): array
    {
        return [
            'title' => 'FoodLoop | Gestion intelligente des surplus alimentaires',
            'description' => 'FoodLoop connecte commerces, associations et citoyens pour redistribuer les invendus avec rapidite et impact.',
            'heroStats' => [
                ['value' => '48', 'label' => 'Commerces connectes'],
                ['value' => '2.4T', 'label' => 'Repas sauves / semaine'],
                ['value' => '12', 'label' => 'Associations prioritaires'],
            ],
            'highlights' => [
                ['title' => 'Publication rapide', 'text' => 'Les commerces publient un lot en quelques secondes avec quantite, date limite et point de retrait.'],
                ['title' => 'Priorite solidaire', 'text' => 'Les associations recoivent une fenetre prioritaire avant l’ouverture au grand public.'],
                ['title' => 'Pilotage en temps reel', 'text' => 'Le tableau de bord suit disponibilite, reservations, retraits et impact durable.'],
            ],
            'showcaseCards' => [
                ['tag' => 'Nouveau lot', 'title' => 'Viennoiseries du jour', 'meta' => '34 portions • Tunis Centre'],
                ['tag' => 'Priorite association', 'title' => 'Fruits de saison', 'meta' => '22 kg • La Marsa'],
                ['tag' => 'Collecte planifiee', 'title' => 'Plats prets a retirer', 'meta' => '16 portions • Sfax'],
            ],
            'journey' => [
                'Le commerce publie un lot alimentaire.',
                'Les associations sont notifiees en priorite.',
                'Une reservation est confirmee avec horaire de retrait.',
                'Le systeme trace la redistribution et l’impact.',
            ],
        ];
    }

    public function getPlatformData(): array
    {
        return [
            'title' => 'FoodLoop | Plateforme',
            'description' => 'Decouvrez le fonctionnement produit, les modules et la logique de redistribution de FoodLoop.',
            'modules' => [
                ['name' => 'Authentification', 'text' => 'Connexion, inscription et controle d’acces par role.'],
                ['name' => 'Gestion des lots', 'text' => 'Ajout, modification, expiration et etat des invendus.'],
                ['name' => 'Reservations', 'text' => 'Attribution des lots selon priorite et disponibilite.'],
                ['name' => 'Notifications', 'text' => 'Alertes email, web et rappels de retrait automatises.'],
                ['name' => 'IA & RPA', 'text' => 'Suggestions de redistribution et taches repetitives automatisees.'],
                ['name' => 'Reporting', 'text' => 'Suivi des performances, de l’impact et des volumes sauves.'],
            ],
            'workflow' => [
                ['step' => '01', 'title' => 'Publier', 'text' => 'Le commercant decrit le lot, sa quantite et sa date d’expiration.'],
                ['step' => '02', 'title' => 'Prioriser', 'text' => 'Le systeme ouvre d’abord l’acces aux associations eligibles.'],
                ['step' => '03', 'title' => 'Reserver', 'text' => 'Une reservation validee bloque le stock et prepare le retrait.'],
                ['step' => '04', 'title' => 'Tracer', 'text' => 'Le retrait et la distribution nourrissent les indicateurs d’impact.'],
            ],
            'metrics' => [
                ['label' => 'Taux de retrait a temps', 'value' => '92%'],
                ['label' => 'Lots publies ce mois', 'value' => '1 186'],
                ['label' => 'Alertes intelligentes', 'value' => '360'],
            ],
        ];
    }

    public function getActorsData(): array
    {
        return [
            'title' => 'FoodLoop | Acteurs',
            'description' => 'Les trois profils de la plateforme et leurs interactions dans le systeme.',
            'roles' => [
                [
                    'id' => 'business',
                    'name' => 'Food Business Owners',
                    'headline' => 'Restaurants, boulangeries, supermarches',
                    'text' => 'Ils publient les surplus, definissent les horaires de retrait et suivent les lots redistribues.',
                    'points' => ['Creation de lots', 'Suivi des statuts', 'Historique des publications'],
                ],
                [
                    'id' => 'association',
                    'name' => 'Association Administrators',
                    'headline' => 'Associations et organisations caritatives',
                    'text' => 'Ils beneficient d’une priorite, organisent la collecte et pilotent la distribution aux beneficiaires.',
                    'points' => ['Acces prioritaire', 'Coordination des retraits', 'Suivi de redistribution'],
                ],
                [
                    'id' => 'user',
                    'name' => 'Regular Users',
                    'headline' => 'Citoyens proches des points de retrait',
                    'text' => 'Ils consultent les disponibilites restantes, reservent et recoivent des notifications ciblees.',
                    'points' => ['Recherche locale', 'Reservation simple', 'Alertes personnalisees'],
                ],
            ],
            'faq' => [
                ['q' => 'Pourquoi donner la priorite aux associations ?', 'a' => 'Pour atteindre plus rapidement les populations vulnerables et optimiser l’impact social.'],
                ['q' => 'Comment limiter les pertes ?', 'a' => 'Avec des alertes sur les dates limites, une fenetre prioritaire et une organisation de retrait claire.'],
                ['q' => 'Le systeme est-il evolutif ?', 'a' => 'Oui, l’architecture MVC et la base relationnelle permettent d’ajouter login, dashboard et CRUD facilement.'],
            ],
        ];
    }

    public function getContactData(): array
    {
        return [
            'title' => 'FoodLoop | Contact',
            'description' => 'Page de contact et de prise de brief pour lancer la plateforme FoodLoop.',
            'contactCards' => [
                ['title' => 'Email projet', 'text' => 'contact@foodloop.tn'],
                ['title' => 'Localisation', 'text' => 'Tunis, Tunisie'],
                ['title' => 'Disponibilite', 'text' => 'Lun - Ven, 09:00 - 18:00'],
            ],
            'steps' => [
                'Definir le type d’acteur a onboarder.',
                'Choisir les modules prioritaires.',
                'Connecter la base Oracle et les vues MVC.',
            ],
        ];
    }
}
