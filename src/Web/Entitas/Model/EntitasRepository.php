<?php

declare(strict_types=1);

namespace App\Web\Entitas\Model;

use Cycle\ORM\Select\Repository;

class EntitasRepository extends Repository
{
    /**
     * @return Entitas[]
     */
    public function findByModul(int $modulId): array
    {
        return $this->select()
            ->where(['modul_id' => $modulId])
            ->orderBy('id', 'DESC')
            ->fetchAll();
    }

    public function findById(int $id): ?Entitas
    {
        return $this->findByPK($id);
    }
}
