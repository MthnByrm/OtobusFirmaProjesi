<?php
declare(strict_types=1);

namespace App\Support;

final class SistemSaati implements SaatInterface
{
    public function simdi(): int
    {
        return time();
    }
}
