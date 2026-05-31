<?php

declare(strict_types=1);

namespace App\Web\Task\Model;

use Cycle\ORM\Select\Repository;

class TaskRepository extends Repository
{
    /**
     * @return Task[]
     */
    public function findByProgram(int $programId): array
    {
        return $this->select()
            ->where(['program_id' => $programId])
            ->orderBy('kanban_column_id', 'ASC')
            ->orderBy('urutan', 'ASC')
            ->fetchAll();
    }

    public function findById(int $id): ?Task
    {
        return $this->findByPK($id);
    }
}
