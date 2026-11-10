<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\IslemYoneticisiInterface;
use App\Repository\KayitDeposuInterface;
use App\Support\UygulamaHatasi;

final class KimlikService
{
    public function __construct(private IslemYoneticisiInterface $islemler)
    {
    }

    public function kayit(string $ad, string $eposta, string $parola): void
    {
        $eposta = strtolower($eposta);
        if (strlen($ad) < 3 || strlen($ad) > 100 || !filter_var($eposta, FILTER_VALIDATE_EMAIL)
            || strlen($eposta) > 160 || strlen($parola) < 8 || strlen($parola) > 72 || str_contains($parola, "\0")) {
            throw new UygulamaHatasi('Ad soyad, geçerli e-posta ve 8–72 baytlık bir parola girin.');
        }
        $ozet = password_hash($parola, PASSWORD_DEFAULT);
        $this->islemler->calistir(function (KayitDeposuInterface $depo) use ($ad, $eposta, $ozet): void {
            foreach ($depo->tumu('kullanicilar') as $kullanici) {
                if ($kullanici['eposta'] === $eposta) {
                    throw new UygulamaHatasi('Bu e-posta ile bir hesap var.', 409);
                }
            }
            $depo->ekle('kullanicilar', ['ad' => $ad, 'eposta' => $eposta, 'parola' => $ozet, 'rol' => 'yolcu']);
        });
    }

    /** @return array{id: int, ad: string, rol: string} */
    public function giris(string $eposta, string $parola): array
    {
        $eposta = strtolower($eposta);
        $kullanicilar = $this->islemler->oku(fn (KayitDeposuInterface $depo): array => $depo->tumu('kullanicilar'));
        foreach ($kullanicilar as $kullanici) {
            if ($kullanici['eposta'] === $eposta && strlen($parola) <= 72 && password_verify($parola, $kullanici['parola'])) {
                return ['id' => $kullanici['id'], 'ad' => $kullanici['ad'], 'rol' => $kullanici['rol']];
            }
        }
        throw new UygulamaHatasi('E-posta veya parola hatalı.', 401);
    }
}
