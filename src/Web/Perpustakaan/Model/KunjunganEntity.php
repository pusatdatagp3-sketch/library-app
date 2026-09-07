<?php

declare(strict_types=1);

namespace App\Web\Perpustakaan\Model;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Cycle ORM Entity untuk tabel record_perpustakaan_kunjungan.
 * Mengikuti aturan tabel transaksi berawalan record_ dan mapping attribute Cycle ORM v2.
 */
#[Entity(
    role: 'record_perpustakaan_kunjungan',
    table: 'record_perpustakaan_kunjungan',
    repository: KunjunganRepository::class
)]
class KunjunganEntity
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'integer', name: 'santri_id')]
    public int $santri_id = 0;

    #[Column(type: 'string(30)', name: 'stambuk')]
    public string $stambuk = '';

    #[Column(type: 'string(150)', name: 'nama_santri', nullable: true)]
    public ?string $nama_santri = null;

    #[Column(type: 'string(50)', name: 'kelas', nullable: true)]
    public ?string $kelas = null;

    #[Column(type: 'string(100)', name: 'rayon', nullable: true)]
    public ?string $rayon = null;

    #[Column(type: 'string(100)', name: 'konsulat', nullable: true)]
    public ?string $konsulat = null;

    #[Column(type: 'datetime', name: 'waktu_kunjungan')]
    public ?DateTimeImmutable $waktu_kunjungan = null;

    #[Column(type: 'string(100)', name: 'penginput')]
    public string $penginput = 'system';

    #[Column(type: 'datetime', name: 'created_at')]
    public ?DateTimeImmutable $created_at = null;

    public function __construct()
    {
        $this->waktu_kunjungan = new DateTimeImmutable();
        $this->created_at = new DateTimeImmutable();
    }

    /**
     * Helper accessor untuk mendukung pemanggilan camelCase maupun snake_case.
     */
    public function __get(string $name): mixed
    {
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
        return $this->$snake ?? null;
    }

    /**
     * Helper mutator untuk mendukung pengisian camelCase maupun snake_case.
     */
    public function __set(string $name, mixed $value): void
    {
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
        $this->$snake = $value;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'santri_id' => $this->santri_id,
            'stambuk' => $this->stambuk,
            'nama_santri' => $this->nama_santri,
            'kelas' => $this->kelas,
            'rayon' => $this->rayon,
            'konsulat' => $this->konsulat,
            'waktu_kunjungan' => $this->waktu_kunjungan?->format('Y-m-d H:i:s'),
            'penginput' => $this->penginput,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
