<?php

declare(strict_types=1);

namespace App\Web\Modul\Model;

use Cycle\ORM\Select\Repository;

class ModulRepository extends Repository
{
    public function findByName(string $name): ?Modul
    {
        return $this->select()->where(['nama' => $name])->fetchOne();
    }
}
