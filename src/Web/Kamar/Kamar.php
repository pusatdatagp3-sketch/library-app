<?php

declare(strict_types=1);

namespace App\Web\Kamar;

final class Kamar
{
    public function __construct(
        public ?int $id,
        public string $namaKamar,
        public int $kapasitas
    ) {
    }
}
