<?php

declare(strict_types=1);

namespace App\Web\Entitas\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\BelongsTo;

#[Entity(role: 'entitas_anggota', table: 'entitas_anggota', repository: AnggotaEntitasRepository::class)]
class AnggotaEntitas
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'integer', name: 'entitas_id')]
    public int $entitasId;

    #[BelongsTo(target: Entitas::class, innerKey: 'entitasId', fkAction: 'CASCADE', load: 'eager')]
    public ?Entitas $entitas = null;

    #[Column(type: 'integer', name: 'user_id', nullable: true)]
    public ?int $userId = null;

    #[Column(type: 'string(255)', name: 'nama_anggota')]
    public string $namaAnggota = '';

    #[Column(type: 'string(4)', name: 'kode_kampus', nullable: true)]
    public ?string $kodeKampus = null;

    #[Column(type: 'string(255)', nullable: true)]
    public ?string $jabatan = null;

    #[Column(type: 'datetime', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;

    #[Column(type: 'datetime', name: 'updated_at', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;

    public array $errors = [];

    public function load(array $data): bool
    {
        $this->namaAnggota = trim((string)($data['nama_anggota'] ?? $this->namaAnggota));
        $this->jabatan = isset($data['jabatan']) ? trim((string)$data['jabatan']) : $this->jabatan;
        if (isset($data['entitas_id'])) {
            $this->entitasId = (int)$data['entitas_id'];
        }
        if (isset($data['user_id'])) {
            $this->userId = $data['user_id'] ? (int)$data['user_id'] : null;
        }
        return !empty($data);
    }

    public function validate(): bool
    {
        $this->errors = [];
        if ($this->namaAnggota === '') {
            $this->errors['nama_anggota'] = 'Nama anggota tidak boleh kosong.';
        }
        return empty($this->errors);
    }
}
