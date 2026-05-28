<?php

declare(strict_types=1);

namespace App\Web\Kamar\Model;

final class KamarDto
{
    public function __construct(
        public string $namaKamar,
        public int $kapasitas
    ) {
    }
}
