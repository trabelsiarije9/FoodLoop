<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

use App\Controllers\HomeController;

try {
    $controller = new HomeController();
    echo $controller->index();
} catch (Throwable $exception) {
    http_response_code(500);
    $message = htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');

    echo <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>FoodLoop | Erreur</title>
            <style>
                body { font-family: "Trebuchet MS", sans-serif; margin: 0; background: #f8f3eb; color: #1d2a23; }
                main { width: min(760px, calc(100% - 2rem)); margin: 5rem auto; padding: 2rem; background: #fff9f0; border-radius: 24px; border: 1px solid #e4d8c2; }
                h1 { margin-top: 0; font-family: Georgia, serif; }
                code { display: block; padding: 1rem; background: #f3ebdd; border-radius: 16px; }
            </style>
        </head>
        <body>
            <main>
                <h1>Le projet ne peut pas demarrer pour le moment.</h1>
                <p>La structure a ete convertie vers une application basee sur PDO + SQLite. Verifiez que l extension <strong>pdo_sqlite</strong> est disponible dans votre installation PHP.</p>
                <code>{$message}</code>
            </main>
        </body>
        </html>
    HTML;
}
