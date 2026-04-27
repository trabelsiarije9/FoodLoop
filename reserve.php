<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/cnx.php';

function reserveJsonResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function reserveNextId(PDO $pdo, string $table, string $column): int
{
    $sql = "SELECT NVL(MAX($column), 0) + 1 AS NEXT_ID FROM $table";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();

    return (int) ($row['NEXT_ID'] ?? 1);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reserveJsonResponse(405, [
        'status' => 'error',
        'message' => 'Methode non autorisee.',
    ]);
}

if (!isset($_SESSION['foodloop_user_id'], $_SESSION['foodloop_role'])) {
    reserveJsonResponse(401, [
        'status' => 'error',
        'message' => 'Utilisateur non connecte.',
    ]);
}

$role = (string) $_SESSION['foodloop_role'];

if (!in_array($role, ['acheteur', 'admin_association'], true)) {
    reserveJsonResponse(403, [
        'status' => 'error',
        'message' => 'Seuls acheteur et admin_association peuvent reserver.',
    ]);
}

/*
|--------------------------------------------------------------------------
| Recuperation des donnees de reservation
|--------------------------------------------------------------------------
*/
$annonceId = (int) ($_POST['annonce_id'] ?? 0);
$quantiteDemandee = (int) ($_POST['quantite'] ?? 0);
$pickupDate = trim((string) ($_POST['pickup_date'] ?? ''));

if ($annonceId <= 0 || $quantiteDemandee <= 0) {
    reserveJsonResponse(422, [
        'status' => 'error',
        'message' => 'Annonce et quantite sont obligatoires.',
    ]);
}

if ($pickupDate === '') {
    $pickupDate = date('Y-m-d H:i:s', strtotime('+2 hours'));
} else {
    $pickupTimestamp = strtotime($pickupDate);

    if ($pickupTimestamp === false) {
        reserveJsonResponse(422, [
            'status' => 'error',
            'message' => 'Date de pickup invalide.',
        ]);
    }

    $pickupDate = date('Y-m-d H:i:s', $pickupTimestamp);
}

$userId = null;
$associationAdminId = null;

if ($role === 'acheteur') {
    $userId = (int) $_SESSION['foodloop_user_id'];
}

if ($role === 'admin_association') {
    $associationAdminId = (int) $_SESSION['foodloop_user_id'];
}

try {
    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Lecture de l'annonce a reserver
    |--------------------------------------------------------------------------
    | On verrouille la ligne pour garder une logique propre de quantite.
    |--------------------------------------------------------------------------
    */
    $sqlAnnonce = '
        SELECT
            ID_ANNONCE,
            QUANTITE,
            STATUT
        FROM ANNONCES
        WHERE ID_ANNONCE = :id_annonce
        FOR UPDATE
    ';
    $stmtAnnonce = $pdo->prepare($sqlAnnonce);
    $stmtAnnonce->bindParam(':id_annonce', $annonceId, PDO::PARAM_INT);
    $stmtAnnonce->execute();
    $annonce = $stmtAnnonce->fetch();

    if ($annonce === false) {
        $pdo->rollBack();
        reserveJsonResponse(404, [
            'status' => 'error',
            'message' => 'Annonce introuvable.',
        ]);
    }

    $quantiteDisponible = (int) $annonce['QUANTITE'];
    $statutAnnonce = (string) $annonce['STATUT'];

    if (!in_array($statutAnnonce, ['available', 'priority_access'], true)) {
        $pdo->rollBack();
        reserveJsonResponse(409, [
            'status' => 'error',
            'message' => 'Cette annonce ne peut pas etre reservee actuellement.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Controle de quantite
    |--------------------------------------------------------------------------
    | La quantite demandee ne doit jamais depasser la quantite disponible.
    |--------------------------------------------------------------------------
    */
    if ($quantiteDemandee > $quantiteDisponible) {
        $pdo->rollBack();
        reserveJsonResponse(409, [
            'status' => 'error',
            'message' => 'Quantite demandee superieure au stock disponible.',
            'quantite_disponible' => $quantiteDisponible,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Insertion de la reservation
    |--------------------------------------------------------------------------
    | La contrainte Oracle impose :
    | - soit UTILISATEUR_ID renseigne
    | - soit ADMIN_ASSOCIATION_ID renseigne
    | - jamais les deux en meme temps
    |--------------------------------------------------------------------------
    */
    $reservationId = reserveNextId($pdo, 'RESERVATIONS', 'ID_RESERVATION');
    $reservationStatus = 'pending';
    $currentReservationDate = date('Y-m-d H:i:s');

    $sqlReservation = '
        INSERT INTO RESERVATIONS (
            ID_RESERVATION,
            ANNONCE_ID,
            UTILISATEUR_ID,
            ADMIN_ASSOCIATION_ID,
            QUANTITE_RESERVEE,
            STATUT,
            DATE_RESERVATION,
            DATE_PICKUP,
            CREATED_AT
        ) VALUES (
            :id_reservation,
            :annonce_id,
            :utilisateur_id,
            :admin_association_id,
            :quantite_reservee,
            :statut,
            TO_TIMESTAMP(:date_reservation, ''YYYY-MM-DD HH24:MI:SS''),
            TO_TIMESTAMP(:date_pickup, ''YYYY-MM-DD HH24:MI:SS''),
            CURRENT_TIMESTAMP
        )
    ';
    $stmtReservation = $pdo->prepare($sqlReservation);
    $stmtReservation->bindParam(':id_reservation', $reservationId, PDO::PARAM_INT);
    $stmtReservation->bindParam(':annonce_id', $annonceId, PDO::PARAM_INT);
    $stmtReservation->bindParam(':utilisateur_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmtReservation->bindParam(':admin_association_id', $associationAdminId, $associationAdminId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmtReservation->bindParam(':quantite_reservee', $quantiteDemandee, PDO::PARAM_INT);
    $stmtReservation->bindParam(':statut', $reservationStatus, PDO::PARAM_STR);
    $stmtReservation->bindParam(':date_reservation', $currentReservationDate, PDO::PARAM_STR);
    $stmtReservation->bindParam(':date_pickup', $pickupDate, PDO::PARAM_STR);
    $stmtReservation->execute();

    /*
    |--------------------------------------------------------------------------
    | Mise a jour du stock de l'annonce
    |--------------------------------------------------------------------------
    | Si tout le stock est reserve, on passe le statut a "reserved".
    |--------------------------------------------------------------------------
    */
    $nouvelleQuantite = $quantiteDisponible - $quantiteDemandee;
    $nouveauStatut = $nouvelleQuantite === 0 ? 'reserved' : $statutAnnonce;

    $sqlUpdateAnnonce = '
        UPDATE ANNONCES
        SET QUANTITE = :quantite_restante,
            STATUT = :nouveau_statut,
            UPDATED_AT = CURRENT_TIMESTAMP
        WHERE ID_ANNONCE = :id_annonce
    ';
    $stmtUpdateAnnonce = $pdo->prepare($sqlUpdateAnnonce);
    $stmtUpdateAnnonce->bindParam(':quantite_restante', $nouvelleQuantite, PDO::PARAM_INT);
    $stmtUpdateAnnonce->bindParam(':nouveau_statut', $nouveauStatut, PDO::PARAM_STR);
    $stmtUpdateAnnonce->bindParam(':id_annonce', $annonceId, PDO::PARAM_INT);
    $stmtUpdateAnnonce->execute();

    $pdo->commit();

    reserveJsonResponse(201, [
        'status' => 'success',
        'message' => 'Reservation enregistree avec succes.',
        'reservation_id' => $reservationId,
        'quantite_restante' => $nouvelleQuantite,
    ]);
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    reserveJsonResponse(500, [
        'status' => 'error',
        'message' => 'Erreur lors de la reservation.',
    ]);
}
