<?php
declare(strict_types=1);

namespace App\Models;

use App\Infrastructure\Database;
use App\Support\PlatformClock;
use PDO;

final class PlatformRepository
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? Database::connection();
    }

    public function getPlatformOverview(string $actorRole): array
    {
        $announcementCount = $this->fetchCount('SELECT COUNT(*) FROM annonces');
        $reservationCount = $this->fetchCount('SELECT COUNT(*) FROM reservations');
        $distributionCount = $this->fetchCount("SELECT COUNT(*) FROM distributions WHERE statut = 'completed'");
        $notificationCount = $this->fetchCount('SELECT COUNT(*) FROM notifications WHERE read_at IS NULL');

        return [
            'name' => 'FoodLoop',
            'tagline' => 'Prototype reconstruit autour du diagramme de classes',
            'headline' => 'Base de donnees, roles, relations et ecrans sont maintenant organises autour des entites UML du projet.',
            'description' => 'Le modele couvre les annonces, reservations, paiements, recus, distributions, notifications, suggestions IA, rapports et les quatre profils connectes, sur une base MySQL coherente avec l UML.',
            'activeRole' => $actorRole,
            'referenceNow' => PlatformClock::displayReferenceNow(),
            'stats' => [
                ['value' => (string) $announcementCount, 'label' => 'annonces persistentes'],
                ['value' => (string) $reservationCount, 'label' => 'reservations reliees au workflow'],
                ['value' => (string) $distributionCount, 'label' => 'distributions terminees'],
                ['value' => (string) $notificationCount, 'label' => 'notifications non lues'],
            ],
        ];
    }

    public function getCategories(): array
    {
        return $this->connection
            ->query('SELECT id_cat, nom, description FROM categories ORDER BY nom')
            ->fetchAll();
    }

    public function getZones(): array
    {
        return $this->connection
            ->query('SELECT id_zone, nom, code_postal, ville_nom FROM zones_geographiques ORDER BY nom')
            ->fetchAll();
    }

    public function searchAnnouncements(array $filters, string $actorRole): array
    {
        $sql = <<<SQL
            SELECT
                a.id_annonce,
                a.titre,
                a.type_aliment,
                a.quantite,
                a.unite,
                a.localisation,
                a.statut,
                a.created_at AS date_creation,
                a.public_visibility_at AS date_visibilite_utilisateur,
                a.date_expiration,
                c.nom AS categorie_nom,
                z.nom AS zone_nom,
                z.ville_nom,
                z.code_postal,
                p.nom_commerce,
                p.type_commerce
            FROM annonces a
            INNER JOIN categories c ON c.id_cat = a.categorie_id
            INNER JOIN zones_geographiques z ON z.id_zone = a.zone_id
            INNER JOIN proprietaires_commerce p ON p.id_commerce = a.proprietaire_id
            WHERE 1 = 1
        SQL;

        $params = [];

        if ($filters['q'] !== '') {
            $sql .= ' AND (a.titre LIKE :keyword OR a.type_aliment LIKE :keyword OR a.localisation LIKE :keyword OR p.nom_commerce LIKE :keyword)';
            $params['keyword'] = '%' . $filters['q'] . '%';
        }

        if ($filters['category'] !== null) {
            $sql .= ' AND a.categorie_id = :category_id';
            $params['category_id'] = $filters['category'];
        }

        if ($filters['zone'] !== null) {
            $sql .= ' AND a.zone_id = :zone_id';
            $params['zone_id'] = $filters['zone'];
        }

        if (in_array($actorRole, ['invite', 'utilisateur'], true)) {
            $sql .= ' AND a.public_visibility_at <= :reference_now';
            $params['reference_now'] = PlatformClock::referenceNow();
        }

        $sql .= '
            GROUP BY
                a.id_annonce,
                a.titre,
                a.type_aliment,
                a.quantite,
                a.unite,
                a.localisation,
                a.statut,
                a.created_at,
                a.public_visibility_at,
                a.date_expiration,
                c.nom,
                z.nom,
                z.ville_nom,
                z.code_postal,
                p.nom_commerce,
                p.type_commerce
            ORDER BY a.created_at DESC
        ';

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function getPreferenceProfile(): array
    {
        $statement = $this->connection->query(
            <<<SQL
                SELECT
                    CONCAT(u.prenom, ' ', u.nom) AS utilisateur,
                    (
                        SELECT GROUP_CONCAT(c.nom ORDER BY c.nom SEPARATOR ', ')
                        FROM utilisateur_categories uc
                        INNER JOIN categories c ON c.id_cat = uc.categorie_id
                        WHERE uc.utilisateur_id = u.id_util
                    ) AS categories,
                    (
                        SELECT GROUP_CONCAT(z.nom ORDER BY z.nom SEPARATOR ', ')
                        FROM utilisateur_zones uz
                        INNER JOIN zones_geographiques z ON z.id_zone = uz.zone_id
                        WHERE uz.utilisateur_id = u.id_util
                    ) AS zones
                FROM utilisateurs u
                ORDER BY u.id_util
                LIMIT 1
            SQL
        );

        return $statement->fetch() ?: [];
    }

    public function getConsultationHistory(): array
    {
        $statement = $this->connection->query(
            <<<SQL
                SELECT
                    ca.date_consultation,
                    a.titre,
                    c.nom AS categorie_nom,
                    z.nom AS zone_nom
                FROM consultations_annonces ca
                INNER JOIN annonces a ON a.id_annonce = ca.annonce_id
                INNER JOIN categories c ON c.id_cat = a.categorie_id
                INNER JOIN zones_geographiques z ON z.id_zone = a.zone_id
                WHERE ca.utilisateur_id = 1
                ORDER BY ca.date_consultation DESC
                LIMIT 4
            SQL
        );

        return $statement->fetchAll();
    }

    public function getReservationFlow(): array
    {
        $statement = $this->connection->query(
            <<<SQL
                SELECT
                    r.id_reservation,
                    r.quantite_reservee,
                    r.statut,
                    r.date_reservation,
                    r.date_pickup,
                    a.titre AS annonce_titre,
                    COALESCE(CONCAT(u.prenom, ' ', u.nom), aa.nom_association) AS demandeur,
                    CASE
                        WHEN r.utilisateur_id IS NOT NULL THEN 'Utilisateur'
                        ELSE 'AdminAssociation'
                    END AS demandeur_type,
                    p.montant,
                    p.methode_paiement,
                    p.statut AS paiement_statut,
                    p.date_paiement,
                    re.date_emission,
                    GROUP_CONCAT(DISTINCT d.statut ORDER BY d.statut SEPARATOR ',') AS distribution_statuts
                FROM reservations r
                INNER JOIN annonces a ON a.id_annonce = r.annonce_id
                LEFT JOIN utilisateurs u ON u.id_util = r.utilisateur_id
                LEFT JOIN admins_association aa ON aa.id_admin_association = r.admin_association_id
                LEFT JOIN paiements p ON p.reservation_id = r.id_reservation
                LEFT JOIN recus re ON re.paiement_id = p.id_paiement
                LEFT JOIN reservation_distributions rd ON rd.reservation_id = r.id_reservation
                LEFT JOIN distributions d ON d.id_distribution = rd.distribution_id
                GROUP BY
                    r.id_reservation,
                    r.quantite_reservee,
                    r.statut,
                    r.date_reservation,
                    r.date_pickup,
                    a.titre,
                    demandeur,
                    demandeur_type,
                    p.montant,
                    p.methode_paiement,
                    p.statut,
                    p.date_paiement,
                    re.date_emission
                ORDER BY r.date_reservation DESC
            SQL
        );

        return array_map(
            static function (array $row): array {
                $row['distribution_statuts'] = $row['distribution_statuts'] !== null && $row['distribution_statuts'] !== ''
                    ? explode(',', $row['distribution_statuts'])
                    : [];

                return $row;
            },
            $statement->fetchAll()
        );
    }

    public function getNotificationsForRole(string $actorRole): array
    {
        if (!in_array($actorRole, ['utilisateur', 'association', 'commerce'], true)) {
            return [];
        }

        $columnMap = [
            'utilisateur' => ['column' => 'utilisateur_id', 'table' => 'utilisateurs', 'id' => 'id_util'],
            'association' => ['column' => 'admin_association_id', 'table' => 'admins_association', 'id' => 'id_admin_association'],
            'commerce' => ['column' => 'proprietaire_id', 'table' => 'proprietaires_commerce', 'id' => 'id_commerce'],
        ];

        $mapping = $columnMap[$actorRole];
        $recipientId = $this->fetchScalar(
            'SELECT ' . $mapping['id'] . ' FROM ' . $mapping['table'] . ' ORDER BY ' . $mapping['id'] . ' LIMIT 1'
        );

        $statement = $this->connection->prepare(
            <<<SQL
                SELECT
                    n.message_notification,
                    n.sent_at AS date_envoi,
                    (n.read_at IS NOT NULL) AS est_lue,
                    a.titre AS annonce_titre
                FROM notifications n
                LEFT JOIN annonces a ON a.id_annonce = n.annonce_id
                WHERE n.{$mapping['column']} = :recipient_id
                ORDER BY n.sent_at DESC
                LIMIT 4
            SQL
        );
        $statement->execute(['recipient_id' => $recipientId]);

        return $statement->fetchAll();
    }

    public function getSuggestions(string $actorRole): array
    {
        $sql = <<<SQL
            SELECT
                s.id AS id_ia,
                s.recommended_action AS message,
                a.titre AS annonce_titre,
                p.nom_commerce,
                (
                    SELECT aa.nom_association
                    FROM reservations r
                    INNER JOIN admins_association aa ON aa.id_admin_association = r.admin_association_id
                    WHERE r.annonce_id = a.id_annonce
                    ORDER BY r.id_reservation DESC
                    LIMIT 1
                ) AS nom_association
            FROM suggestions_ia s
            INNER JOIN annonces a ON a.id_annonce = s.food_item_id
            INNER JOIN proprietaires_commerce p ON p.id_commerce = a.proprietaire_id
        SQL;

        $params = [];

        if ($actorRole === 'commerce') {
            $sql .= ' WHERE a.proprietaire_id = :role_id';
            $params['role_id'] = $this->fetchScalar('SELECT id_commerce FROM proprietaires_commerce ORDER BY id_commerce LIMIT 1');
        } elseif ($actorRole === 'association') {
            $sql .= ' WHERE EXISTS (
                SELECT 1
                FROM reservations r
                WHERE r.annonce_id = a.id_annonce
                  AND r.admin_association_id = :role_id
            )';
            $params['role_id'] = $this->fetchScalar('SELECT id_admin_association FROM admins_association ORDER BY id_admin_association LIMIT 1');
        }

        $sql .= ' ORDER BY s.generated_at DESC LIMIT 4';

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function getReports(): array
    {
        $statement = $this->connection->query(
            <<<SQL
                SELECT
                    r.id AS id_rapport,
                    r.report_type,
                    CONCAT(DATE_FORMAT(r.period_start, '%d/%m/%Y'), ' -> ', DATE_FORMAT(r.period_end, '%d/%m/%Y')) AS periode,
                    r.total_food_saved_kg,
                    r.total_reservations,
                    r.total_distributions,
                    (
                        SELECT COUNT(*)
                        FROM report_consultations_commerce rc
                        WHERE rc.report_id = r.id
                    ) AS lectures_commerce,
                    (
                        SELECT COUNT(*)
                        FROM report_consultations_super_admin rs
                        WHERE rs.report_id = r.id
                    ) AS lectures_super_admin
                FROM reports r
                ORDER BY r.id DESC
            SQL
        );

        return $statement->fetchAll();
    }

    public function getCurrentActorSnapshot(string $actorRole): array
    {
        return match ($actorRole) {
            'utilisateur' => $this->fetchSingle(
                <<<SQL
                    SELECT
                        CONCAT(prenom, ' ', nom) AS nom_affiche,
                        email AS detail_principal,
                        'Consultation, reservation et paiement utilisateur.' AS resume
                    FROM utilisateurs
                    ORDER BY id_util
                    LIMIT 1
                SQL
            ) ?: [],
            'association' => $this->fetchSingle(
                <<<SQL
                    SELECT
                        nom_association AS nom_affiche,
                        'Association prioritaire' AS detail_principal,
                        'Peut reserver en priorite, planifier la collecte et gerer la distribution.' AS resume
                    FROM admins_association
                    ORDER BY id_admin_association
                    LIMIT 1
                SQL
            ) ?: [],
            'commerce' => $this->fetchSingle(
                <<<SQL
                    SELECT
                        nom_commerce AS nom_affiche,
                        type_commerce AS detail_principal,
                        'Peut publier des annonces, consulter les reservations et valider les recuperations.' AS resume
                    FROM proprietaires_commerce
                    ORDER BY id_commerce
                    LIMIT 1
                SQL
            ) ?: [],
            'superadmin' => $this->fetchSingle(
                <<<SQL
                    SELECT
                        'Console SuperAdmin' AS nom_affiche,
                        'Gouvernance plateforme' AS detail_principal,
                        'Peut gerer les comptes, moderer les annonces et consulter les rapports globaux.' AS resume
                    FROM super_admins
                    ORDER BY id_super_admin
                    LIMIT 1
                SQL
            ) ?: [],
            default => [
                'nom_affiche' => 'Visiteur anonyme',
                'detail_principal' => 'Consultation publique',
                'resume' => 'Peut rechercher et consulter les annonces, sans reservation ni paiement.',
            ],
        };
    }

    public function getDatabaseCoverage(): array
    {
        $tables = [
            'utilisateurs' => 'Classe Utilisateur',
            'proprietaires_commerce' => 'Classe ProprietaireCommerce',
            'admins_association' => 'Classe AdminAssociation',
            'super_admins' => 'Classe SuperAdmin',
            'annonces' => 'Classe Annonce',
            'reservations' => 'Classe Reservation',
            'paiements' => 'Classe Paiement',
            'recus' => 'Classe Recu',
            'distributions' => 'Classe Distribution',
            'notifications' => 'Classe Notification',
            'suggestions_ia' => 'Classe SuggestionIA',
            'reports' => 'Classe Rapport',
            'utilisateur_categories' => 'Relation Utilisateur <-> Categorie',
            'utilisateur_zones' => 'Relation Utilisateur <-> ZoneGeographique',
            'reservation_distributions' => 'Relation Reservation <-> Distribution',
        ];

        $coverage = [];

        foreach ($tables as $table => $description) {
            $coverage[] = [
                'table' => $table,
                'description' => $description,
                'rows' => $this->fetchCount('SELECT COUNT(*) FROM ' . $table),
            ];
        }

        return $coverage;
    }

    private function fetchCount(string $sql): int
    {
        return (int) $this->connection->query($sql)->fetchColumn();
    }

    private function fetchScalar(string $sql): int
    {
        return (int) $this->connection->query($sql)->fetchColumn();
    }

    private function fetchSingle(string $sql): array|false
    {
        return $this->connection->query($sql)->fetch();
    }
}
