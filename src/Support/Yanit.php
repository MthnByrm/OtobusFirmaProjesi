<?php
declare(strict_types=1);

namespace App\Support;

final class Yanit
{
    public static function gorunum(string $sayfa, string $baslik, array $veri = []): void
    {
        $dosya = dirname(__DIR__, 2) . '/views/' . $sayfa . '.php';
        $kullanici = Oturum::kullanici();
        $mesaj = Oturum::mesajiAl();
        extract($veri, EXTR_SKIP);
        require dirname(__DIR__, 2) . '/views/duzen.php';
    }

    public static function yonlendir(string $sayfa, array $parametreler = []): void
    {
        header('Location: ' . self::url($sayfa, $parametreler), true, 303);
    }

    public static function url(string $sayfa, array $parametreler = []): string
    {
        return '?' . http_build_query(['sayfa' => $sayfa] + $parametreler, '', '&', PHP_QUERY_RFC3986);
    }

    public static function json(array $veri): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($veri, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
