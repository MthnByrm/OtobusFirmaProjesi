<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\SeferAramaService;
use App\Support\Girdi;
use App\Support\Yanit;

final class SeferAramaController
{
    public function __construct(private SeferAramaService $seferler)
    {
    }

    public function form(): void
    {
        Yanit::gorunum('arama', 'Sefer ara', $this->seferler->form() + ['sonuclar' => null, 'nereden' => '', 'nereye' => '']);
    }

    public function ara(array $girdi): void
    {
        $nereden = Girdi::metin($girdi['nereden'] ?? null);
        $nereye = Girdi::metin($girdi['nereye'] ?? null);
        $gun = Girdi::tarih($girdi['tarih'] ?? null);
        $tarih = $gun->format('Y-m-d');
        $sonuclar = $this->seferler->ara($nereden, $nereye, $gun);
        Yanit::gorunum('arama', 'Sefer sonuçları', compact('sonuclar', 'nereden', 'nereye', 'tarih') + $this->seferler->form());
    }
}
