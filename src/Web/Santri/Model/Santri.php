<?php

declare(strict_types=1);

namespace App\Web\Santri\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;

#[Entity(role: 'santri', table: 'santri')]
class Santri
{
    #[Column(type: 'primary')]
    public ?int $kds = null;

    #[Column(type: 'string(150)')]
    public string $nama = '';

    #[Column(type: 'string(50)')]
    public string $kelas = '';

    #[Column(type: 'string(100)')]
    public string $daerah = '';

    public array $errors = [];

    public function load(array $data): bool
    {
        $this->nama = trim((string)($data['nama'] ?? $this->nama));
        $this->kelas = trim((string)($data['kelas'] ?? $this->kelas));
        $this->daerah = trim((string)($data['daerah'] ?? $this->daerah));
        return !empty($data);
    }

    public function validate(): bool
    {
        $this->errors = [];
        if ($this->nama === '') {
            $this->errors['nama'] = 'Nama tidak boleh kosong.';
        }
        if ($this->kelas === '') {
            $this->errors['kelas'] = 'Kelas tidak boleh kosong.';
        }
        if ($this->daerah === '') {
            $this->errors['daerah'] = 'Daerah tidak boleh kosong.';
        }
        return empty($this->errors);
    }
}
