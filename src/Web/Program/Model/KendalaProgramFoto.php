<?php

declare(strict_types=1);

namespace App\Web\Program\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\BelongsTo;

#[Entity(role: 'entitas_program_kendala_foto', table: 'entitas_program_kendala_foto')]
class KendalaProgramFoto
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'integer', name: 'kendala_id')]
    public int $kendalaId;

    #[BelongsTo(target: KendalaProgram::class, innerKey: 'kendalaId', fkAction: 'CASCADE', load: 'lazy')]
    public ?KendalaProgram $kendala = null;

    #[Column(type: 'string(255)', name: 'file_path')]
    public string $filePath = '';

    #[Column(type: 'datetime', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;

    #[Column(type: 'datetime', name: 'updated_at', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;
}
