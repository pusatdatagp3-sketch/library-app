<?php

declare(strict_types=1);

namespace App\Web\Kanban\Model;

use Cycle\ORM\Select\Repository;

class KanbanColumnRepository extends Repository
{
    /**
     * @return KanbanColumn[]
     */
    public function findByEntitas(int $entitasId): array
    {
        return $this->select()
            ->where(['entitas_id' => $entitasId])
            ->orderBy('urutan', 'ASC')
            ->fetchAll();
    }

    public function findById(int $id): ?KanbanColumn
    {
        return $this->findByPK($id);
    }
}
