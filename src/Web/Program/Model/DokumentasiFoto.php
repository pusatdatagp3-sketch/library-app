<?php

declare(strict_types=1);

namespace App\Web\Program\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\BelongsTo;

#[Entity(role: 'entitas_program_dokumentasi_foto', table: 'entitas_program_dokumentasi_foto')]
class DokumentasiFoto
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'integer', name: 'dokumentasi_id')]
    public int $dokumentasiId;

    #[BelongsTo(target: Dokumentasi::class, innerKey: 'dokumentasiId', fkAction: 'CASCADE', load: 'lazy')]
    public ?Dokumentasi $dokumentasi = null;

    #[Column(type: 'string(255)', name: 'file_path')]
    public string $filePath = '';

    #[Column(type: 'datetime', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;

    #[Column(type: 'datetime', name: 'updated_at', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;
}
