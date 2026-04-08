<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = []): string
    {
        return View::render($view, $data);
    }

    protected function requireCsrf(): void
    {
        if (!verify_csrf($_POST['_csrf'] ?? null)) {
            flash('error', 'Session invalide. Reessayez le formulaire.');
            redirect('home');
        }
    }
}
