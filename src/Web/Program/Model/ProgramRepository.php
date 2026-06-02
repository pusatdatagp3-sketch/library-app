<?php

declare(strict_types=1);

namespace App\Web\Program\Model;

use Cycle\ORM\Select\Repository;
use App\Shared\TenantContext;
use Cycle\ORM\Select;

class ProgramRepository extends Repository
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
        if ($activeCampus === 'ALL') {
            $select = $select->where('kode_kampus', 'in', $this->tenantContext->getAllowedCampusCodes());
        } elseif ($activeCampus !== null) {
            $select = $select->where('kode_kampus', $activeCampus);
        }
        return $select;
    }

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
        $entity = $this->findByPK($id);
        if ($entity !== null && !$this->tenantContext->hasActiveAccess($entity->kodeKampus)) {
            return null;
        }
        return $entity;
    }
}
