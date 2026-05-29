<?php

declare(strict_types=1);

namespace App\Web\Perizinan\Model;

use Cycle\ORM\Select\Repository;

class PerizinanRepository extends Repository
{
    /**
     * @return Perizinan[]
     */
    public function getAll(): array
    {
        return $this->select()->orderBy('perizinan.id', 'DESC')->fetchAll();
    }

    /**
     * @return Perizinan[]
     */
    public function getAllFiltered(array $filters = []): array
    {
        $select = $this->select();

        if (!empty($filters['id'])) {
            $select = $select->where('id', '=', (int)$filters['id']);
        }
        if (!empty($filters['kemana'])) {
            $select = $select->where('kemana', 'like', '%' . $filters['kemana'] . '%');
        }
        if (!empty($filters['sama_siapa'])) {
            $select = $select->where('sama_siapa', 'like', '%' . $filters['sama_siapa'] . '%');
        }
        if (!empty($filters['berapa_orang'])) {
            $select = $select->where('berapa_orang', 'like', '%' . $filters['berapa_orang'] . '%');
        }

        return $select->orderBy('perizinan.id', 'DESC')->fetchAll();
    }

    public function getById(int $id): ?Perizinan
    {
        return $this->findByPK($id);
    }
}
