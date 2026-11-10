<?php
declare(strict_types=1);

namespace App\Service;

final class EtapCakisma
{
    public static function varMi(int $ilkBinis, int $ilkInis, int $ikinciBinis, int $ikinciInis): bool
    {
        return $ilkBinis < $ikinciInis && $ikinciBinis < $ilkInis;
    }
}
