<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\IslemYoneticisiInterface;
use App\Repository\KayitDeposuInterface;

final class RaporService
{
    public function __construct(private IslemYoneticisiInterface $islemler)
    {
    }

    public function rapor(): array
    {
        return $this->islemler->oku(function (KayitDeposuInterface $depo): array {
            $biletler = [];
            foreach ($depo->tumu('biletler') as $bilet) {
                $biletler[$bilet['sefer_id']][] = $bilet;
            }
            $satirlar = [];
            $toplam = ['bilet' => 0, 'brut' => 0, 'iade' => 0, 'net' => 0];
            foreach ($depo->tumu('seferler') as $sefer) {
                $rota = $depo->bul('guzergahlar', $sefer['guzergah_id']);
                $sonDurak = count($rota['duraklar']) - 1;
                $etap = SeferAramaService::etap($depo, $sefer['id'], 0, $sonDurak);
                $sayac = array_fill(0, $sonDurak, 0);
                $biletSayisi = $brut = $iade = 0;
                foreach ($biletler[$sefer['id']] ?? [] as $bilet) {
                    $brut += $bilet['ucret'];
                    $iade += $bilet['iade'];
                    if ($bilet['durum'] === 'aktif') {
                        $biletSayisi++;
                        for ($i = $bilet['binis']; $i < $bilet['inis']; $i++) {
                            $sayac[$i]++;
                        }
                    }
                }
                $sayilar = ['bilet' => $biletSayisi, 'brut' => $brut, 'iade' => $iade, 'net' => $brut - $iade];
                foreach ($sayilar as $alan => $deger) {
                    $toplam[$alan] += $deger;
                }
                $satirlar[] = $sayilar + [
                    'etap' => $etap, 'dolu' => max($sayac),
                    'oran' => intdiv(max($sayac) * 100 + intdiv($etap['kapasite'], 2), $etap['kapasite']),
                ];
            }
            usort($satirlar, fn (array $a, array $b): int => $a['etap']['kalkis'] <=> $b['etap']['kalkis']);
            return compact('satirlar', 'toplam');
        });
    }
}
