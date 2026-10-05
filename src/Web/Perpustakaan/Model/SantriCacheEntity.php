<?php

declare(strict_types=1);

namespace App\Web\Perpustakaan\Model;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table\Index;
use DateTimeImmutable;

/**
 * Cycle ORM Entity untuk tabel santri_cache.
 * Menyimpan cache data santri dari SIDAS untuk keperluan pencatatan presensi offline/resilient.
 */
#[Entity(
    role: 'santri_cache',
    table: 'santri_cache'
)]
#[Index(columns: ['stambuk'], name: 'idx_santri_cache_stambuk')]
#[Index(columns: ['santri_id'], name: 'idx_santri_cache_santri_id')]
class SantriCacheEntity
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'integer', name: 'santri_id')]
    public int $santri_id = 0;

    #[Column(type: 'integer', name: 'kds', nullable: true)]
    public ?int $kds = null;

    #[Column(type: 'string(30)', name: 'stambuk')]
    public string $stambuk = '';

    #[Column(type: 'string(150)', name: 'nama')]
    public string $nama = '';

    #[Column(type: 'string(50)', name: 'kelas', nullable: true)]
    public ?string $kelas = null;

    #[Column(type: 'string(100)', name: 'rayon', nullable: true)]
    public ?string $rayon = null;

    #[Column(type: 'string(50)', name: 'kamar', nullable: true)]
    public ?string $kamar = null;

    #[Column(type: 'string(100)', name: 'konsulat', nullable: true)]
    public ?string $konsulat = null;

    #[Column(type: 'string(50)', name: 'kampus', nullable: true)]
    public ?string $kampus = null;

    #[Column(type: 'string(20)', name: 'jenis_kelamin', nullable: true)]
    public ?string $jenis_kelamin = null;

    #[Column(type: 'string(50)', name: 'status', nullable: true)]
    public ?string $status = null;

    #[Column(type: 'boolean', name: 'aktif', default: true)]
    public bool $aktif = true;

    #[Column(type: 'datetime', name: 'synced_at')]
    public ?DateTimeImmutable $synced_at = null;

    public function __construct()
    {
        $this->synced_at = new DateTimeImmutable();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'santri_id' => $this->santri_id,
            'kds' => $this->kds,
            'stambuk' => $this->stambuk,
            'nama' => $this->nama,
            'kelas' => $this->kelas,
            'rayon' => $this->rayon,
            'kamar' => $this->kamar,
            'konsulat' => $this->konsulat,
            'kampus' => $this->kampus,
            'jenis_kelamin' => $this->jenis_kelamin,
            'status' => $this->status,
            'aktif' => $this->aktif,
            'synced_at' => $this->synced_at?->format('Y-m-d H:i:s'),
        ];
    }
}
