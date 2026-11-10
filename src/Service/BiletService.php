<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\IslemYoneticisiInterface;
use App\Repository\KayitDeposuInterface;
use App\Service\Iade\IadeHesaplayici;
use App\Support\SaatInterface;
use App\Support\UygulamaHatasi;

final class BiletService
{
    public function __construct(
        private IslemYoneticisiInterface $islemler,
        private SaatInterface $saat,
        private IadeHesaplayici $iadeler
    ) {
    }

    public function sorgula(string $pnr, string $tc): int
    {
        $pnr = strtoupper($pnr);
        if (!preg_match('/^[A-Z0-9]{6,10}$/D', $pnr) || !TcKimlikDogrulayici::gecerli($tc)) {
            throw new UygulamaHatasi('PNR ve geçerli demo kimlik numarası girin.');
        }
        return $this->islemler->oku(function (KayitDeposuInterface $depo) use ($pnr, $tc): int {
            foreach ($depo->tumu('biletler') as $bilet) {
                if ($bilet['pnr'] === $pnr) {
                    $yolcu = $depo->bul('yolcular', $bilet['yolcu_id']);
                    if (password_verify($tc, $yolcu['tc_ozeti'])) {
                        return $bilet['id'];
                    }
                    break;
                }
            }
            throw new UygulamaHatasi('Bu bilgilerle eşleşen bilet bulunamadı.', 404);
        });
    }

    public function bilet(int $id, int $kullanici, bool $sorgulandi): array
    {
        return $this->islemler->oku(fn (KayitDeposuInterface $depo): array =>
            $this->detay($depo, $this->yetkiliBilet($depo, $id, $kullanici, $sorgulandi)));
    }

    public function siparis(int $id, int $kullanici): array
    {
        return $this->islemler->oku(function (KayitDeposuInterface $depo) use ($id, $kullanici): array {
            $siparis = $depo->bul('siparisler', $id);
            if ($siparis === null || $siparis['kullanici_id'] !== $kullanici) {
                throw new UygulamaHatasi('Sipariş bulunamadı.', 404);
            }
            return $this->biletleriGetir($depo, [$id]);
        });
    }

    public function biletlerim(int $kullanici): array
    {
        return $this->islemler->oku(function (KayitDeposuInterface $depo) use ($kullanici): array {
            $idListesi = [];
            foreach ($depo->tumu('siparisler') as $siparis) {
                if ($siparis['kullanici_id'] === $kullanici) {
                    $idListesi[] = $siparis['id'];
                }
            }
            return array_reverse($this->biletleriGetir($depo, $idListesi));
        });
    }

    public function iptalEt(int $id, int $kullanici, bool $sorgulandi): void
    {
        $this->islemler->calistir(function (KayitDeposuInterface $depo) use ($id, $kullanici, $sorgulandi): void {
            $bilet = $this->yetkiliBilet($depo, $id, $kullanici, $sorgulandi);
            if ($bilet['durum'] !== 'aktif') {
                return;
            }
            $etap = SeferAramaService::etap($depo, $bilet['sefer_id'], $bilet['binis'], $bilet['inis']);
            $iade = $this->iadeler->hesapla($bilet['ucret'], $etap['kalkis'] - $this->saat->simdi());
            $depo->guncelle('biletler', $id, ['durum' => 'iptal', 'iade' => $iade['tutar']]);
        });
    }

    private function biletleriGetir(KayitDeposuInterface $depo, array $siparisIdleri): array
    {
        $sonuc = [];
        foreach ($depo->tumu('biletler') as $bilet) {
            if (in_array($bilet['siparis_id'], $siparisIdleri, true)) {
                $sonuc[] = $this->detay($depo, $bilet);
            }
        }
        return $sonuc;
    }

    private function yetkiliBilet(KayitDeposuInterface $depo, int $id, int $kullanici, bool $sorgulandi): array
    {
        $bilet = $depo->bul('biletler', $id);
        if ($bilet !== null) {
            $siparis = $depo->bul('siparisler', $bilet['siparis_id']);
            if ($sorgulandi || $siparis['kullanici_id'] === $kullanici) {
                return $bilet;
            }
        }
        throw new UygulamaHatasi('Bilet bulunamadı. PNR ve yolcu kimliği ile sorgulayabilirsiniz.', 404);
    }

    private function detay(KayitDeposuInterface $depo, array $bilet): array
    {
        $etap = SeferAramaService::etap($depo, $bilet['sefer_id'], $bilet['binis'], $bilet['inis']);
        $yolcu = $depo->bul('yolcular', $bilet['yolcu_id']);
        $kalan = $etap['kalkis'] - $this->saat->simdi();
        return $bilet + [
            'etap' => $etap, 'yolcu' => $yolcu['ad'], 'maskeli_tc' => '*********' . $yolcu['tc_son'],
            'iade_teklifi' => $bilet['durum'] === 'aktif' && $kalan >= 0
                ? $this->iadeler->hesapla($bilet['ucret'], $kalan) : null,
        ];
    }
}
