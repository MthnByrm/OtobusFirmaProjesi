<?php
declare(strict_types=1);

namespace App\Repository;

interface KayitDeposuInterface
{
    /** @return list<array<string, mixed>> */
    public function tumu(string $tablo): array;

    /** @return array<string, mixed>|null */
    public function bul(string $tablo, int $id): ?array;

    /** @param array<string, mixed> $kayit */
    public function ekle(string $tablo, array $kayit): int;

    /** @param array<string, mixed> $alanlar */
    public function guncelle(string $tablo, int $id, array $alanlar): void;

    public function sil(string $tablo, int $id): void;
}
