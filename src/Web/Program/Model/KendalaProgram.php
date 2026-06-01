<?php

declare(strict_types=1);

namespace App\Web\Program\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\Annotated\Annotation\Relation\HasMany;

#[Entity(role: 'entitas_program_kendala', table: 'entitas_program_kendala')]
class KendalaProgram
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'integer', name: 'program_id')]
    public int $programId;

    #[BelongsTo(target: Program::class, innerKey: 'programId', fkAction: 'CASCADE', load: 'eager')]
    public ?Program $program = null;

    #[HasMany(target: KendalaProgramFoto::class, outerKey: 'kendalaId', load: 'eager')]
    public array $fotos = [];

    #[Column(type: 'string(255)')]
    public string $kendala = '';

    #[Column(type: 'text', name: 'solusi_singkat', nullable: true)]
    public ?string $solusiSingkat = null;

    #[Column(type: 'string(50)')]
    public string $jenis = 'terbuka';

    #[Column(type: 'datetime', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;

    #[Column(type: 'datetime', name: 'updated_at', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;

    public array $errors = [];

    public function load(array $data): bool
    {
        $this->kendala = trim((string)($data['kendala'] ?? $this->kendala));
        $this->solusiSingkat = isset($data['solusi_singkat']) && trim((string)$data['solusi_singkat']) !== ''
            ? trim((string)$data['solusi_singkat'])
            : null;
        $this->jenis = trim((string)($data['jenis'] ?? $this->jenis));
        if (isset($data['program_id'])) {
            $this->programId = (int)$data['program_id'];
        }
        return !empty($data);
    }

    public function validate(): bool
    {
        $this->errors = [];
        if ($this->kendala === '') {
            $this->errors['kendala'] = 'Kendala tidak boleh kosong.';
        }
        if (!in_array($this->jenis, ['terbuka', 'tertutup'], true)) {
            $this->errors['jenis'] = 'Jenis kendala tidak valid.';
        }
        return empty($this->errors);
    }
}
