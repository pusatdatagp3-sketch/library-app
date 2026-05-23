<?php

declare(strict_types=1);

namespace App\Web\Guru;

final class GuruFactory
{
    public static function createFromRow(array $row): GuruEntity
    {
        return new GuruEntity(
            kdg: isset($row['kdg']) ? (int) $row['kdg'] : null,
            stambuk: (string) ($row['stambuk'] ?? ''),
            nama: (string) ($row['nama'] ?? ''),
            daerah: (string) ($row['daerah'] ?? ''),
            konsulat: (string) ($row['konsulat'] ?? ''),
            email: (string) ($row['email'] ?? ''),
            noTelp: (string) ($row['no_telp'] ?? ''),
            kamarId: isset($row['kamar_id']) && $row['kamar_id'] !== '' ? (int) $row['kamar_id'] : null,
            namaKamar: isset($row['nama_kamar']) ? (string) $row['nama_kamar'] : null
        );
    }
}
