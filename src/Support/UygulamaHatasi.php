<?php
declare(strict_types=1);

namespace App\Support;

final class UygulamaHatasi extends \RuntimeException
{
    public function __construct(string $mesaj, int $durum = 422)
    {
        parent::__construct($mesaj, $durum);
    }
}
