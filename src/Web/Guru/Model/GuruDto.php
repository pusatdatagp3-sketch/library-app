<?php

declare(strict_types=1);

namespace App\Web\Guru\Model;

final class GuruDto
{
    public function __construct(
        public string $stambuk,
        public string $nama,
        public string $daerah,
        public string $konsulat,
        public string $email,
        public string $noTelp,
        public ?int $kamarId = null
    ) {
    }
}
