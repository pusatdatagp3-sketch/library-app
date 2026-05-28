<?php

declare(strict_types=1);

namespace App\Web\Guru\Model;

use Cycle\ORM\Select\Repository;

class GuruRepository extends Repository
{
    /**
     * @return GuruEntity[]
     */
    public function getAll(): array
    {
        return $this->select()
            ->load('kamar')
            ->load('konsulat')
            ->orderBy('guru.kdg', 'DESC')
            ->fetchAll();
    }

    public function getById(int $kdg): ?GuruEntity
    {
        return $this->select()
            ->load('kamar')
            ->load('konsulat')
            ->where('guru.kdg', $kdg)
            ->fetchOne();
    }

    public function getByStambuk(string $stambuk): ?GuruEntity
    {
        return $this->select()
            ->load('kamar')
            ->load('konsulat')
            ->where('guru.stambuk', $stambuk)
            ->fetchOne();
    }
}
