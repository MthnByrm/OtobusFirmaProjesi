<?php
declare(strict_types=1);

namespace App\Service;

use App\Support\UygulamaHatasi;

final class OdemeService
{
    public function onayla(string $senaryo): void
    {
        if ($senaryo === 'ret') {
            throw new UygulamaHatasi('Demo ödeme reddedildi. Seçim süresi dolmadan tekrar deneyebilirsiniz.');
        }
        if ($senaryo !== 'onay') {
            throw new UygulamaHatasi('Demo ödeme senaryosunu seçin.');
        }
    }
}
