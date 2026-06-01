<?php

declare(strict_types=1);

namespace App\Web\Task\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\BelongsTo;

#[Entity(role: 'entitas_program_kanban_log', table: 'entitas_program_kanban_log')]
class TaskProgressLog
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'integer', name: 'task_id')]
    public int $taskId;

    #[BelongsTo(target: Task::class, innerKey: 'taskId', fkAction: 'CASCADE', load: 'eager')]
    public ?Task $task = null;

    #[Column(type: 'string(255)', nullable: true)]
    public ?string $keterangan = null;

    #[Column(type: 'string(500)', name: 'bukti_foto', nullable: true)]
    public ?string $buktiFoto = null;

    #[Column(type: 'text', nullable: true)]
    public ?string $alasan = null;

    #[Column(type: 'integer', name: 'progress_sebelumnya', nullable: true)]
    public ?int $progressSebelumnya = null;

    #[Column(type: 'integer', name: 'progress_baru', nullable: true)]
    public ?int $progressBaru = null;

    #[Column(type: 'datetime', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;
}
