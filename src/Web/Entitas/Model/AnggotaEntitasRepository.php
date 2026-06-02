<?php

declare(strict_types=1);

namespace App\Web\Entitas\Model;

use Cycle\ORM\Select\Repository;
use App\Shared\TenantContext;
use Cycle\ORM\Select;

class AnggotaEntitasRepository extends Repository
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
        $entity = $this->findByPK($id);
        if ($entity !== null && $this->tenantContext->getActiveCampusCode() !== null) {
            if ($entity->kodeCampus !== $this->tenantContext->getActiveCampusCode()) {
                return null;
            }
        }
        return $entity;
    }
}
