<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\BiletService;
use App\Support\Girdi;
use App\Support\Oturum;
use App\Support\Yanit;

final class BiletController
{
    public function __construct(private BiletService $biletler)
    {
    }

    public function sorguFormu(): void
    {
        Yanit::gorunum('sorgu', 'Bilet sorgula');
    }

    public function sorgula(array $girdi): void
    {
        $id = $this->biletler->sorgula(Girdi::metin($girdi['pnr'] ?? null), Girdi::metin($girdi['tc'] ?? null));
        Oturum::biletErisimiVer($id);
        Yanit::yonlendir('bilet', ['id' => $id]);
    }

    public function goster(array $girdi): void
    {
        $id = Girdi::sayi($girdi['id'] ?? null);
        $bilet = $this->biletler->bilet($id, Oturum::kimlik(), Oturum::biletErisimiVar($id));
        Yanit::gorunum('bilet', 'Bilet detayı', ['bilet' => $bilet]);
    }

    public function iptal(array $girdi): void
    {
        $id = Girdi::sayi($girdi['id'] ?? null);
        $this->biletler->iptalEt($id, Oturum::kimlik(), Oturum::biletErisimiVar($id));
        Oturum::mesaj('Bilet iptal edildi. İade tutarı bilette gösteriliyor.');
        Yanit::yonlendir('bilet', ['id' => $id]);
    }

    public function siparis(array $girdi): void
    {
        $id = Girdi::sayi($girdi['id'] ?? null);
        Yanit::gorunum('biletler', 'Sipariş biletleri', ['biletler' => $this->biletler->siparis($id, Oturum::kimlik())]);
    }

    public function biletlerim(): void
    {
        Yanit::gorunum('biletler', 'Biletlerim', ['biletler' => $this->biletler->biletlerim(Oturum::kimlik())]);
    }
}
