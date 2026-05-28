<?php

declare(strict_types=1);

namespace App\Web\Kamar\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;

#[Entity(role: 'kamar', table: 'kamar', repository: KamarRepository::class)]
class Kamar
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'string(100)', name: 'nama_kamar', unique: true)]
    public string $namaKamar = '';

    #[Column(type: 'integer')]
    public int $kapasitas = 0;

    public function __construct(
        ?int $id = null,
        string $namaKamar = '',
        int $kapasitas = 0
    ) {
        $this->id = $id;
        $this->namaKamar = $namaKamar;
        $this->kapasitas = $kapasitas;
    }
}
