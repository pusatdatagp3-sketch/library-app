<?php

declare(strict_types=1);

namespace App\Web\Konsulat\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\ORM\ORMInterface;

#[Entity(role: 'konsulat', table: 'konsulat')]
class Konsulat
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'string(100)', unique: true)]
    public string $konsulat = '';

    #[Column(type: 'string(100)')]
    public string $kampus = '';

    public array $errors = [];

    public function load(array $data): bool
    {
        $this->konsulat = trim((string)($data['konsulat'] ?? $this->konsulat));
        $this->kampus = trim((string)($data['kampus'] ?? $this->kampus));
        return !empty($data);
    }

    public function validate(ORMInterface $orm): bool
    {
        $this->errors = [];
        if ($this->konsulat === '') {
            $this->errors['konsulat'] = 'Nama Konsulat tidak boleh kosong.';
        }
        if ($this->kampus === '') {
            $this->errors['kampus'] = 'Nama Kampus tidak boleh kosong.';
        }

        if (empty($this->errors['konsulat'])) {
            $repo = $orm->getRepository(self::class);
            $query = $repo->select()->where(['konsulat' => $this->konsulat]);
            if ($this->id !== null) {
                $query = $query->where('id', '!=', $this->id);
            }
            $existing = $query->findOne();
            if ($existing !== null) {
                $this->errors['konsulat'] = 'Nama Konsulat ini sudah terdaftar.';
            }
        }

        return empty($this->errors);
    }
}
