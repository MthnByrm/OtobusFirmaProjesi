<?php
declare(strict_types=1);

namespace App\Service\Iade;

use App\Support\UygulamaHatasi;

final class IadeHesaplayici
{
    /** @param list<IadePolitikasiInterface> $politikalar */
    public function __construct(private array $politikalar)
    {
    }

    /** @return array{oran: int, tutar: int} */
    public function hesapla(int $ucret, int $kalanSaniye): array
    {
        foreach ($this->politikalar as $politika) {
            if ($politika->uygun($kalanSaniye)) {
                $oran = $politika->oran();
                return ['oran' => $oran, 'tutar' => intdiv($ucret * $oran + 50, 100)];
            }
        }
        throw new UygulamaHatasi('Kalkış zamanı geçmiş bilet iptal edilemez.', 409);
    }
}
