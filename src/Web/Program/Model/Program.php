<?php

declare(strict_types=1);

namespace App\Web\Program\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use App\Web\Entitas\Model\Entitas;
use App\Web\Entitas\Model\AnggotaEntitas;

#[Entity(role: 'entitas_program', table: 'entitas_program', repository: ProgramRepository::class)]
class Program
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'integer', name: 'entitas_id')]
    public int $entitasId;

    #[BelongsTo(target: Entitas::class, innerKey: 'entitasId', fkAction: 'CASCADE', load: 'eager')]
    public ?Entitas $entitas = null;

    #[Column(type: 'string(255)', name: 'nama_program')]
    public string $namaProgram = '';

    #[Column(type: 'string(50)', nullable: true)]
    public ?string $periode = null;

    #[Column(type: 'integer', name: 'penanggung_jawab_id', nullable: true)]
    public ?int $penanggungJawabId = null;

    #[BelongsTo(target: AnggotaEntitas::class, innerKey: 'penanggungJawabId', fkAction: 'SET NULL', nullable: true, load: 'eager')]
    public ?AnggotaEntitas $penanggungJawab = null;

    #[Column(type: 'text', nullable: true)]
    public ?string $tupoksi = null;

    #[Column(type: 'string(255)', default: 'active')]
    public string $status = 'active';

    #[Column(type: 'datetime', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;

    #[Column(type: 'datetime', name: 'updated_at', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;

    public array $errors = [];

    public function load(array $data): bool
    {
        $this->namaProgram = trim((string)($data['nama_program'] ?? $this->namaProgram));
        $this->periode = isset($data['periode']) ? trim((string)$data['periode']) : $this->periode;
        $this->tupoksi = isset($data['tupoksi']) ? trim((string)$data['tupoksi']) : $this->tupoksi;
        $this->status = trim((string)($data['status'] ?? $this->status));
        
        if (isset($data['entitas_id'])) {
            $this->entitasId = (int)$data['entitas_id'];
        }
        if (isset($data['penanggung_jawab_id'])) {
            $this->penanggungJawabId = $data['penanggung_jawab_id'] ? (int)$data['penanggung_jawab_id'] : null;
        }
        return !empty($data);
    }

    public function validate(): bool
    {
        $this->errors = [];
        if ($this->namaProgram === '') {
            $this->errors['nama_program'] = 'Nama program tidak boleh kosong.';
        }
        if ($this->periode !== null && !in_array($this->periode, ['mingguan', 'bulanan', 'semesteran', 'tahunan'], true)) {
            $this->errors['periode'] = 'Periode tidak valid.';
        }
        return empty($this->errors);
    }
}
