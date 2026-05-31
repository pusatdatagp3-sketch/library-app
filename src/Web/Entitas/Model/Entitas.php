<?php

declare(strict_types=1);

namespace App\Web\Entitas\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use App\Web\Modul\Model\Modul;

#[Entity(role: 'entitas', table: 'entitas', repository: EntitasRepository::class)]
class Entitas
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'string(255)')]
    public string $nama = '';

    #[Column(type: 'integer', name: 'modul_id', nullable: true)]
    public ?int $modulId = null;

    #[BelongsTo(target: Modul::class, innerKey: 'modulId', fkAction: 'CASCADE', nullable: true, load: 'eager')]
    public ?Modul $modul = null;

    #[Column(type: 'text', nullable: true)]
    public ?string $deskripsi = null;

    #[Column(type: 'datetime', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;

    #[Column(type: 'datetime', name: 'updated_at', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;

    public array $errors = [];

    public function load(array $data): bool
    {
        $this->nama = trim((string)($data['nama'] ?? $this->nama));
        $this->deskripsi = isset($data['deskripsi']) ? trim((string)$data['deskripsi']) : $this->deskripsi;
        if (isset($data['modul_id'])) {
            $this->modulId = (int)$data['modul_id'];
        }
        return !empty($data);
    }

    public function validate(): bool
    {
        $this->errors = [];
        if ($this->nama === '') {
            $this->errors['nama'] = 'Nama entitas tidak boleh kosong.';
        }
        return empty($this->errors);
    }
}
