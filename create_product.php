<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/cnx.php';

function productJsonResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function productPostValue(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function productNextId(PDO $pdo, string $table, string $column): int
{
    $sql = "SELECT NVL(MAX($column), 0) + 1 AS NEXT_ID FROM $table";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();

    return (int) ($row['NEXT_ID'] ?? 1);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    productJsonResponse(405, [
        'status' => 'error',
        'message' => 'Methode non autorisee.',
    ]);
}

/*
|--------------------------------------------------------------------------
| Controle d'acces
|--------------------------------------------------------------------------
| Seul un utilisateur connecte avec le role commerce peut publier un produit.
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['foodloop_user_id'], $_SESSION['foodloop_role'])) {
    productJsonResponse(401, [
        'status' => 'error',
        'message' => 'Utilisateur non connecte.',
    ]);
}

if ($_SESSION['foodloop_role'] !== 'commerce') {
    productJsonResponse(403, [
        'status' => 'error',
        'message' => 'Acces reserve aux commerces.',
    ]);
}

/*
|--------------------------------------------------------------------------
| Recuperation des donnees du produit
|--------------------------------------------------------------------------
| Le sujet impose :
| - titre
| - description
| - type
| - quantite
| - localisation
| - prix
| - pickup_time
|
| La base Oracle export.sql stocke les produits dans la table ANNONCES.
| Cette table ne contient pas de colonne PRIX.
| Le backend accepte donc "prix", le valide, mais ne l'insere pas en base
| faute de colonne dediee dans le schema Oracle existant.
|--------------------------------------------------------------------------
*/
$titre = productPostValue('titre');
$description = productPostValue('description');
$type = productPostValue('type');
$quantite = (int) ($_POST['quantite'] ?? 0);
$localisation = productPostValue('localisation');
$prix = (float) ($_POST['prix'] ?? 0);
$pickupTime = productPostValue('pickup_time');
$categorieId = (int) ($_POST['categorie_id'] ?? 1);
$zoneId = (int) ($_POST['zone_id'] ?? 0);

if ($titre === '' || $description === '' || $type === '' || $localisation === '' || $pickupTime === '') {
    productJsonResponse(422, [
        'status' => 'error',
        'message' => 'Tous les champs du produit sont obligatoires.',
    ]);
}

if ($quantite <= 0) {
    productJsonResponse(422, [
        'status' => 'error',
        'message' => 'La quantite doit etre strictement positive.',
    ]);
}

if ($prix < 0) {
    productJsonResponse(422, [
        'status' => 'error',
        'message' => 'Le prix ne peut pas etre negatif.',
    ]);
}

$pickupTimestamp = strtotime($pickupTime);

if ($pickupTimestamp === false) {
    productJsonResponse(422, [
        'status' => 'error',
        'message' => 'Date de pickup invalide.',
    ]);
}

/*
|--------------------------------------------------------------------------
| Construction des dates obligatoires pour Oracle
|--------------------------------------------------------------------------
| La table ANNONCES demande plusieurs dates non nulles.
| On derive des valeurs coherentes a partir de pickup_time.
|--------------------------------------------------------------------------
*/
$publicVisibilityAt = date('Y-m-d H:i:s');
$pickupStart = date('Y-m-d H:i:s', $pickupTimestamp);
$pickupEnd = date('Y-m-d H:i:s', strtotime('+1 hour', $pickupTimestamp));
$expirationDate = date('Y-m-d H:i:s', strtotime('+2 hours', $pickupTimestamp));
$status = 'available';
$unit = 'unite';
$commerceId = (int) $_SESSION['foodloop_user_id'];

try {
    /*
    |--------------------------------------------------------------------------
    | Verification du profil commerce
    |--------------------------------------------------------------------------
    | La cle etrangere PROPRIETAIRE_ID pointe vers PROPRIETAIRES_COMMERCE.
    |--------------------------------------------------------------------------
    */
    $sqlCommerce = 'SELECT COUNT(*) AS TOTAL FROM PROPRIETAIRES_COMMERCE WHERE ID_COMMERCE = :id_commerce';
    $stmtCommerce = $pdo->prepare($sqlCommerce);
    $stmtCommerce->bindParam(':id_commerce', $commerceId, PDO::PARAM_INT);
    $stmtCommerce->execute();
    $commerceRow = $stmtCommerce->fetch();

    if ((int) ($commerceRow['TOTAL'] ?? 0) === 0) {
        productJsonResponse(403, [
            'status' => 'error',
            'message' => 'Profil commerce introuvable dans la base Oracle.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Recherche d'une zone valide
    |--------------------------------------------------------------------------
    | ZONE_ID est obligatoire en base.
    | Si le frontend n'envoie rien, on prend la premiere zone disponible.
    |--------------------------------------------------------------------------
    */
    if ($zoneId <= 0) {
        $sqlZone = 'SELECT MIN(ID_ZONE) AS ID_ZONE FROM ZONES_GEOGRAPHIQUES';
        $stmtZone = $pdo->prepare($sqlZone);
        $stmtZone->execute();
        $zoneRow = $stmtZone->fetch();
        $zoneId = (int) ($zoneRow['ID_ZONE'] ?? 0);
    }

    if ($zoneId <= 0) {
        productJsonResponse(500, [
            'status' => 'error',
            'message' => 'Aucune zone geographique disponible.',
        ]);
    }

    $annonceId = productNextId($pdo, 'ANNONCES', 'ID_ANNONCE');

    /*
    |--------------------------------------------------------------------------
    | Insertion du produit dans ANNONCES
    |--------------------------------------------------------------------------
    | "create_product.php" alimente la table ANNONCES du schema Oracle existant.
    |--------------------------------------------------------------------------
    */
    $sqlInsert = '
        INSERT INTO ANNONCES (
            ID_ANNONCE,
            TITRE,
            DESCRIPTION,
            TYPE_ALIMENT,
            QUANTITE,
            UNITE,
            LOCALISATION,
            STATUT,
            PUBLIC_VISIBILITY_AT,
            PICKUP_START,
            PICKUP_END,
            DATE_EXPIRATION,
            CATEGORIE_ID,
            ZONE_ID,
            PROPRIETAIRE_ID,
            CREATED_AT,
            UPDATED_AT
        ) VALUES (
            :id_annonce,
            :titre,
            :description,
            :type_aliment,
            :quantite,
            :unite,
            :localisation,
            :statut,
            TO_TIMESTAMP(:public_visibility_at, ''YYYY-MM-DD HH24:MI:SS''),
            TO_TIMESTAMP(:pickup_start, ''YYYY-MM-DD HH24:MI:SS''),
            TO_TIMESTAMP(:pickup_end, ''YYYY-MM-DD HH24:MI:SS''),
            TO_TIMESTAMP(:date_expiration, ''YYYY-MM-DD HH24:MI:SS''),
            :categorie_id,
            :zone_id,
            :proprietaire_id,
            CURRENT_TIMESTAMP,
            CURRENT_TIMESTAMP
        )
    ';
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->bindParam(':id_annonce', $annonceId, PDO::PARAM_INT);
    $stmtInsert->bindParam(':titre', $titre, PDO::PARAM_STR);
    $stmtInsert->bindParam(':description', $description, PDO::PARAM_STR);
    $stmtInsert->bindParam(':type_aliment', $type, PDO::PARAM_STR);
    $stmtInsert->bindParam(':quantite', $quantite, PDO::PARAM_INT);
    $stmtInsert->bindParam(':unite', $unit, PDO::PARAM_STR);
    $stmtInsert->bindParam(':localisation', $localisation, PDO::PARAM_STR);
    $stmtInsert->bindParam(':statut', $status, PDO::PARAM_STR);
    $stmtInsert->bindParam(':public_visibility_at', $publicVisibilityAt, PDO::PARAM_STR);
    $stmtInsert->bindParam(':pickup_start', $pickupStart, PDO::PARAM_STR);
    $stmtInsert->bindParam(':pickup_end', $pickupEnd, PDO::PARAM_STR);
    $stmtInsert->bindParam(':date_expiration', $expirationDate, PDO::PARAM_STR);
    $stmtInsert->bindParam(':categorie_id', $categorieId, PDO::PARAM_INT);
    $stmtInsert->bindParam(':zone_id', $zoneId, PDO::PARAM_INT);
    $stmtInsert->bindParam(':proprietaire_id', $commerceId, PDO::PARAM_INT);
    $stmtInsert->execute();

    productJsonResponse(201, [
        'status' => 'success',
        'message' => 'Produit cree avec succes.',
        'product_id' => $annonceId,
        'prix_recu' => $prix,
        'note' => 'Le schema Oracle export.sql ne contient pas de colonne prix dans ANNONCES.',
    ]);
} catch (PDOException $exception) {
    productJsonResponse(500, [
        'status' => 'error',
        'message' => 'Erreur lors de la creation du produit.',
    ]);
}
