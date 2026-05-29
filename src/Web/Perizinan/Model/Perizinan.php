<?php

declare(strict_types=1);

namespace App\Web\Perizinan\Model;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;

#[Entity(role: 'perizinan', table: 'perizinan', repository: PerizinanRepository::class)]
class Perizinan
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'string(50)')]
    public string $kemana = '';

    #[Column(type: 'string(20)', name: 'sama_siapa')]
    public string $samaSiapa = '';

    #[Column(type: 'string(20)', name: 'berapa_orang')]
    public string $berapaOrang = '';

    public function __construct(
        ?int $id = null,
        string $kemana = '',
        string $samaSiapa = '',
        string $berapaOrang = ''
    ) {
        $this->id = $id;
        $this->kemana = $kemana;
        $this->samaSiapa = $samaSiapa;
        $this->berapaOrang = $berapaOrang;
    }
}
