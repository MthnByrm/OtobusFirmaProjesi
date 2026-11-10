<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\KimlikService;
use App\Support\Girdi;
use App\Support\Oturum;
use App\Support\Yanit;

final class KimlikController
{
    public function __construct(private KimlikService $kimlik)
    {
    }

    public function girisFormu(array $girdi): void
    {
        Yanit::gorunum('kimlik', 'Giriş yap', ['kayit' => false, 'donus' => $this->donus($girdi)]);
    }

    public function kayitFormu(array $girdi): void
    {
        Yanit::gorunum('kimlik', 'Hesap oluştur', ['kayit' => true, 'donus' => $this->donus($girdi)]);
    }

    public function kaydet(array $girdi): void
    {
        $eposta = Girdi::metin($girdi['eposta'] ?? null);
        $parola = is_string($girdi['parola'] ?? null) ? $girdi['parola'] : '';
        $this->kimlik->kayit(Girdi::metin($girdi['ad'] ?? null), $eposta, $parola);
        Oturum::oturumAc($this->kimlik->giris($eposta, $parola));
        Oturum::mesaj('Hesabınız oluşturuldu.');
        $this->yonlendir($girdi);
    }

    public function giris(array $girdi): void
    {
        $parola = is_string($girdi['parola'] ?? null) ? $girdi['parola'] : '';
        Oturum::oturumAc($this->kimlik->giris(Girdi::metin($girdi['eposta'] ?? null), $parola));
        $this->yonlendir($girdi);
    }

    public function cikis(): void
    {
        Oturum::oturumKapat();
        Yanit::yonlendir('anasayfa');
    }

    private function donus(array $girdi): array
    {
        $sefer = Girdi::metin($girdi['sefer'] ?? null);
        $binis = Girdi::metin($girdi['binis'] ?? null);
        $inis = Girdi::metin($girdi['inis'] ?? null);
        return preg_match("/^[0-9]+$/D", $sefer) && preg_match("/^[0-9]+$/D", $binis) && preg_match("/^[0-9]+$/D", $inis)
            ? compact('sefer', 'binis', 'inis') : [];
    }

    private function yonlendir(array $girdi): void
    {
        $donus = $this->donus($girdi);
        $hedef = (Oturum::kullanici()['rol'] ?? null) === 'yonetici' ? 'yonetim' : 'anasayfa';
        Yanit::yonlendir($donus === [] ? $hedef : 'koltuklar', $donus);
    }
}
