<?php

declare(strict_types=1);

namespace App\Web\Staf\Model;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

/**
 * Cycle ORM Entity untuk tabel master list_staf.
 * Menggunakan atribut PHP 8.4 dan Cycle Annotated Entity.
 */
#[Entity(
    role: 'list_staf',
    table: 'list_staf',
    repository: StafRepository::class
)]
class StafEntity
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'string(150)', name: 'nama_staf')]
    public string $nama_staf = '';

    /**
     * Divisi staf, hanya 2 opsi yang valid: 'Library' atau 'Staff'.
     */
    #[Column(type: 'string(50)', name: 'divisi', default: 'Library')]
    public string $divisi = 'Library';

    #[Column(type: 'boolean', name: 'is_active', default: true)]
    public bool $is_active = true;

    public function __construct(string $nama_staf = '', string $divisi = 'Library', bool $is_active = true)
    {
        $this->nama_staf = $nama_staf;
        $this->divisi = $divisi === 'Staff' ? 'Staff' : 'Library';
        $this->is_active = $is_active;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nama_staf' => $this->nama_staf,
            'divisi' => $this->divisi,
            'is_active' => $this->is_active,
        ];
    }
}
