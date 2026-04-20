<?php
declare(strict_types=1);

namespace App\Views;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        $templatePath = __DIR__ . '/templates/' . $template . '.php';

        if (!is_file($templatePath)) {
            throw new \RuntimeException('Template not found: ' . $template);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $templatePath;

        return (string) ob_get_clean();
    }
}
