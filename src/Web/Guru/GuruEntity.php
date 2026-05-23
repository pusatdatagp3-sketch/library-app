<?php

declare(strict_types=1);

namespace App\Web\Guru;

final class GuruEntity
{
    public function __construct(
        public ?int $kdg,
        public string $stambuk,
        public string $nama,
        public string $daerah,
        public string $konsulat,
        public string $email,
        public string $noTelp,
        public ?int $kamarId = null,
        public ?string $namaKamar = null
    ) {
    }
}
