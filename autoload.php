<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Istanbul');
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    // Teslim ZIP'i Composer kurulumu olmadan da çalışır.
    spl_autoload_register(static function (string $sinif): void {
        if (str_starts_with($sinif, 'App\\')) {
            $yol = __DIR__ . '/src/' . str_replace('\\', '/', substr($sinif, 4)) . '.php';
            if (is_file($yol)) {
                require $yol;
            }
        }
    });
}
