<?php
declare(strict_types=1);

use App\Support\{Girdi, Oturum};

$router = require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/views/yardimcilar.php';

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'");
try {
    Oturum::baslat();
} catch (\RuntimeException) {
    http_response_code(500);
    echo 'Oturum başlatılamadı. Projeyi ZIP içinden çıkarın ve storage klasörünün yazılabilir olduğunu kontrol edin.';
    return;
}
$yol = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$metot = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($yol, ['/', '/index.php'], true)) {
    $router->calistir('', $metot, []);
    return;
}
$router->calistir(Girdi::metin($_GET['sayfa'] ?? 'anasayfa'), $metot, $metot === 'POST' ? $_POST : $_GET);
