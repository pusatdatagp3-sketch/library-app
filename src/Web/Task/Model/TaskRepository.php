<?php

declare(strict_types=1);

namespace App\Web\Task\Model;

use Cycle\ORM\Select\Repository;
use App\Shared\TenantContext;
use Cycle\ORM\Select;

class TaskRepository extends Repository
{
    private TenantContext $tenantContext;

    public function __construct(Select $select, TenantContext $tenantContext)
    {
        parent::__construct($select);
        $this->tenantContext = $tenantContext;
    }

    public function select(): Select
    {
        $select = parent::select();
        $activeCampus = $this->tenantContext->getActiveCampusCode();
        if ($activeCampus !== null) {
            $select = $select->where('kode_kampus', $activeCampus);
        }
        return $select;
    }

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
        $entity = $this->findByPK($id);
        if ($entity !== null && $this->tenantContext->getActiveCampusCode() !== null) {
            if ($entity->kodeKampus !== $this->tenantContext->getActiveCampusCode()) {
                return null;
            }
        }
        return $entity;
    }
}
