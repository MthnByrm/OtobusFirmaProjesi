<?php
declare(strict_types=1);

namespace App\Repository\Json;

use App\Repository\KayitDeposuInterface;

final class JsonKayitDeposu implements KayitDeposuInterface
{
    private bool $degisti = false;

    /** @param array<string, mixed> $veri */
    public function __construct(private array $veri)
    {
    }

    public function tumu(string $tablo): array
    {
        return $this->veri[$tablo];
    }

    public function bul(string $tablo, int $id): ?array
    {
        foreach ($this->tumu($tablo) as $kayit) {
            if ($kayit['id'] === $id) {
                return $kayit;
            }
        }
        return null;
    }

    public function ekle(string $tablo, array $kayit): int
    {
        $id = ($this->veri['son_id'][$tablo] ?? 0) + 1;
        $this->veri['son_id'][$tablo] = $id;
        $this->veri[$tablo][] = ['id' => $id] + $kayit;
        $this->degisti = true;
        return $id;
    }

    public function guncelle(string $tablo, int $id, array $alanlar): void
    {
        foreach ($this->tumu($tablo) as $sira => $kayit) {
            if ($kayit['id'] === $id) {
                $this->veri[$tablo][$sira] = ['id' => $id] + $alanlar + $kayit;
                $this->degisti = true;
                return;
            }
        }
        throw new \LogicException('Güncellenecek kayıt bulunamadı.');
    }

    public function sil(string $tablo, int $id): void
    {
        foreach ($this->tumu($tablo) as $sira => $kayit) {
            if ($kayit['id'] === $id) {
                array_splice($this->veri[$tablo], $sira, 1);
                $this->degisti = true;
                return;
            }
        }
    }

    /** @return array<string, mixed> */
    public function veri(): array
    {
        return $this->veri;
    }

    public function degisti(): bool
    {
        return $this->degisti;
    }
}
