<?php
declare(strict_types=1);

namespace App\Repository\Json;

use App\Repository\IslemYoneticisiInterface;
use App\Support\SaatInterface;

final class JsonIslemYoneticisi implements IslemYoneticisiInterface
{
    public function __construct(private string $klasor, private SaatInterface $saat)
    {
    }

    public function oku(callable $islem): mixed
    {
        return $this->kilitle($islem, false);
    }

    public function calistir(callable $islem): mixed
    {
        return $this->kilitle($islem, true);
    }

    private function kilitle(callable $islem, bool $yaz): mixed
    {
        $yol = $this->klasor . '/veri.json';
        // Kilit ayrı dosyadadır. Veri dosyası yenilense de tüm istekler aynı kilidi kullanır.
        $kilit = fopen($this->klasor . '/veri.lock', 'c+b');
        if ($kilit === false) {
            throw new \RuntimeException('Veri kilidi açılamadı.');
        }
        try {
            if (!flock($kilit, $yaz ? LOCK_EX : LOCK_SH)) {
                throw new \RuntimeException('Veri kilitlenemedi.');
            }
            if (!is_file($yol)) {
                if (!flock($kilit, LOCK_EX)) {
                    throw new \RuntimeException('Başlangıç verisi için kilit alınamadı.');
                }
                clearstatcache(true, $yol);
                if (!is_file($yol)) {
                    $this->yaz($yol, $this->baslangic());
                }
            }
            $depo = new JsonKayitDeposu($this->jsonOku($yol));
            $sonuc = $islem($depo);
            if ($yaz && $depo->degisti()) {
                $this->yaz($yol, $depo->veri());
            }
            return $sonuc;
        } finally {
            fclose($kilit);
        }
    }

    /** @return array<string, mixed> */
    private function jsonOku(string $yol): array
    {
        $icerik = file_get_contents($yol);
        if ($icerik === false) {
            throw new \RuntimeException('JSON verisi okunamadı.');
        }
        $veri = json_decode($icerik, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($veri) || ($veri['surum'] ?? null) !== 1) {
            throw new \RuntimeException('JSON veri biçimi geçersiz.');
        }
        return $veri;
    }

    /** @return array<string, mixed> */
    private function baslangic(): array
    {
        $veri = $this->jsonOku($this->klasor . '/ornek-veriler.json');
        $simdi = $this->saat->simdi();
        $bugun = (new \DateTimeImmutable('@' . $simdi))
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()))->setTime(0, 0);
        foreach ($veri['seferler'] as &$sefer) {
            [$saat, $dakika] = explode(':', $sefer['saat']);
            $sefer['kalkis'] = $bugun->modify('+' . $sefer['gun'] . ' days')
                ->setTime((int) $saat, (int) $dakika)->getTimestamp();
            unset($sefer['gun'], $sefer['saat']);
        }
        unset($sefer);
        foreach ($veri['siparisler'] as &$siparis) {
            $siparis['zaman'] = $simdi;
        }
        unset($siparis);
        foreach ($veri as $tablo => $kayitlar) {
            if (is_array($kayitlar)) {
                $veri['son_id'][$tablo] = max([0, ...array_column($kayitlar, 'id')]);
            }
        }
        return $veri;
    }

    /** @param array<string, mixed> $veri */
    private function yaz(string $yol, array $veri): void
    {
        $json = json_encode($veri, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        $gecici = tempnam($this->klasor, 'yaz-');
        if ($gecici === false) {
            throw new \RuntimeException('Geçici veri dosyası oluşturulamadı.');
        }
        try {
            if (file_put_contents($gecici, $json) !== strlen($json) || !rename($gecici, $yol)) {
                throw new \RuntimeException('JSON işlemi kaydedilemedi.');
            }
        } finally {
            if (is_file($gecici)) {
                unlink($gecici);
            }
        }
    }
}
