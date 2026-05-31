<?php

declare(strict_types=1);

namespace App\Web\Task\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use App\Web\Program\Model\Program;
use App\Web\Kanban\Model\KanbanColumn;
use App\Web\Entitas\Model\AnggotaEntitas;

#[Entity(role: 'task', table: 'tasks', repository: TaskRepository::class)]
class Task
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'integer', name: 'program_id')]
    public int $programId;

    #[BelongsTo(target: Program::class, innerKey: 'programId', fkAction: 'CASCADE', load: 'eager')]
    public ?Program $program = null;

    #[Column(type: 'integer', name: 'kanban_column_id', nullable: true)]
    public ?int $kanbanColumnId = null;

    #[BelongsTo(target: KanbanColumn::class, innerKey: 'kanbanColumnId', fkAction: 'SET NULL', nullable: true, load: 'eager')]
    public ?KanbanColumn $kanbanColumn = null;

    #[Column(type: 'string(255)')]
    public string $judul = '';

    #[Column(type: 'text', nullable: true)]
    public ?string $deskripsi = null;

    #[Column(type: 'integer', name: 'assigned_to', nullable: true)]
    public ?int $assignedTo = null;

    #[BelongsTo(target: AnggotaEntitas::class, innerKey: 'assignedTo', fkAction: 'SET NULL', nullable: true, load: 'eager')]
    public ?AnggotaEntitas $assignedUser = null;

    #[Column(type: 'integer', default: 0)]
    public int $progress = 0;

    #[Column(type: 'date', nullable: true)]
    public ?\DateTimeImmutable $deadline = null;

    #[Column(type: 'integer', default: 0)]
    public int $urutan = 0;

    #[Column(type: 'datetime', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;

    #[Column(type: 'datetime', name: 'updated_at', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;

    public array $errors = [];

    public function load(array $data): bool
    {
        $this->judul = trim((string)($data['judul'] ?? $this->judul));
        $this->deskripsi = isset($data['deskripsi']) ? trim((string)$data['deskripsi']) : $this->deskripsi;
        $this->progress = isset($data['progress']) ? (int)$data['progress'] : $this->progress;
        $this->urutan = isset($data['urutan']) ? (int)$data['urutan'] : $this->urutan;
        
        if (isset($data['program_id'])) {
            $this->programId = (int)$data['program_id'];
        }
        if (isset($data['kanban_column_id'])) {
            $this->kanbanColumnId = $data['kanban_column_id'] ? (int)$data['kanban_column_id'] : null;
        }
        if (isset($data['assigned_to'])) {
            $this->assignedTo = $data['assigned_to'] ? (int)$data['assigned_to'] : null;
        }
        if (isset($data['deadline'])) {
            $this->deadline = $data['deadline'] ? new \DateTimeImmutable($data['deadline']) : null;
        }
        return !empty($data);
    }

    public function validate(): bool
    {
        $this->errors = [];
        if ($this->judul === '') {
            $this->errors['judul'] = 'Judul task tidak boleh kosong.';
        }
        if ($this->progress < 0 || $this->progress > 100) {
            $this->errors['progress'] = 'Progress harus bernilai antara 0 dan 100.';
        }
        return empty($this->errors);
    }
}
