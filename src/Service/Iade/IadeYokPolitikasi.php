<?php
declare(strict_types=1);

namespace App\Service\Iade;

final class IadeYokPolitikasi implements IadePolitikasiInterface
{
    public function uygun(int $kalanSaniye): bool
    {
        return $kalanSaniye >= 0 && $kalanSaniye < 21600;
    }

    public function oran(): int
    {
        return 0;
    }
}
