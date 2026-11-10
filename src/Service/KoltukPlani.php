<?php
declare(strict_types=1);

namespace App\Service;

final class KoltukPlani
{
    public static function siradakiKoltuk(string $duzen): int
    {
        return $duzen === '2+1' ? 3 : 4;
    }

    public static function komsu(string $duzen, int $koltuk): ?int
    {
        return match (($koltuk - 1) % self::siradakiKoltuk($duzen)) {
            0 => $koltuk + 1,
            1, 3 => $koltuk - 1,
            2 => $duzen === '2+1' ? null : $koltuk + 1,
        };
    }
}
