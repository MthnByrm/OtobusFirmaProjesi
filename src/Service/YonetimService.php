<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\IslemYoneticisiInterface;
use App\Repository\KayitDeposuInterface;
use App\Support\Girdi;
use App\Support\SaatInterface;
use App\Support\UygulamaHatasi;

final class YonetimService
{
    public function __construct(private IslemYoneticisiInterface $islemler, private SaatInterface $saat)
    {
    }

    public function katalog(): array
    {
        return $this->islemler->oku(function (KayitDeposuInterface $depo): array {
            $guzergahlar = array_column($depo->tumu('guzergahlar'), null, 'id');
            $otobusler = array_column($depo->tumu('otobusler'), null, 'id');
            $seferler = array_column($depo->tumu('seferler'), null, 'id');
            uasort($seferler, fn (array $a, array $b): int => $a['kalkis'] <=> $b['kalkis']);
            return compact('guzergahlar', 'otobusler', 'seferler') + ['simdi' => $this->saat->simdi()];
        });
    }

    public function guzergah(int $id, string $ad, string $satirlar): void
    {
        if (strlen($ad) < 3 || strlen($ad) > 100 || strlen($satirlar) > 3000) {
            throw new UygulamaHatasi('Geçerli bir güzergâh adı ve durak listesi girin.');
        }
        $duraklar = [];
        $oncekiDakika = -1;
        $oncekiUcret = -1;
        foreach (preg_split('/\R/u', $satirlar) ?: [] as $satir) {
            if (trim($satir) === '') {
                continue;
            }
            $parcalar = array_map('trim', explode('|', $satir));
            if (count($parcalar) !== 3) {
                throw new UygulamaHatasi('Her durak satırını Şehir | Dakika | Ücret (TL) biçiminde yazın.');
            }
            [$sehir, $dakika, $ucret] = $parcalar;
            $dakika = Girdi::sayi($dakika, 0, 4320);
            if (!preg_match('/^[\p{L}][\p{L} .-]{1,39}$/u', $sehir)
                || !preg_match('/^([0-9]{1,5})(?:[.,]([0-9]{1,2}))?$/D', $ucret, $eslesme)) {
                throw new UygulamaHatasi('Durak adı veya TL tutarı geçersiz.');
            }
            $ucretKurus = (int) $eslesme[1] * 100 + (int) str_pad($eslesme[2] ?? '', 2, '0');
            if (in_array($sehir, array_column($duraklar, 'sehir'), true)
                || $dakika <= $oncekiDakika || $ucretKurus <= $oncekiUcret
                || ($duraklar === [] && ($dakika !== 0 || $ucretKurus !== 0))) {
                throw new UygulamaHatasi('İlk durak 0 dakika ve 0 TL olmalı. Sonraki duraklar farklı, süre ve ücret artan sırada olmalı.');
            }
            $duraklar[] = ['sehir' => $sehir, 'dakika' => $dakika, 'ucret' => $ucretKurus];
            $oncekiDakika = $dakika;
            $oncekiUcret = $ucretKurus;
        }
        if (count($duraklar) < 2 || count($duraklar) > 15) {
            throw new UygulamaHatasi('Bir güzergâhta 2–15 durak olmalıdır.');
        }
        $this->islemler->calistir(function (KayitDeposuInterface $depo) use ($id, $ad, $duraklar): void {
            $kayit = ['ad' => $ad, 'duraklar' => $duraklar];
            if ($id === 0) {
                $depo->ekle('guzergahlar', $kayit);
                return;
            }
            $mevcut = $this->kayit($depo, 'guzergahlar', $id);
            if ($mevcut['duraklar'] !== $duraklar) {
                $seferler = $depo->tumu('seferler');
                foreach ($seferler as $sefer) {
                    if ($sefer['guzergah_id'] === $id) {
                        $this->seferDegisebilir($depo, $sefer);
                        $this->otobusMusait($depo, $sefer, $duraklar);
                    }
                }
            }
            $depo->guncelle('guzergahlar', $id, $kayit);
        });
    }

    public function otobus(int $id, string $plaka, string $duzen, int $sira): void
    {
        $plaka = strtoupper($plaka);
        if (!preg_match('/^[0-9]{2} [A-Z]{1,3} [0-9]{2,4}$/D', $plaka)
            || !in_array($duzen, ['2+1', '2+2'], true) || $sira < 1 || $sira > 20) {
            throw new UygulamaHatasi('Geçerli plaka, koltuk düzeni ve 1–20 arası sıra sayısı girin.');
        }
        $this->islemler->calistir(function (KayitDeposuInterface $depo) use ($id, $plaka, $duzen, $sira): void {
            foreach ($depo->tumu('otobusler') as $otobus) {
                if ($otobus['id'] !== $id && $otobus['plaka'] === $plaka) {
                    throw new UygulamaHatasi('Bu plaka sistemde zaten var.', 409);
                }
            }
            $kayit = compact('plaka', 'duzen', 'sira');
            if ($id === 0) {
                $depo->ekle('otobusler', $kayit);
                return;
            }
            $mevcut = $this->kayit($depo, 'otobusler', $id);
            if ($mevcut['duzen'] !== $duzen || $mevcut['sira'] !== $sira) {
                foreach ($depo->tumu('seferler') as $sefer) {
                    if ($sefer['otobus_id'] === $id) {
                        $this->seferDegisebilir($depo, $sefer);
                    }
                }
            }
            $depo->guncelle('otobusler', $id, $kayit);
        });
    }

    public function sefer(int $id, int $guzergah, int $otobus, \DateTimeImmutable $tarih): void
    {
        $kalkis = $tarih->getTimestamp();
        if ($kalkis <= $this->saat->simdi()) {
            throw new UygulamaHatasi('Sefer kalkışı gelecek bir tarih ve saatte olmalıdır.');
        }
        $this->islemler->calistir(function (KayitDeposuInterface $depo) use ($id, $guzergah, $otobus, $kalkis): void {
            $rota = $this->kayit($depo, 'guzergahlar', $guzergah);
            $this->kayit($depo, 'otobusler', $otobus);
            $kayit = ['id' => $id, 'guzergah_id' => $guzergah, 'otobus_id' => $otobus, 'kalkis' => $kalkis];
            $this->otobusMusait($depo, $kayit, $rota['duraklar']);
            if ($id === 0) {
                $depo->ekle('seferler', $kayit);
                return;
            }
            $this->seferDegisebilir($depo, $this->kayit($depo, 'seferler', $id));
            $depo->guncelle('seferler', $id, $kayit);
        });
    }

    private function kayit(KayitDeposuInterface $depo, string $tablo, int $id): array
    {
        return $depo->bul($tablo, $id) ?? throw new UygulamaHatasi('İstenen yönetim kaydı bulunamadı.', 404);
    }

    private function seferDegisebilir(KayitDeposuInterface $depo, array $sefer): void
    {
        $simdi = $this->saat->simdi();
        if ($sefer['kalkis'] <= $simdi) {
            throw new UygulamaHatasi('Başlamış bir seferin tarih, rota veya koltuk düzeni değiştirilemez.', 409);
        }
        foreach ($depo->tumu('biletler') as $bilet) {
            if ($bilet['sefer_id'] === $sefer['id']) {
                throw new UygulamaHatasi('Bilet kaydı bulunan seferin tarih, rota veya koltuk düzeni değiştirilemez.', 409);
            }
        }
        foreach ($depo->tumu('kilitler') as $kilit) {
            if ($kilit['sefer_id'] === $sefer['id'] && $kilit['bitis'] > $simdi) {
                throw new UygulamaHatasi('Bu seferde koltuk seçimi yapılıyor. Kilit süresi bitince tekrar deneyin.', 409);
            }
        }
    }

    private function otobusMusait(KayitDeposuInterface $depo, array $aday, array $duraklar): void
    {
        $varis = $aday['kalkis'] + $duraklar[array_key_last($duraklar)]['dakika'] * 60;
        foreach ($depo->tumu('seferler') as $diger) {
            if ($diger['id'] === $aday['id'] || $diger['otobus_id'] !== $aday['otobus_id']) {
                continue;
            }
            $digerDuraklar = $this->kayit($depo, 'guzergahlar', $diger['guzergah_id'])['duraklar'];
            $digerVaris = $diger['kalkis'] + $digerDuraklar[array_key_last($digerDuraklar)]['dakika'] * 60;
            if (EtapCakisma::varMi($aday['kalkis'], $varis, $diger['kalkis'], $digerVaris)) {
                throw new UygulamaHatasi('Bu otobüs aynı saatlerde başka bir sefere atanmış.', 409);
            }
        }
    }
}
