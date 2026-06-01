<?php

declare(strict_types=1);

namespace App\Web\Kanban\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use App\Web\Entitas\Model\Entitas;

#[Entity(role: 'entitas_program_kanban_column', table: 'entitas_program_kanban_column', repository: KanbanColumnRepository::class)]
class KanbanColumn
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'integer', name: 'entitas_id')]
    public int $entitasId;

    #[BelongsTo(target: Entitas::class, innerKey: 'entitasId', fkAction: 'CASCADE', load: 'eager')]
    public ?Entitas $entitas = null;

    #[Column(type: 'string(255)')]
    public string $nama = '';

    #[Column(type: 'integer', default: 1)]
    public int $urutan = 1;

    #[Column(type: 'integer', default: 0)]
    public int $progress = 0;

    #[Column(type: 'datetime', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;

    #[Column(type: 'datetime', name: 'updated_at', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;

    public array $errors = [];

    public function load(array $data): bool
    {
        $this->nama = trim((string)($data['nama'] ?? $this->nama));
        $this->urutan = isset($data['urutan']) ? (int)$data['urutan'] : $this->urutan;
        $this->progress = isset($data['progress']) ? (int)$data['progress'] : $this->progress;
        if (isset($data['entitas_id'])) {
            $this->entitasId = (int)$data['entitas_id'];
        }
        return !empty($data);
    }

    public function validate(): bool
    {
        $this->errors = [];
        if ($this->nama === '') {
            $this->errors['nama'] = 'Nama kolom tidak boleh kosong.';
        }
        if ($this->progress < 0 || $this->progress > 100) {
            $this->errors['progress'] = 'Nilai progress harus berkisar antara 0 dan 100.';
        }
        return empty($this->errors);
    }
}
