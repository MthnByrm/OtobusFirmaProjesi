<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\IslemYoneticisiInterface;
use App\Repository\KayitDeposuInterface;
use App\Support\SaatInterface;
use App\Support\UygulamaHatasi;

final class SeferAramaService
{
    public function __construct(private IslemYoneticisiInterface $islemler, private SaatInterface $saat)
    {
    }

    public function form(): array
    {
        return $this->islemler->oku(function (KayitDeposuInterface $depo): array {
            $sehirler = [];
            foreach ($depo->tumu('guzergahlar') as $guzergah) {
                foreach ($guzergah['duraklar'] as $durak) {
                    $sehirler[$durak['sehir']] = $durak['sehir'];
                }
            }
            natcasesort($sehirler);
            $simdi = $this->saat->simdi();
            $gun = null;
            foreach ($depo->tumu('seferler') as $sefer) {
                if ($sefer['kalkis'] >= $simdi && ($gun === null || $sefer['kalkis'] < $gun)) {
                    $gun = $sefer['kalkis'];
                }
            }
            return ['sehirler' => $sehirler, 'bugun' => date('Y-m-d', $simdi), 'tarih' => date('Y-m-d', $gun ?? $simdi)];
        });
    }

    public function ara(string $nereden, string $nereye, \DateTimeImmutable $tarih): array
    {
        if ($nereden === '' || $nereye === '' || $nereden === $nereye) {
            throw new UygulamaHatasi('Birbirinden farklı iki şehir seçin.');
        }
        return $this->islemler->oku(function (KayitDeposuInterface $depo) use ($nereden, $nereye, $tarih): array {
            $sonuclar = [];
            $baslangic = $tarih->getTimestamp();
            $bitis = $tarih->modify('+1 day')->getTimestamp();
            $simdi = $this->saat->simdi();
            foreach ($depo->tumu('seferler') as $sefer) {
                $guzergah = $depo->bul('guzergahlar', $sefer['guzergah_id']);
                $sehirler = array_column($guzergah['duraklar'], 'sehir');
                $binis = array_search($nereden, $sehirler, true);
                $inis = array_search($nereye, $sehirler, true);
                if ($binis === false || $inis === false || $binis >= $inis) {
                    continue;
                }
                $etap = self::etap($depo, $sefer['id'], $binis, $inis);
                if ($etap['kalkis'] > $simdi && $etap['kalkis'] >= $baslangic && $etap['kalkis'] < $bitis) {
                    $sonuclar[] = $etap;
                }
            }
            usort($sonuclar, fn (array $a, array $b): int => $a['kalkis'] <=> $b['kalkis']);
            return $sonuclar;
        });
    }

    public static function etap(KayitDeposuInterface $depo, int $seferId, int $binis, int $inis): array
    {
        $sefer = $depo->bul('seferler', $seferId);
        if ($sefer === null) {
            throw new UygulamaHatasi('Sefer bulunamadı.', 404);
        }
        $guzergah = $depo->bul('guzergahlar', $sefer['guzergah_id']);
        $otobus = $depo->bul('otobusler', $sefer['otobus_id']);
        $duraklar = $guzergah['duraklar'];
        if ($binis < 0 || $inis <= $binis || !isset($duraklar[$inis])) {
            throw new UygulamaHatasi('Biniş ve iniş duraklarının sırası geçersiz.');
        }
        return [
            'sefer_id' => $seferId,
            'binis' => $binis,
            'inis' => $inis,
            'nereden' => $duraklar[$binis]['sehir'],
            'nereye' => $duraklar[$inis]['sehir'],
            'kalkis' => $sefer['kalkis'] + $duraklar[$binis]['dakika'] * 60,
            'varis' => $sefer['kalkis'] + $duraklar[$inis]['dakika'] * 60,
            'ucret' => $duraklar[$inis]['ucret'] - $duraklar[$binis]['ucret'],
            'duzen' => $otobus['duzen'],
            'kapasite' => $otobus['sira'] * KoltukPlani::siradakiKoltuk($otobus['duzen']),
            'duraklar' => array_slice($duraklar, $binis, $inis - $binis + 1),
        ];
    }
}
