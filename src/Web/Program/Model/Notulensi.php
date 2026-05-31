<?php

declare(strict_types=1);

namespace App\Web\Program\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\BelongsTo;

#[Entity(role: 'notulensi', table: 'notulensi')]
class Notulensi
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'integer', name: 'program_id')]
    public int $programId;

    #[BelongsTo(target: Program::class, innerKey: 'programId', fkAction: 'CASCADE', load: 'eager')]
    public ?Program $program = null;

    #[Column(type: 'string(255)')]
    public string $judul = '';

    #[Column(type: 'text', nullable: true)]
    public ?string $isi = null;

    #[Column(type: 'string(255)', name: 'file_path', nullable: true)]
    public ?string $filePath = null;

    #[Column(type: 'datetime', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;

    #[Column(type: 'datetime', name: 'updated_at', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;

    public array $errors = [];

    public function load(array $data): bool
    {
        $this->judul = trim((string)($data['judul'] ?? $this->judul));
        $this->isi = isset($data['isi']) ? trim((string)$data['isi']) : $this->isi;
        $this->filePath = isset($data['file_path']) ? trim((string)$data['file_path']) : $this->filePath;
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
