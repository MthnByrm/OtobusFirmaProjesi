<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\KoltukKilitService;
use App\Service\SatinAlmaService;
use App\Support\Girdi;
use App\Support\Oturum;
use App\Support\UygulamaHatasi;
use App\Support\Yanit;

final class SatinAlmaController
{
    public function __construct(private KoltukKilitService $koltuklar, private SatinAlmaService $satis)
    {
    }

    public function form(array $girdi): void
    {
        $etapIstegi = EtapIstegi::oku($girdi);
        $durum = $this->koltuklar->durum(Oturum::kimlik(), ...$etapIstegi);
        if ($durum['secimler'] === []) {
            Oturum::mesaj('Devam etmek için önce haritadan koltuk seçin.');
            Yanit::yonlendir('koltuklar', $etapIstegi);
            return;
        }
        $bitis = min(array_column($durum['secimler'], 'bitis'));
        $toplam = count($durum['secimler']) * $durum['etap']['ucret'];
        Yanit::gorunum('odeme', 'Yolcu bilgileri ve ödeme', $durum + compact('etapIstegi', 'bitis', 'toplam'));
    }

    public function ode(array $girdi): void
    {
        $etapIstegi = EtapIstegi::oku($girdi);
        $yolcular = is_array($girdi['yolcular'] ?? null) ? $girdi['yolcular'] : [];
        try {
            $siparis = $this->satis->satinAl(Oturum::kimlik(), ...($etapIstegi + [
                'yolcular' => $yolcular, 'senaryo' => Girdi::metin($girdi['odeme'] ?? null),
            ]));
        } catch (UygulamaHatasi $hata) {
            Oturum::mesaj($hata->getMessage());
            Yanit::yonlendir($hata->getCode() === 409 ? 'koltuklar' : 'odeme', $etapIstegi);
            return;
        }
        Oturum::mesaj('Demo ödeme onaylandı. Biletleriniz hazır.');
        Yanit::yonlendir('siparis', ['id' => $siparis]);
    }
}
