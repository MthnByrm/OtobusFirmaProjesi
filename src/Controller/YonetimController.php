<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\RaporService;
use App\Service\YonetimService;
use App\Support\Girdi;
use App\Support\Oturum;
use App\Support\UygulamaHatasi;
use App\Support\Yanit;

final class YonetimController
{
    private const BOLUMLER = [
        'guzergahlar' => 'Güzergâhlar', 'otobusler' => 'Otobüsler', 'seferler' => 'Seferler', 'rapor' => 'Doluluk ve gelir',
    ];

    public function __construct(private YonetimService $yonetim, private RaporService $rapor)
    {
    }

    public function goster(array $girdi): void
    {
        $bolum = $this->bolum($girdi);
        $duzenle = Girdi::sayi($girdi['id'] ?? 0, 0);
        $veri = $bolum === 'rapor' ? $this->rapor->rapor() : $this->yonetim->katalog();
        if ($duzenle !== 0 && !isset($veri[$bolum][$duzenle])) {
            throw new UygulamaHatasi('Düzenlenecek kayıt bulunamadı.', 404);
        }
        Yanit::gorunum('yonetim', self::BOLUMLER[$bolum], $veri + compact('bolum', 'duzenle') + ['bolumler' => self::BOLUMLER]);
    }

    public function kaydet(array $girdi): void
    {
        $bolum = $this->bolum($girdi);
        $id = Girdi::sayi($girdi['id'] ?? 0, 0);
        match ($bolum) {
            'guzergahlar' => $this->yonetim->guzergah($id, Girdi::metin($girdi['ad'] ?? null), Girdi::metin($girdi['duraklar'] ?? null)),
            'otobusler' => $this->yonetim->otobus(
                $id, Girdi::metin($girdi['plaka'] ?? null), Girdi::metin($girdi['duzen'] ?? null), Girdi::sayi($girdi['sira'] ?? null, 1, 20)
            ),
            'seferler' => $this->yonetim->sefer(
                $id, Girdi::sayi($girdi['guzergah'] ?? null), Girdi::sayi($girdi['otobus'] ?? null),
                Girdi::tarih($girdi['kalkis'] ?? null, 'Y-m-d\TH:i')
            ),
            default => throw new UygulamaHatasi('Bu bölümde kayıt yapılamaz.', 405),
        };
        Oturum::mesaj('Kayıt başarıyla kaydedildi.');
        Yanit::yonlendir('yonetim', ['bolum' => $bolum]);
    }

    private function bolum(array $girdi): string
    {
        $bolum = Girdi::metin($girdi['bolum'] ?? 'rapor');
        if (!isset(self::BOLUMLER[$bolum])) {
            throw new UygulamaHatasi('Yönetim bölümü bulunamadı.', 404);
        }
        return $bolum;
    }
}
