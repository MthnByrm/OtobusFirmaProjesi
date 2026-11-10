<?php
declare(strict_types=1);

namespace App\Repository;

interface IslemYoneticisiInterface
{
    /**
     * @template T
     * @param callable(KayitDeposuInterface): T $islem
     * @return T
     */
    public function oku(callable $islem): mixed;

    /**
     * @template T
     * @param callable(KayitDeposuInterface): T $islem
     * @return T
     */
    public function calistir(callable $islem): mixed;
}
