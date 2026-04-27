<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/cnx.php';

function paymentJsonResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function paymentNextId(PDO $pdo, string $table, string $column): int
{
    $sql = "SELECT NVL(MAX($column), 0) + 1 AS NEXT_ID FROM $table";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();

    return (int) ($row['NEXT_ID'] ?? 1);
}

function paymentGeneratePickupCode(): string
{
    return strtoupper('PK' . bin2hex(random_bytes(4)));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    paymentJsonResponse(405, [
        'status' => 'error',
        'message' => 'Methode non autorisee.',
    ]);
}

if (!isset($_SESSION['foodloop_user_id'], $_SESSION['foodloop_role'])) {
    paymentJsonResponse(401, [
        'status' => 'error',
        'message' => 'Utilisateur non connecte.',
    ]);
}

$role = (string) $_SESSION['foodloop_role'];

if (!in_array($role, ['acheteur', 'admin_association'], true)) {
    paymentJsonResponse(403, [
        'status' => 'error',
        'message' => 'Paiement reserve aux reservations acheteur ou association.',
    ]);
}

/*
|--------------------------------------------------------------------------
| Recuperation des donnees de paiement
|--------------------------------------------------------------------------
*/
$reservationId = (int) ($_POST['reservation_id'] ?? 0);
$amount = (float) ($_POST['montant'] ?? 0);
$method = trim((string) ($_POST['method'] ?? ''));
$cardNumber = preg_replace('/\s+/', '', (string) ($_POST['card_number'] ?? ''));
$cardHolder = trim((string) ($_POST['card_holder'] ?? ''));
$cardExpiry = trim((string) ($_POST['card_expiry'] ?? ''));
$cardCvc = trim((string) ($_POST['card_cvc'] ?? ''));
$receiverName = trim((string) ($_POST['receiver_name'] ?? ''));
$receiverPhone = trim((string) ($_POST['receiver_phone'] ?? ''));

if ($reservationId <= 0 || $amount <= 0 || $method === '') {
    paymentJsonResponse(422, [
        'status' => 'error',
        'message' => 'reservation_id, montant et method sont obligatoires.',
    ]);
}

/*
|--------------------------------------------------------------------------
| Mapping des methodes frontend vers Oracle
|--------------------------------------------------------------------------
| Frontend souhaite :
| - card
| - onsite
|
| Oracle export.sql accepte :
| - par carte
| - espece
|--------------------------------------------------------------------------
*/
$oracleMethod = match ($method) {
    'card' => 'par carte',
    'onsite' => 'espece',
    default => null,
};

if ($oracleMethod === null) {
    paymentJsonResponse(422, [
        'status' => 'error',
        'message' => 'Methode de paiement invalide.',
    ]);
}

/*
|--------------------------------------------------------------------------
| Validation specifique aux paiements carte
|--------------------------------------------------------------------------
| Pour la securite :
| - on ne stocke jamais le numero complet de carte
| - on ne stocke jamais le CVC en base
| Le schema Oracle ne prevoit d'ailleurs aucune colonne carte.
|--------------------------------------------------------------------------
*/
$maskedCard = null;

if ($method === 'card') {
    if ($cardHolder === '' || $cardExpiry === '' || $cardNumber === '' || $cardCvc === '') {
        paymentJsonResponse(422, [
            'status' => 'error',
            'message' => 'Les informations carte sont obligatoires.',
        ]);
    }

    if (!preg_match('/^\d{13,19}$/', $cardNumber)) {
        paymentJsonResponse(422, [
            'status' => 'error',
            'message' => 'Numero de carte invalide.',
        ]);
    }

    if (!preg_match('/^\d{3,4}$/', $cardCvc)) {
        paymentJsonResponse(422, [
            'status' => 'error',
            'message' => 'CVC invalide.',
        ]);
    }

    $maskedCard = '**** **** **** ' . substr($cardNumber, -4);
}

try {
    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Verification de la reservation et du proprietaire
    |--------------------------------------------------------------------------
    | On verifie que l'utilisateur connecte a le droit de payer cette reservation.
    |--------------------------------------------------------------------------
    */
    $sqlReservation = '
        SELECT
            ID_RESERVATION,
            UTILISATEUR_ID,
            ADMIN_ASSOCIATION_ID,
            STATUT,
            DATE_PICKUP
        FROM RESERVATIONS
        WHERE ID_RESERVATION = :reservation_id
        FOR UPDATE
    ';
    $stmtReservation = $pdo->prepare($sqlReservation);
    $stmtReservation->bindParam(':reservation_id', $reservationId, PDO::PARAM_INT);
    $stmtReservation->execute();
    $reservation = $stmtReservation->fetch();

    if ($reservation === false) {
        $pdo->rollBack();
        paymentJsonResponse(404, [
            'status' => 'error',
            'message' => 'Reservation introuvable.',
        ]);
    }

    $sessionUserId = (int) $_SESSION['foodloop_user_id'];

    if (
        ($role === 'acheteur' && (int) ($reservation['UTILISATEUR_ID'] ?? 0) !== $sessionUserId) ||
        ($role === 'admin_association' && (int) ($reservation['ADMIN_ASSOCIATION_ID'] ?? 0) !== $sessionUserId)
    ) {
        $pdo->rollBack();
        paymentJsonResponse(403, [
            'status' => 'error',
            'message' => 'Cette reservation ne vous appartient pas.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Eviter le double paiement
    |--------------------------------------------------------------------------
    | La table PAIEMENTS a une contrainte UNIQUE sur RESERVATION_ID.
    |--------------------------------------------------------------------------
    */
    $sqlPaymentExists = 'SELECT COUNT(*) AS TOTAL FROM PAIEMENTS WHERE RESERVATION_ID = :reservation_id';
    $stmtPaymentExists = $pdo->prepare($sqlPaymentExists);
    $stmtPaymentExists->bindParam(':reservation_id', $reservationId, PDO::PARAM_INT);
    $stmtPaymentExists->execute();
    $paymentExists = $stmtPaymentExists->fetch();

    if ((int) ($paymentExists['TOTAL'] ?? 0) > 0) {
        $pdo->rollBack();
        paymentJsonResponse(409, [
            'status' => 'error',
            'message' => 'Cette reservation a deja ete payee.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Insertion du paiement principal
    |--------------------------------------------------------------------------
    | Le schema Oracle conserve :
    | - reservation
    | - montant
    | - methode
    | - date
    | - statut
    |--------------------------------------------------------------------------
    */
    $paymentId = paymentNextId($pdo, 'PAIEMENTS', 'ID_PAIEMENT');
    $paymentStatus = 'confirme';
    $paymentDate = date('Y-m-d H:i:s');

    $sqlPayment = '
        INSERT INTO PAIEMENTS (
            ID_PAIEMENT,
            RESERVATION_ID,
            MONTANT,
            METHODE_PAIEMENT,
            DATE_PAIEMENT,
            STATUT,
            CREATED_AT
        ) VALUES (
            :id_paiement,
            :reservation_id,
            :montant,
            :methode_paiement,
            TO_TIMESTAMP(:date_paiement, ''YYYY-MM-DD HH24:MI:SS''),
            :statut,
            CURRENT_TIMESTAMP
        )
    ';
    $stmtPayment = $pdo->prepare($sqlPayment);
    $stmtPayment->bindParam(':id_paiement', $paymentId, PDO::PARAM_INT);
    $stmtPayment->bindParam(':reservation_id', $reservationId, PDO::PARAM_INT);
    $stmtPayment->bindParam(':montant', $amount);
    $stmtPayment->bindParam(':methode_paiement', $oracleMethod, PDO::PARAM_STR);
    $stmtPayment->bindParam(':date_paiement', $paymentDate, PDO::PARAM_STR);
    $stmtPayment->bindParam(':statut', $paymentStatus, PDO::PARAM_STR);
    $stmtPayment->execute();

    /*
    |--------------------------------------------------------------------------
    | Mise a jour du statut de reservation
    |--------------------------------------------------------------------------
    | Une reservation payee peut passer a "approved".
    |--------------------------------------------------------------------------
    */
    $reservationStatus = 'approved';
    $sqlReservationUpdate = 'UPDATE RESERVATIONS SET STATUT = :statut WHERE ID_RESERVATION = :reservation_id';
    $stmtReservationUpdate = $pdo->prepare($sqlReservationUpdate);
    $stmtReservationUpdate->bindParam(':statut', $reservationStatus, PDO::PARAM_STR);
    $stmtReservationUpdate->bindParam(':reservation_id', $reservationId, PDO::PARAM_INT);
    $stmtReservationUpdate->execute();

    /*
    |--------------------------------------------------------------------------
    | Cas paiement sur place
    |--------------------------------------------------------------------------
    | On genere un code de retrait et on l'enregistre dans PICKUPS.
    |--------------------------------------------------------------------------
    */
    $pickupCode = null;

    if ($method === 'onsite') {
        $pickupId = paymentNextId($pdo, 'PICKUPS', 'ID');
        $pickupCode = paymentGeneratePickupCode();
        $pickupStatus = 'scheduled';
        $scheduledAt = (string) $reservation['DATE_PICKUP'];

        if ($receiverName === '') {
            $receiverName = (string) ($_SESSION['foodloop_name'] ?? 'Client FoodLoop');
        }

        if ($receiverPhone === '') {
            $receiverPhone = '00000000';
        }

        $sqlPickup = '
            INSERT INTO PICKUPS (
                ID,
                RESERVATION_ID,
                SCHEDULED_AT,
                PICKED_UP_AT,
                RECEIVER_NAME,
                RECEIVER_PHONE,
                PICKUP_CODE,
                STATUS,
                CREATED_AT
            ) VALUES (
                :id_pickup,
                :reservation_id,
                TO_TIMESTAMP(:scheduled_at, ''YYYY-MM-DD HH24:MI:SS''),
                NULL,
                :receiver_name,
                :receiver_phone,
                :pickup_code,
                :status,
                CURRENT_TIMESTAMP
            )
        ';
        $stmtPickup = $pdo->prepare($sqlPickup);
        $stmtPickup->bindParam(':id_pickup', $pickupId, PDO::PARAM_INT);
        $stmtPickup->bindParam(':reservation_id', $reservationId, PDO::PARAM_INT);
        $stmtPickup->bindParam(':scheduled_at', $scheduledAt, PDO::PARAM_STR);
        $stmtPickup->bindParam(':receiver_name', $receiverName, PDO::PARAM_STR);
        $stmtPickup->bindParam(':receiver_phone', $receiverPhone, PDO::PARAM_STR);
        $stmtPickup->bindParam(':pickup_code', $pickupCode, PDO::PARAM_STR);
        $stmtPickup->bindParam(':status', $pickupStatus, PDO::PARAM_STR);
        $stmtPickup->execute();
    }

    $pdo->commit();

    paymentJsonResponse(201, [
        'status' => 'success',
        'message' => 'Paiement enregistre avec succes.',
        'payment_id' => $paymentId,
        'role' => $role,
        'payment_method' => $method,
        'masked_card' => $maskedCard,
        'pickup_code' => $pickupCode,
    ]);
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    paymentJsonResponse(500, [
        'status' => 'error',
        'message' => 'Erreur lors du paiement.',
    ]);
}
