<?php
declare(strict_types=1);

namespace App\Support;

final class Girdi
{
    public static function metin(mixed $deger): string
    {
        return is_string($deger) ? trim($deger) : '';
    }

    public static function sayi(mixed $deger, int $enAz = 1, int $enCok = PHP_INT_MAX): int
    {
        $sayi = is_int($deger) || is_string($deger)
            ? filter_var($deger, FILTER_VALIDATE_INT, ['options' => ['min_range' => $enAz, 'max_range' => $enCok]])
            : false;
        if ($sayi === false) {
            throw new UygulamaHatasi('Geçersiz veya eksik sayısal bilgi.');
        }
        return $sayi;
    }

    public static function tarih(mixed $deger, string $bicim = 'Y-m-d'): \DateTimeImmutable
    {
        $metin = str_replace("\0", " ", self::metin($deger));
        $tarih = \DateTimeImmutable::createFromFormat('!' . $bicim, $metin);
        if ($tarih === false || $tarih->format($bicim) !== $metin) {
            throw new UygulamaHatasi('Geçerli bir tarih ve gerekiyorsa saat girin.');
        }
        return $tarih;
    }
}
