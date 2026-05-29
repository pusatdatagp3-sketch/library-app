<?php

declare(strict_types=1);

namespace App\Web\Rayon\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;

#[Entity(role: 'rayon', table: 'rayon')]
class Rayon
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'string(20)')]
    public string $nama_rayon = '';

    #[Column(type: 'string(20)')]
    public string $zona = '';

    public array $errors = [];

    public function load(array $data): bool
    {
        $this->nama_rayon = trim((string)($data['nama_rayon'] ?? $this->nama_rayon));
        $this->zona = trim((string)($data['zona'] ?? $this->zona));
        return !empty($data);
    }

    public function validate(): bool
    {
        $this->errors = [];
        if ($this->nama_rayon === '' || $this->nama_rayon === null) {
            $this->errors['nama_rayon'] = 'Kolom Nama_rayon tidak boleh kosong.';
        }
        if ($this->zona === '' || $this->zona === null) {
            $this->errors['zona'] = 'Kolom Zona tidak boleh kosong.';
        }
        return empty($this->errors);
    }
}
