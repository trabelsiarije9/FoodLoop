<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/cnx.php';

function loginJsonResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function loginMapOracleRoleToFrontend(string $oracleRole): ?string
{
    return match ($oracleRole) {
        'citizen' => 'acheteur',
        'business_owner' => 'commerce',
        'association_admin' => 'admin_association',
        default => null,
    };
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    loginJsonResponse(405, [
        'status' => 'error',
        'message' => 'Methode non autorisee.',
    ]);
}

/*
|--------------------------------------------------------------------------
| Recuperation des champs de connexion
|--------------------------------------------------------------------------
*/
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    loginJsonResponse(422, [
        'status' => 'error',
        'message' => 'Email et mot de passe obligatoires.',
    ]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    loginJsonResponse(422, [
        'status' => 'error',
        'message' => 'Adresse email invalide.',
    ]);
}

try {
    /*
    |--------------------------------------------------------------------------
    | Recherche de l'utilisateur avec jointure sur le role
    |--------------------------------------------------------------------------
    | On recupere uniquement l'utilisateur correspondant a l'email saisi.
    |--------------------------------------------------------------------------
    */
    $sql = '
        SELECT
            u.ID_UTIL,
            u.EMAIL,
            u.MOT_DE_PASSE,
            u.PRENOM,
            u.NOM,
            r.CODE AS ROLE_CODE
        FROM UTILISATEURS u
        INNER JOIN ROLES r ON r.ID_ROLE = u.ROLE_ID
        WHERE u.EMAIL = :email
    ';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user === false) {
        loginJsonResponse(401, [
            'status' => 'error',
            'message' => 'Identifiants invalides.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Verification du mot de passe hashé
    |--------------------------------------------------------------------------
    | password_verify() est la bonne pratique pour comparer un mot de passe
    | saisi avec le hash stocke en base.
    |--------------------------------------------------------------------------
    */
    $hashedPassword = (string) $user['MOT_DE_PASSE'];

    if (!password_verify($password, $hashedPassword)) {
        loginJsonResponse(401, [
            'status' => 'error',
            'message' => 'Identifiants invalides.',
        ]);
    }

    $frontendRole = loginMapOracleRoleToFrontend((string) $user['ROLE_CODE']);

    if ($frontendRole === null) {
        loginJsonResponse(403, [
            'status' => 'error',
            'message' => 'Role non autorise pour cette interface.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Enregistrement des informations de session
    |--------------------------------------------------------------------------
    | Ces donnees seront reutilisees par create_product.php, reserve.php
    | et payment.php afin de connaitre l'utilisateur connecte.
    |--------------------------------------------------------------------------
    */
    $_SESSION['foodloop_user_id'] = (int) $user['ID_UTIL'];
    $_SESSION['foodloop_role'] = $frontendRole;
    $_SESSION['foodloop_email'] = (string) $user['EMAIL'];
    $_SESSION['foodloop_name'] = trim((string) $user['PRENOM'] . ' ' . (string) $user['NOM']);

    loginJsonResponse(200, [
        'status' => 'success',
        'message' => 'Connexion reussie.',
        'role' => $frontendRole,
        'user_id' => (int) $user['ID_UTIL'],
    ]);
} catch (PDOException $exception) {
    loginJsonResponse(500, [
        'status' => 'error',
        'message' => 'Erreur lors de la connexion.',
    ]);
}
