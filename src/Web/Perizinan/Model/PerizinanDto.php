<?php

declare(strict_types=1);

namespace App\Web\Perizinan\Model;

final class PerizinanDto
{
    public function __construct(
        public string $kemana,
        public string $samaSiapa,
        public string $berapaOrang
    ) {
    }
}
