<?php
declare(strict_types=1);

namespace App\Support;

final class UmlModelCatalog
{
    public static function classes(): array
    {
        return [
            [
                'name' => 'Utilisateur',
                'table' => 'utilisateurs',
                'attributes' => ['idUtil:int', 'prenom:string', 'nom:string', 'email:string', 'MDP:string', 'numTel:int', 'adresse:string'],
                'operations' => ['Sinscrire()', 'SeConnecter()', 'modifierProfil()', 'Consulterexcedents()', 'RechercherProduits()', 'FiltrerParCategorie()', 'FiltrerParZone()', 'ReserverProduit()', 'AnnulerReservation()', 'effectuerPaiement()', 'consulterHistorique()', 'recevoirNotifs()'],
            ],
            [
                'name' => 'ZoneGeographique',
                'table' => 'zones_geographiques',
                'attributes' => ['idZG:int', 'nom:string', 'codePostal:int', 'villeNom:string'],
                'operations' => ['listerAnnonceZ()'],
            ],
            [
                'name' => 'Categorie',
                'table' => 'categories',
                'attributes' => ['idCat:int', 'nom:string', 'description:string'],
                'operations' => ['listerAnnonces()'],
            ],
            [
                'name' => 'Annonce',
                'table' => 'annonces',
                'attributes' => ['idAn:int', 'titre:string', 'typeAliment:string', 'quantite:int', 'unite:string', 'localisation:string', 'statut:string', 'DateCreation:DateTime', 'DateVisibiliteUtilisateur:DateTime', 'DateExpiration:DateTime'],
                'operations' => ['modifier()', 'supprimer()', 'changerStatut()'],
            ],
            [
                'name' => 'Notification',
                'table' => 'notifications',
                'attributes' => ['idNotif:int', 'messageNotif:string', 'dateEnvoi:DateTime'],
                'operations' => ['envoyerNotif()', 'marquerLue()'],
            ],
            [
                'name' => 'SuggestionIA',
                'table' => 'suggestions_ia',
                'attributes' => ['idIA:int', 'message:string'],
                'operations' => ['recommanderDistributions()'],
            ],
            [
                'name' => 'Reservation',
                'table' => 'reservations',
                'attributes' => ['idR:int', 'qteReserve:int', 'statut:string', 'dateReservation:DateTime', 'datePickUp:DateTime'],
                'operations' => ['confirmer()', 'annuler()', 'validerRecuperation()'],
            ],
            [
                'name' => 'Paiement',
                'table' => 'paiements',
                'attributes' => ['idP:int', 'montant:float', 'methodePaiement:string', 'datePaiement:DateTime'],
                'operations' => ['traiter()', 'rembourser()', 'confirmerPaiement()', 'annulerPaiement()'],
            ],
            [
                'name' => 'Recu',
                'table' => 'recus',
                'attributes' => ['idRe:int', 'montant:float', 'dateEmission:DateTime'],
                'operations' => ['imprimer()', 'envoyerParEmail()'],
            ],
            [
                'name' => 'Distribution',
                'table' => 'distributions',
                'attributes' => ['idD:int', 'quantiteDistrib:int', 'dateDistrib:DateTime', 'statut:string'],
                'operations' => ['confirmerCollecte()', 'enregistrer()'],
            ],
            [
                'name' => 'ProprietaireCommerce',
                'table' => 'proprietaires_commerce',
                'attributes' => ['nomCommerce:string', 'typeCommerce:string', 'dateInscription:DateTime'],
                'operations' => ['SeConnecter()', 'ajouterExcedent()', 'modifierExcedent()', 'supprimerExcedent()', 'consulterReservation()', 'validerRecuperation()', 'consulterStatistiques()'],
            ],
            [
                'name' => 'AdminAssociation',
                'table' => 'admins_association',
                'attributes' => ['nomAs:string'],
                'operations' => ['reserverEnPRIORITE()', 'reserverEnGrandeQT()', 'gererDistribution()', 'planifieCollecte()'],
            ],
            [
                'name' => 'SuperAdmin',
                'table' => 'super_admins',
                'attributes' => ['MotdePasse:string'],
                'operations' => ['gererUtilisateur()', 'gererCommerce()', 'gererAssociation()', 'modererAnnonces()', 'superviserPlateforme()'],
            ],
            [
                'name' => 'Rapport',
                'table' => 'reports',
                'attributes' => ['idRapp:int', 'nbAnnonce:int', 'tauxDistribution:float'],
                'operations' => ['generer()', 'exporterRapport()'],
            ],
        ];
    }

    public static function integrationNotes(): array
    {
        return [
            'L utilisateur non connecte est gere comme un etat d acces et non comme une table persistante.',
            'Les relations plusieurs-a-plusieurs du diagramme sont materialisees avec les tables pivots utilisateur_categories, utilisateur_zones et reservation_distributions.',
            'Le projet est initialise sur MySQL via PDO, avec bootstrap automatique du schema et du jeu de donnees.',
        ];
    }
}
