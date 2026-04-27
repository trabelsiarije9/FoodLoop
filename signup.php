<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/cnx.php';

/*
|--------------------------------------------------------------------------
| Fonctions utilitaires
|--------------------------------------------------------------------------
| Chaque fonction ci-dessous sert a garder un code propre et lisible.
|--------------------------------------------------------------------------
*/
function signupJsonResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function signupGetPostString(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function signupRoleMap(): array
{
    /*
    |--------------------------------------------------------------------------
    | Mapping entre les roles du frontend et les codes Oracle
    |--------------------------------------------------------------------------
    | Frontend :
    | - acheteur
    | - commerce
    | - admin_association
    |
    | Oracle export.sql :
    | - citizen
    | - business_owner
    | - association_admin
    |--------------------------------------------------------------------------
    */
    return [
        'acheteur' => 'citizen',
        'commerce' => 'business_owner',
        'admin_association' => 'association_admin',
    ];
}

function signupNextId(PDO $pdo, string $table, string $column): int
{
    $sql = "SELECT NVL(MAX($column), 0) + 1 AS NEXT_ID FROM $table";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();

    return (int) ($row['NEXT_ID'] ?? 1);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    signupJsonResponse(405, [
        'status' => 'error',
        'message' => 'Methode non autorisee.',
    ]);
}

/*
|--------------------------------------------------------------------------
| Recuperation et validation des donnees recues
|--------------------------------------------------------------------------
| Le sujet demande email + password + role.
| Certains champs Oracle sont obligatoires en base (PRENOM, NOM, NUM_TEL, ADRESSE).
| On accepte donc des valeurs optionnelles si le frontend les envoie,
| sinon on place des valeurs par defaut propres et explicites.
|--------------------------------------------------------------------------
*/
$email = strtolower(signupGetPostString('email'));
$password = (string) ($_POST['password'] ?? '');
$frontendRole = signupGetPostString('role');
$firstName = signupGetPostString('prenom');
$lastName = signupGetPostString('nom');
$phone = signupGetPostString('telephone');
$address = signupGetPostString('adresse');
$organizationName = signupGetPostString('nom_organisation');

if ($email === '' || $password === '' || $frontendRole === '') {
    signupJsonResponse(422, [
        'status' => 'error',
        'message' => 'Les champs email, password et role sont obligatoires.',
    ]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    signupJsonResponse(422, [
        'status' => 'error',
        'message' => 'Adresse email invalide.',
    ]);
}

if (mb_strlen($password) < 6) {
    signupJsonResponse(422, [
        'status' => 'error',
        'message' => 'Le mot de passe doit contenir au moins 6 caracteres.',
    ]);
}

$roleMap = signupRoleMap();

if (!array_key_exists($frontendRole, $roleMap)) {
    signupJsonResponse(422, [
        'status' => 'error',
        'message' => 'Role invalide.',
    ]);
}

/*
|--------------------------------------------------------------------------
| Valeurs de secours pour respecter les contraintes Oracle
|--------------------------------------------------------------------------
| La base Oracle impose PRENOM, NOM, NUM_TEL et ADRESSE non nuls.
|--------------------------------------------------------------------------
*/
if ($firstName === '') {
    $firstName = 'Utilisateur';
}

if ($lastName === '') {
    $lastName = 'FoodLoop';
}

if ($phone === '') {
    $phone = '00000000';
}

if ($address === '') {
    $address = 'Adresse a renseigner';
}

if ($organizationName === '') {
    $organizationName = match ($frontendRole) {
        'commerce' => 'Commerce FoodLoop',
        'admin_association' => 'Association FoodLoop',
        default => '',
    };
}

try {
    /*
    |--------------------------------------------------------------------------
    | Verification de l'unicite de l'email
    |--------------------------------------------------------------------------
    | On controle avant insertion pour retourner un message clair.
    |--------------------------------------------------------------------------
    */
    $sqlEmail = 'SELECT COUNT(*) AS TOTAL FROM UTILISATEURS WHERE EMAIL = :email';
    $stmtEmail = $pdo->prepare($sqlEmail);
    $stmtEmail->bindParam(':email', $email, PDO::PARAM_STR);
    $stmtEmail->execute();
    $emailRow = $stmtEmail->fetch();

    if ((int) ($emailRow['TOTAL'] ?? 0) > 0) {
        signupJsonResponse(409, [
            'status' => 'error',
            'message' => 'Cet email existe deja.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Recuperation de l'identifiant du role Oracle
    |--------------------------------------------------------------------------
    | On ne permet jamais l'inscription superadmin.
    |--------------------------------------------------------------------------
    */
    $oracleRoleCode = $roleMap[$frontendRole];

    $sqlRole = 'SELECT ID_ROLE FROM ROLES WHERE CODE = :role_code';
    $stmtRole = $pdo->prepare($sqlRole);
    $stmtRole->bindParam(':role_code', $oracleRoleCode, PDO::PARAM_STR);
    $stmtRole->execute();
    $roleRow = $stmtRole->fetch();

    if ($roleRow === false) {
        signupJsonResponse(500, [
            'status' => 'error',
            'message' => 'Role Oracle introuvable.',
        ]);
    }

    $roleId = (int) $roleRow['ID_ROLE'];
    $userId = signupNextId($pdo, 'UTILISATEURS', 'ID_UTIL');
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Insertion du nouvel utilisateur
    |--------------------------------------------------------------------------
    | La requete utilise prepare + bindParam pour eviter toute injection SQL.
    |--------------------------------------------------------------------------
    */
    $sqlUser = '
        INSERT INTO UTILISATEURS (
            ID_UTIL,
            ROLE_ID,
            PRENOM,
            NOM,
            EMAIL,
            MOT_DE_PASSE,
            NUM_TEL,
            ADRESSE,
            CREATED_AT,
            UPDATED_AT
        ) VALUES (
            :id_util,
            :role_id,
            :prenom,
            :nom,
            :email,
            :mot_de_passe,
            :num_tel,
            :adresse,
            CURRENT_TIMESTAMP,
            CURRENT_TIMESTAMP
        )
    ';
    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->bindParam(':id_util', $userId, PDO::PARAM_INT);
    $stmtUser->bindParam(':role_id', $roleId, PDO::PARAM_INT);
    $stmtUser->bindParam(':prenom', $firstName, PDO::PARAM_STR);
    $stmtUser->bindParam(':nom', $lastName, PDO::PARAM_STR);
    $stmtUser->bindParam(':email', $email, PDO::PARAM_STR);
    $stmtUser->bindParam(':mot_de_passe', $hashedPassword, PDO::PARAM_STR);
    $stmtUser->bindParam(':num_tel', $phone, PDO::PARAM_STR);
    $stmtUser->bindParam(':adresse', $address, PDO::PARAM_STR);
    $stmtUser->execute();

    /*
    |--------------------------------------------------------------------------
    | Creation des enregistrements metier relies au role
    |--------------------------------------------------------------------------
    | - commerce : table PROPRIETAIRES_COMMERCE
    | - admin_association : tables ORGANIZATIONS et ADMINS_ASSOCIATION
    |--------------------------------------------------------------------------
    */
    if ($frontendRole === 'commerce') {
        $commerceType = 'Commerce';
        $businessLicence = 'LIC-' . $userId . '-AUTO';

        $sqlCommerce = '
            INSERT INTO PROPRIETAIRES_COMMERCE (
                ID_COMMERCE,
                NOM_COMMERCE,
                TYPE_COMMERCE,
                BUSINESS_LICENCE,
                CREATED_AT
            ) VALUES (
                :id_commerce,
                :nom_commerce,
                :type_commerce,
                :business_licence,
                CURRENT_TIMESTAMP
            )
        ';
        $stmtCommerce = $pdo->prepare($sqlCommerce);
        $stmtCommerce->bindParam(':id_commerce', $userId, PDO::PARAM_INT);
        $stmtCommerce->bindParam(':nom_commerce', $organizationName, PDO::PARAM_STR);
        $stmtCommerce->bindParam(':type_commerce', $commerceType, PDO::PARAM_STR);
        $stmtCommerce->bindParam(':business_licence', $businessLicence, PDO::PARAM_STR);
        $stmtCommerce->execute();
    }

    if ($frontendRole === 'admin_association') {
        $organizationId = signupNextId($pdo, 'ORGANIZATIONS', 'ID');
        $organizationType = 'association';

        $sqlOrganization = '
            INSERT INTO ORGANIZATIONS (
                ID,
                NAME,
                ORGANIZATION_TYPE,
                CREATED_AT
            ) VALUES (
                :id_organisation,
                :nom_organisation,
                :type_organisation,
                CURRENT_TIMESTAMP
            )
        ';
        $stmtOrganization = $pdo->prepare($sqlOrganization);
        $stmtOrganization->bindParam(':id_organisation', $organizationId, PDO::PARAM_INT);
        $stmtOrganization->bindParam(':nom_organisation', $organizationName, PDO::PARAM_STR);
        $stmtOrganization->bindParam(':type_organisation', $organizationType, PDO::PARAM_STR);
        $stmtOrganization->execute();

        $sqlAssociationAdmin = '
            INSERT INTO ADMINS_ASSOCIATION (
                ID_ADMIN_ASSOCIATION,
                ORGANIZATION_ID,
                NOM_ASSOCIATION,
                CREATED_AT
            ) VALUES (
                :id_admin_association,
                :organization_id,
                :nom_association,
                CURRENT_TIMESTAMP
            )
        ';
        $stmtAssociationAdmin = $pdo->prepare($sqlAssociationAdmin);
        $stmtAssociationAdmin->bindParam(':id_admin_association', $userId, PDO::PARAM_INT);
        $stmtAssociationAdmin->bindParam(':organization_id', $organizationId, PDO::PARAM_INT);
        $stmtAssociationAdmin->bindParam(':nom_association', $organizationName, PDO::PARAM_STR);
        $stmtAssociationAdmin->execute();
    }

    $pdo->commit();

    signupJsonResponse(201, [
        'status' => 'success',
        'message' => 'Inscription reussie.',
        'role' => $frontendRole,
        'user_id' => $userId,
    ]);
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    signupJsonResponse(500, [
        'status' => 'error',
        'message' => 'Erreur lors de l inscription.',
    ]);
}
