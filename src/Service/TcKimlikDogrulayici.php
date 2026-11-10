<?php
declare(strict_types=1);

namespace App\Service;

final class TcKimlikDogrulayici
{
    public static function gecerli(string $tc): bool
    {
        if (!preg_match('/^[1-9][0-9]{10}$/D', $tc)) {
            return false;
        }
        $rakamlar = array_map('intval', str_split($tc));
        [$a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k] = $rakamlar;
        $onuncu = ((7 * ($a + $c + $e + $g + $i) - ($b + $d + $f + $h)) % 10 + 10) % 10;
        return $onuncu === $j && (array_sum($rakamlar) - $k) % 10 === $k;
    }
}
