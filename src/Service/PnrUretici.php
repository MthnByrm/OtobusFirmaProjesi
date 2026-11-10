<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\KayitDeposuInterface;

final class PnrUretici
{
    public static function uret(KayitDeposuInterface $depo): string
    {
        $pnrler = array_column($depo->tumu('biletler'), 'pnr');
        do {
            $pnr = strtoupper(bin2hex(random_bytes(5)));
        } while (in_array($pnr, $pnrler, true));
        return $pnr;
    }
}
