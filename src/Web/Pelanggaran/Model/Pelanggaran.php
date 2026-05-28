<?php

declare(strict_types=1);

namespace App\Web\Pelanggaran\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;

#[Entity(role: 'pelanggaran', table: 'pelanggaran')]
class Pelanggaran
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'string(50)')]
    public string $stambuk = '';

    #[Column(type: 'string(255)')]
    public string $pelanggaran = '';

    #[Column(type: 'integer')]
    public int $poin = 0;

    #[Column(type: 'text', nullable: true)]
    public ?string $keterangan = null;

    public array $errors = [];

    public function load(array $data): bool
    {
        $this->stambuk = trim((string)($data['stambuk'] ?? $this->stambuk));
        $this->pelanggaran = trim((string)($data['pelanggaran'] ?? $this->pelanggaran));
        $this->poin = isset($data['poin']) && $data['poin'] !== '' ? (int)$data['poin'] : 0;
        $this->keterangan = trim((string)($data['keterangan'] ?? $this->keterangan));
        return !empty($data);
    }

    public function validate(): bool
    {
        $this->errors = [];
        if ($this->stambuk === '') {
            $this->errors['stambuk'] = 'Kolom Stambuk tidak boleh kosong.';
        }
        if ($this->pelanggaran === '') {
            $this->errors['pelanggaran'] = 'Kolom Pelanggaran tidak boleh kosong.';
        }
        if (!isset($this->poin) || $this->poin <= 0) {
            $this->errors['poin'] = 'Kolom Poin tidak boleh kosong.';
        }
        return empty($this->errors);
    }
}
