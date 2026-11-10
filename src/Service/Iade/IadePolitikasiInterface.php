<?php
declare(strict_types=1);

namespace App\Service\Iade;

interface IadePolitikasiInterface
{
    public function uygun(int $kalanSaniye): bool;

    public function oran(): int;
}
