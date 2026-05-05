<?php
declare(strict_types=1);

namespace App\Controllers;

use RuntimeException;

final class PageController
{
    public function render(string $view): never
    {
        $file = BASE_PATH . '/app/Views/pages/' . $view . '.php';

        if (!is_file($file)) {
            throw new RuntimeException('Vue introuvable.');
        }

        require $file;
        exit;
    }
}
