<?php
declare(strict_types=1);

namespace App\Service\Iade;

final class TamIadePolitikasi implements IadePolitikasiInterface
{
    public function uygun(int $kalanSaniye): bool
    {
        return $kalanSaniye > 86400;
    }

    public function oran(): int
    {
        return 100;
    }
}
