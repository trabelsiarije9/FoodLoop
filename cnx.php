<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Connexion PDO Oracle reutilisable
|--------------------------------------------------------------------------
| Ce fichier ne contient QUE la connexion a la base Oracle.
| Il est inclus par les autres fichiers PHP du projet.
| Aucune logique metier ne doit etre ajoutee ici.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Parametres de connexion
|--------------------------------------------------------------------------
| Adaptez ces valeurs a votre environnement Oracle si necessaire.
| Exemple de DSN Oracle via PDO_OCI :
| oci:dbname=//hote:port/service_name;charset=AL32UTF8
|--------------------------------------------------------------------------
*/
$oracleHost = 'localhost';
$oraclePort = '1521';
$oracleService = 'XE';
$oracleUsername = 'FOODLOOP_USER';
$oraclePassword = 'password';
$oracleCharset = 'AL32UTF8';

/*
|--------------------------------------------------------------------------
| Construction du DSN Oracle
|--------------------------------------------------------------------------
| Le DSN permet a PDO de savoir a quel serveur Oracle se connecter.
|--------------------------------------------------------------------------
*/
$oracleDsn = sprintf(
    'oci:dbname=//%s:%s/%s;charset=%s',
    $oracleHost,
    $oraclePort,
    $oracleService,
    $oracleCharset
);

/*
|--------------------------------------------------------------------------
| Creation de la connexion PDO
|--------------------------------------------------------------------------
| - ERRMODE_EXCEPTION : permet de gerer les erreurs via try/catch
| - DEFAULT_FETCH_MODE : les resultats SQL seront recuperes en tableau associatif
|--------------------------------------------------------------------------
*/
try {
    $pdo = new PDO(
        $oracleDsn,
        $oracleUsername,
        $oraclePassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $exception) {
    /*
    |--------------------------------------------------------------------------
    | Gestion d'erreur securisee
    |--------------------------------------------------------------------------
    | On ne retourne pas les details techniques a l'utilisateur final.
    | Les autres scripts pourront intercepter ce message generique.
    |--------------------------------------------------------------------------
    */
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        [
            'status' => 'error',
            'message' => 'Connexion a la base Oracle impossible.',
        ],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}
