<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\KoltukKilitService;
use App\Support\Girdi;
use App\Support\Oturum;
use App\Support\Yanit;

final class KoltukController
{
    public function __construct(private KoltukKilitService $koltuklar)
    {
    }

    public function harita(array $girdi): void
    {
        $etapIstegi = EtapIstegi::oku($girdi);
        $durum = $this->koltuklar->durum(Oturum::kimlik(), ...$etapIstegi);
        Yanit::gorunum('koltuklar', 'Koltuk seç', $durum + ['etapIstegi' => $etapIstegi]);
    }

    public function durum(array $girdi): void
    {
        $durum = $this->koltuklar->durum(Oturum::kimlik(), ...EtapIstegi::oku($girdi));
        Yanit::json($durum);
    }

    public function kilitle(array $girdi): void
    {
        $etapIstegi = EtapIstegi::oku($girdi);
        $this->koltuklar->kilitle(Oturum::kimlik(), ...($etapIstegi + [
            'koltuk' => Girdi::sayi($girdi['koltuk'] ?? null), 'cinsiyet' => Girdi::metin($girdi['cinsiyet'] ?? null),
        ]));
        $this->durum($girdi);
    }

    public function birak(array $girdi): void
    {
        $this->koltuklar->birak(Oturum::kimlik(), ...(EtapIstegi::oku($girdi) + [
            'koltuk' => Girdi::sayi($girdi['koltuk'] ?? null),
        ]));
        $this->durum($girdi);
    }
}
