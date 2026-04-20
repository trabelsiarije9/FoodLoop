<?php
declare(strict_types=1);

namespace App\Support;

final class PlatformClock
{
    public static function referenceNow(): string
    {
        return '2026-04-20 14:30:00';
    }

    public static function displayReferenceNow(): string
    {
        return '20/04/2026 14:30';
    }
}
