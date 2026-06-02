<?php

declare(strict_types=1);

namespace App\Web\Entitas\Model;

use Cycle\ORM\Select\Repository;
use App\Shared\TenantContext;
use Cycle\ORM\Select;

class EntitasRepository extends Repository
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
        $entity = $this->findByPK($id);
        if ($entity !== null && $this->tenantContext->getActiveCampusCode() !== null) {
            if ($entity->kodeKampus !== $this->tenantContext->getActiveCampusCode()) {
                return null;
            }
        }
        return $entity;
    }
}
