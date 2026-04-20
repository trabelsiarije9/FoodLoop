<?php
declare(strict_types=1);

namespace App\Support;

final class PlatformClock
{
    public static function referenceNow(): string
    {
        return '2026-04-15 14:30:00';
    }

    public static function displayReferenceNow(): string
    {
        return '15/04/2026 14:30';
    }
}
