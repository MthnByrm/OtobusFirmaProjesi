<?php
declare(strict_types=1);

namespace App\Support;

final class Oturum
{
    public static function baslat(): void
    {
        $klasor = dirname(__DIR__, 2) . '/storage/oturumlar';
        if (!is_dir($klasor) && !@mkdir($klasor, 0700) && !is_dir($klasor)) {
            throw new \RuntimeException('Oturum klasörü oluşturulamadı.');
        }
        $basladi = session_start([
            'save_path' => $klasor,
            'use_strict_mode' => 1, 'cookie_httponly' => true, 'cookie_samesite' => 'Lax',
            'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        if (!$basladi) {
            throw new \RuntimeException('Oturum başlatılamadı.');
        }
    }

    public static function kullanici(): ?array
    {
        return $_SESSION['kullanici'] ?? null;
    }

    public static function kimlik(): int
    {
        return self::kullanici()['id'] ?? 0;
    }

    public static function oturumAc(array $kullanici): void
    {
        session_regenerate_id(true);
        $_SESSION = ['kullanici' => $kullanici];
    }

    public static function oturumKapat(): void
    {
        session_regenerate_id(true);
        $_SESSION = [];
    }

    public static function csrf(): string
    {
        return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    }

    public static function mesaj(string $mesaj): void
    {
        $_SESSION['mesaj'] = $mesaj;
    }

    public static function mesajiAl(): ?string
    {
        $mesaj = $_SESSION['mesaj'] ?? null;
        unset($_SESSION['mesaj']);
        return $mesaj;
    }

    public static function biletErisimiVer(int $id): void
    {
        $_SESSION['bilet_erisim'][$id] = time() + 900;
    }

    public static function biletErisimiVar(int $id): bool
    {
        return ($_SESSION['bilet_erisim'][$id] ?? 0) > time();
    }
}
