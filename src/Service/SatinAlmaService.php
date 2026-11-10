<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\IslemYoneticisiInterface;
use App\Repository\KayitDeposuInterface;
use App\Support\Girdi;
use App\Support\SaatInterface;
use App\Support\UygulamaHatasi;

final class SatinAlmaService
{
    public function __construct(
        private IslemYoneticisiInterface $islemler,
        private SaatInterface $saat,
        private OdemeService $odeme
    ) {
    }

    public function satinAl(int $kullanici, int $sefer, int $binis, int $inis, array $yolcular, string $senaryo): int
    {
        if ($yolcular === [] || count($yolcular) > KoltukKilitService::EN_COK) {
            throw new UygulamaHatasi('Bir işlemde 1–5 yolcu olmalıdır.');
        }
        $tcListesi = [];
        foreach ($yolcular as $koltuk => $yolcu) {
            $koltuk = Girdi::sayi($koltuk);
            if (!is_array($yolcu)) {
                throw new UygulamaHatasi('Yolcu bilgileri eksik veya geçersiz.');
            }
            $ad = Girdi::metin($yolcu['ad'] ?? null);
            $tc = Girdi::metin($yolcu['tc'] ?? null);
            if (strlen($ad) < 3 || strlen($ad) > 100 || !TcKimlikDogrulayici::gecerli($tc) || in_array($tc, $tcListesi, true)) {
                throw new UygulamaHatasi('Her koltuk için ad soyad ve farklı, geçerli bir demo kimlik numarası girin.');
            }
            $tcListesi[] = $tc;
            $yolcular[$koltuk] = ['ad' => $ad, 'tc_ozeti' => password_hash($tc, PASSWORD_DEFAULT), 'tc_son' => substr($tc, -2)];
        }
        return $this->islemler->calistir(function (KayitDeposuInterface $depo) use (
            $kullanici, $sefer, $binis, $inis, $yolcular, $senaryo
        ): int {
            $simdi = $this->saat->simdi();
            $etap = SeferAramaService::etap($depo, $sefer, $binis, $inis);
            $kilitler = [];
            foreach (KoltukKilitService::aktifler($depo, $etap, $simdi) as $kayit) {
                if (KoltukKilitService::kendiKilidi($kayit, $kullanici, $etap)) {
                    $kilitler[$kayit['koltuk']] = $kayit;
                }
            }
            if ($etap['kalkis'] <= $simdi || array_diff_key($yolcular, $kilitler) || array_diff_key($kilitler, $yolcular)) {
                throw new UygulamaHatasi('Koltuk seçimi değişti veya kilit süresi doldu. Koltuk haritasına dönün.', 409);
            }
            $this->odeme->onayla($senaryo);
            $siparis = $depo->ekle('siparisler', ['kullanici_id' => $kullanici, 'zaman' => $simdi]);
            foreach ($yolcular as $koltuk => $yolcu) {
                $cinsiyet = $kilitler[$koltuk]['cinsiyet'];
                $yolcuId = $depo->ekle('yolcular', $yolcu + ['cinsiyet' => $cinsiyet]);
                $depo->ekle('biletler', [
                    'pnr' => PnrUretici::uret($depo), 'siparis_id' => $siparis, 'yolcu_id' => $yolcuId,
                    'sefer_id' => $sefer, 'binis' => $binis, 'inis' => $inis, 'koltuk' => $koltuk,
                    'cinsiyet' => $cinsiyet, 'ucret' => $etap['ucret'], 'durum' => 'aktif', 'iade' => 0,
                ]);
                $depo->sil('kilitler', $kilitler[$koltuk]['id']);
            }
            return $siparis;
        });
    }
}
