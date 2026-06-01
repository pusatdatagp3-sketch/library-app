<?php

declare(strict_types=1);

namespace App\Web\Program\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\Annotated\Annotation\Relation\HasMany;

#[Entity(role: 'entitas_program_dokumentasi', table: 'entitas_program_dokumentasi')]
class Dokumentasi
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'integer', name: 'program_id')]
    public int $programId;

    #[BelongsTo(target: Program::class, innerKey: 'programId', fkAction: 'CASCADE', load: 'eager')]
    public ?Program $program = null;

    #[HasMany(target: DokumentasiFoto::class, outerKey: 'dokumentasiId', load: 'eager')]
    public array $fotos = [];

    #[Column(type: 'string(255)')]
    public string $judul = '';

    #[Column(type: 'datetime', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;

    #[Column(type: 'datetime', name: 'updated_at', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;

    public array $errors = [];

    public function load(array $data): bool
    {
        $this->judul = trim((string)($data['judul'] ?? $this->judul));
        if (isset($data['program_id'])) {
            $this->programId = (int)$data['program_id'];
        }
        return !empty($data);
    }

    public function validate(): bool
    {
        $this->errors = [];
        if ($this->judul === '') {
            $this->errors['judul'] = 'Judul tidak boleh kosong.';
        }
        return empty($this->errors);
    }
}
