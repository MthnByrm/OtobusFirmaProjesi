<?php
declare(strict_types=1);

use App\Support\Oturum;
use App\Support\Yanit;

function e(string|int $deger): string
{
    return htmlspecialchars((string) $deger, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $sayfa, array $parametreler = []): string
{
    return e(Yanit::url($sayfa, $parametreler));
}

function csrfAlani(): void
{
    echo '<input type="hidden" name="csrf" value="' . e(Oturum::csrf()) . '">';
}

function tl(int $kurus): string
{
    return number_format(intdiv($kurus, 100), 0, ',', '.') . ',' . str_pad((string) ($kurus % 100), 2, '0', STR_PAD_LEFT) . ' TL';
}
