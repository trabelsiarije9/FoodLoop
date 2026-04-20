<?php
declare(strict_types=1);

namespace App\Support;

final class ActorContext
{
    private const DEFAULT_ROLE = 'invite';

    public static function resolve(array $query): string
    {
        $requestedRole = isset($query['actor']) ? (string) $query['actor'] : null;

        if ($requestedRole !== null && self::isValid($requestedRole)) {
            $_SESSION['actor'] = $requestedRole;
        }

        $sessionRole = isset($_SESSION['actor']) ? (string) $_SESSION['actor'] : self::DEFAULT_ROLE;

        return self::isValid($sessionRole) ? $sessionRole : self::DEFAULT_ROLE;
    }

    public static function catalogue(): array
    {
        return [
            'invite' => [
                'key' => 'invite',
                'label' => 'Utilisateur non connecte',
                'badge' => 'Etat hors diagramme',
                'connected' => false,
                'summary' => 'Peut chercher et consulter les annonces, sans reserver ni declencher d actions transactionnelles.',
                'actions' => [
                    'RechercherProduits()',
                    'FiltrerParCategorie()',
                    'FiltrerParZone()',
                    'Consulter les annonces visibles',
                ],
                'restrictions' => [
                    'Pas de reservation',
                    'Pas de paiement',
                    'Pas de notifications personnelles',
                ],
            ],
            'utilisateur' => [
                'key' => 'utilisateur',
                'label' => 'Utilisateur connecte',
                'badge' => 'Classe Utilisateur',
                'connected' => true,
                'summary' => 'Profil citoyen authentifie qui reserve, annule, paie et consulte son historique.',
                'actions' => [
                    'Sinscrire()',
                    'SeConnecter()',
                    'ReserverProduit()',
                    'AnnulerReservation()',
                    'effectuerPaiement()',
                    'consulterHistorique()',
                    'recevoirNotifs()',
                ],
                'restrictions' => [
                    'Ne publie pas d annonces',
                    'Ne modere pas la plateforme',
                ],
            ],
            'association' => [
                'key' => 'association',
                'label' => 'AdminAssociation',
                'badge' => 'Classe metier',
                'connected' => true,
                'summary' => 'Profil prioritaire pour reserver en premier, organiser les collectes et gerer la distribution.',
                'actions' => [
                    'reserverEnPRIORITE()',
                    'reserverEnGrandeQT()',
                    'gererDistribution()',
                    'planifieCollecte()',
                ],
                'restrictions' => [
                    'Ne gere pas les comptes globaux',
                    'Ne supprime pas les commerces',
                ],
            ],
            'commerce' => [
                'key' => 'commerce',
                'label' => 'ProprietaireCommerce',
                'badge' => 'Classe metier',
                'connected' => true,
                'summary' => 'Publie les excedents, suit les reservations, valide les recuperations et consulte les statistiques.',
                'actions' => [
                    'ajouterExcedent()',
                    'modifierExcedent()',
                    'supprimerExcedent()',
                    'consulterReservation()',
                    'validerRecuperation()',
                    'consulterStatistiques()',
                ],
                'restrictions' => [
                    'Ne gere pas les utilisateurs globaux',
                ],
            ],
            'superadmin' => [
                'key' => 'superadmin',
                'label' => 'SuperAdmin',
                'badge' => 'Classe metier',
                'connected' => true,
                'summary' => 'Supervise la plateforme, modere les annonces et consulte les rapports transverses.',
                'actions' => [
                    'gererUtilisateur()',
                    'gererCommerce()',
                    'gererAssociation()',
                    'modererAnnonces()',
                    'superviserPlateforme()',
                ],
                'restrictions' => [
                    'Pas de paiement utilisateur',
                    'Pas de reservation citoyenne',
                ],
            ],
        ];
    }

    public static function details(string $role): array
    {
        return self::catalogue()[$role] ?? self::catalogue()[self::DEFAULT_ROLE];
    }

    public static function announcementAction(string $role, string $status): array
    {
        $lockedForCitizen = in_array($status, ['priority_access', 'reserved', 'picked_up', 'expired', 'cancelled'], true);

        return match ($role) {
            'utilisateur' => $lockedForCitizen
                ? ['label' => 'Annonce non reservable pour ce profil', 'enabled' => false, 'tone' => 'muted']
                : ['label' => 'Reserver et payer', 'enabled' => true, 'tone' => 'primary'],
            'association' => in_array($status, ['reserved', 'picked_up', 'expired', 'cancelled'], true)
                ? ['label' => 'Annonce deja traitee', 'enabled' => false, 'tone' => 'muted']
                : ($status === 'priority_access'
                    ? ['label' => 'Suivre la collecte', 'enabled' => true, 'tone' => 'secondary']
                    : ['label' => 'Reserver en priorite', 'enabled' => true, 'tone' => 'primary']),
            'commerce' => ['label' => 'Modifier ou supprimer', 'enabled' => true, 'tone' => 'secondary'],
            'superadmin' => ['label' => 'Moderer l annonce', 'enabled' => true, 'tone' => 'secondary'],
            default => ['label' => 'Connexion requise pour reserver', 'enabled' => false, 'tone' => 'muted'],
        };
    }

    private static function isValid(string $role): bool
    {
        return array_key_exists($role, self::catalogue());
    }
}
