<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\IslemYoneticisiInterface;
use App\Repository\KayitDeposuInterface;
use App\Support\SaatInterface;
use App\Support\UygulamaHatasi;

final class KoltukKilitService
{
    public const EN_COK = 5;
    public const SURE = 600;

    public function __construct(private IslemYoneticisiInterface $islemler, private SaatInterface $saat)
    {
    }

    public function durum(int $kullanici, int $sefer, int $binis, int $inis): array
    {
        return $this->islemler->calistir(function (KayitDeposuInterface $depo) use ($kullanici, $sefer, $binis, $inis): array {
            $simdi = $this->saat->simdi();
            $etap = SeferAramaService::etap($depo, $sefer, $binis, $inis);
            $harita = [];
            $secimler = [];
            foreach (self::aktifler($depo, $etap, $simdi) as $kayit) {
                $durum = isset($kayit['bitis']) ? 'kilitli' : 'dolu';
                if ($durum === 'kilitli' && self::kendiKilidi($kayit, $kullanici, $etap)) {
                    $durum = 'secili';
                    $secimler[] = ['koltuk' => $kayit['koltuk'], 'cinsiyet' => $kayit['cinsiyet'], 'bitis' => $kayit['bitis']];
                }
                $harita[$kayit['koltuk']] = $durum;
            }
            usort($secimler, fn (array $a, array $b): int => $a['koltuk'] <=> $b['koltuk']);
            return ['etap' => $etap, 'harita' => $harita, 'secimler' => $secimler, 'simdi' => $simdi];
        });
    }

    public function kilitle(int $kullanici, int $sefer, int $binis, int $inis, int $koltuk, string $cinsiyet): void
    {
        $this->islemler->calistir(function (KayitDeposuInterface $depo) use (
            $kullanici, $sefer, $binis, $inis, $koltuk, $cinsiyet
        ): void {
            $simdi = $this->saat->simdi();
            $etap = SeferAramaService::etap($depo, $sefer, $binis, $inis);
            if ($etap['kalkis'] <= $simdi || $koltuk < 1 || $koltuk > $etap['kapasite']
                || !in_array($cinsiyet, ['K', 'E'], true)) {
                throw new UygulamaHatasi('Koltuk, yolcu cinsiyeti veya kalkış zamanı geçersiz.');
            }
            $aktifler = self::aktifler($depo, $etap, $simdi);
            $komsu = KoltukPlani::komsu($etap['duzen'], $koltuk);
            $secimSayisi = 0;
            foreach ($aktifler as $kayit) {
                if (self::kendiKilidi($kayit, $kullanici, $etap)) {
                    $secimSayisi++;
                    if ($kayit['koltuk'] === $koltuk && $kayit['cinsiyet'] === $cinsiyet) {
                        return;
                    }
                }
                if ($kayit['koltuk'] === $koltuk) {
                    throw new UygulamaHatasi('Bu koltuk seçilen durak aralığında dolu veya kilitli.', 409);
                }
                if ($kayit['koltuk'] === $komsu && $kayit['cinsiyet'] !== $cinsiyet) {
                    throw new UygulamaHatasi('Yan yana iki koltukta aynı etap için farklı cinsiyetten yolcu seçilemez.', 409);
                }
            }
            if ($secimSayisi >= self::EN_COK) {
                throw new UygulamaHatasi('Bir işlemde en fazla 5 koltuk seçebilirsiniz.');
            }
            $depo->ekle('kilitler', [
                'sefer_id' => $sefer, 'kullanici_id' => $kullanici, 'koltuk' => $koltuk,
                'binis' => $binis, 'inis' => $inis, 'cinsiyet' => $cinsiyet, 'bitis' => min($simdi + self::SURE, $etap['kalkis']),
            ]);
        });
    }

    public function birak(int $kullanici, int $sefer, int $binis, int $inis, int $koltuk): void
    {
        $this->islemler->calistir(function (KayitDeposuInterface $depo) use ($kullanici, $sefer, $binis, $inis, $koltuk): void {
            $etap = SeferAramaService::etap($depo, $sefer, $binis, $inis);
            foreach (self::aktifler($depo, $etap, $this->saat->simdi()) as $kayit) {
                if ($kayit['koltuk'] === $koltuk && self::kendiKilidi($kayit, $kullanici, $etap)) {
                    $depo->sil('kilitler', $kayit['id']);
                    return;
                }
            }
        });
    }

    public static function aktifler(KayitDeposuInterface $depo, array $etap, int $simdi): array
    {
        $aktifler = [];
        foreach ($depo->tumu('kilitler') as $kilit) {
            if ($kilit['sefer_id'] !== $etap['sefer_id']) {
                continue;
            }
            if ($kilit['bitis'] <= $simdi) {
                $depo->sil('kilitler', $kilit['id']);
            } elseif (self::kesisiyor($kilit, $etap)) {
                $aktifler[] = $kilit;
            }
        }
        foreach ($depo->tumu('biletler') as $bilet) {
            if ($bilet['sefer_id'] === $etap['sefer_id'] && $bilet['durum'] === 'aktif' && self::kesisiyor($bilet, $etap)) {
                $aktifler[] = $bilet;
            }
        }
        return $aktifler;
    }

    public static function kendiKilidi(array $kayit, int $kullanici, array $etap): bool
    {
        return isset($kayit['bitis']) && $kayit['kullanici_id'] === $kullanici
            && $kayit['binis'] === $etap['binis'] && $kayit['inis'] === $etap['inis'];
    }

    private static function kesisiyor(array $kayit, array $etap): bool
    {
        return EtapCakisma::varMi($kayit['binis'], $kayit['inis'], $etap['binis'], $etap['inis']);
    }
}
