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
            ->orderBy('guru.kdg', 'DESC')
            ->fetchAll();
    }

    public function getById(int $kdg): ?GuruEntity
    {
        return $this->select()
            ->load('kamar')
            ->where('guru.kdg', $kdg)
            ->fetchOne();
    }

    public function getByStambuk(string $stambuk): ?GuruEntity
    {
        return $this->select()
            ->load('kamar')
            ->where('guru.stambuk', $stambuk)
            ->fetchOne();
    }
}
