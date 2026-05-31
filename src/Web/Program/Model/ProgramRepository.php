<?php

declare(strict_types=1);

namespace App\Web\Program\Model;

use Cycle\ORM\Select\Repository;

class ProgramRepository extends Repository
{
    /**
     * @return Program[]
     */
    public function findByEntitas(int $entitasId): array
    {
        return $this->select()
            ->where(['entitas_id' => $entitasId])
            ->orderBy('id', 'DESC')
            ->fetchAll();
    }

    public function findById(int $id): ?Program
    {
        return $this->findByPK($id);
    }
}
