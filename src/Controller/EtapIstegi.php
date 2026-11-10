<?php
declare(strict_types=1);

namespace App\Controller;

use App\Support\Girdi;

final class EtapIstegi
{
    /** @return array{sefer: int, binis: int, inis: int} */
    public static function oku(array $girdi): array
    {
        return [
            'sefer' => Girdi::sayi($girdi['sefer'] ?? null),
            'binis' => Girdi::sayi($girdi['binis'] ?? null, 0),
            'inis' => Girdi::sayi($girdi['inis'] ?? null),
        ];
    }
}
