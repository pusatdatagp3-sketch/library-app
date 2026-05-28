<?php

declare(strict_types=1);

namespace App\Web\Kamar\Model;

use Cycle\ORM\Select\Repository;

class KamarRepository extends Repository
{
    /**
     * @return Kamar[]
     */
    public function getAllKamar(): array
    {
        return $this->select()->orderBy('id', 'DESC')->fetchAll();
    }

    public function findById(int $id): ?Kamar
    {
        return $this->findByPK($id);
    }
}
