<?php

declare(strict_types=1);

namespace App\Web\Guru\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use App\Web\Kamar\Model\Kamar;
use App\Web\Konsulat\Model\Konsulat;

#[Entity(role: 'guru', table: 'guru', repository: GuruRepository::class)]
class GuruEntity
{
    #[Column(type: 'primary')]
    public ?int $kdg = null;

    #[Column(type: 'string(50)')]
    public string $stambuk = '';

    #[Column(type: 'string(150)')]
    public string $nama = '';

    #[Column(type: 'string(100)')]
    public string $daerah = '';

    #[Column(type: 'integer', name: 'konsulat_id', nullable: true)]
    public ?int $konsulatId = null;

    #[BelongsTo(target: Konsulat::class, innerKey: 'konsulatId', fkAction: 'SET NULL', nullable: true, load: 'eager')]
    public ?Konsulat $konsulat = null;

    #[Column(type: 'string(100)')]
    public string $email = '';

    #[Column(type: 'string(20)', name: 'no_telp')]
    public string $noTelp = '';

    #[Column(type: 'integer', name: 'kamar_id', nullable: true)]
    public ?int $kamarId = null;

    #[BelongsTo(target: Kamar::class, innerKey: 'kamarId', fkAction: 'SET NULL', nullable: true, load: 'eager')]
    public ?Kamar $kamar = null;

    public function __construct(
        ?int $kdg = null,
        string $stambuk = '',
        string $nama = '',
        string $daerah = '',
        ?int $konsulatId = null,
        string $email = '',
        string $noTelp = '',
        ?int $kamarId = null
    ) {
        $this->kdg = $kdg;
        $this->stambuk = $stambuk;
        $this->nama = $nama;
        $this->daerah = $daerah;
        $this->konsulatId = $konsulatId;
        $this->email = $email;
        $this->noTelp = $noTelp;
        $this->kamarId = $kamarId;
    }

    public function __get(string $name)
    {
        if ($name === 'namaKamar') {
            return $this->kamar?->namaKamar;
        }
        if ($name === 'namaKonsulat') {
            return $this->konsulat?->konsulat;
        }
        return null;
    }

    public function __isset(string $name): bool
    {
        if ($name === 'namaKamar') {
            return $this->kamar !== null;
        }
        if ($name === 'namaKonsulat') {
            return $this->konsulat !== null;
        }
        return false;
    }
}
