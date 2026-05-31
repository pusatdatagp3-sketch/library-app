<?php

declare(strict_types=1);

namespace App\Web\Entitas\Model;

use Cycle\ORM\Select\Repository;

class AnggotaEntitasRepository extends Repository
{
    /**
     * @return AnggotaEntitas[]
     */
    public function findByEntitas(int $entitasId): array
    {
        return $this->select()
            ->where(['entitas_id' => $entitasId])
            ->orderBy('id', 'ASC')
            ->fetchAll();
    }

    public function findById(int $id): ?AnggotaEntitas
    {
        return $this->findByPK($id);
    }
}
