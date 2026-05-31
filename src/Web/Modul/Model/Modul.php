<?php

declare(strict_types=1);

namespace App\Web\Modul\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;

#[Entity(role: 'modul', table: 'modul', repository: ModulRepository::class)]
class Modul
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'string(100)', nullable: true)]
    public ?string $nama = null;

    #[Column(type: 'string(50)', nullable: true)]
    public ?string $tipe = null;

    #[Column(type: 'timestamp', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;

    #[Column(type: 'timestamp', name: 'updated_at', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;
}
