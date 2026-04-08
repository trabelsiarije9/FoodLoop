<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): string
    {
        $path = __DIR__ . '/../Views/' . $view . '.php';

        if (!is_file($path)) {
            throw new \RuntimeException('Vue introuvable : ' . $view);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $path;
        return (string) ob_get_clean();
    }
}
