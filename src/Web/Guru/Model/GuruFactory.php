<?php

declare(strict_types=1);

namespace App\Web\Guru\Model;

final class GuruFactory
{
    public static function createFromRow(array $row): GuruEntity
    {
        return new GuruEntity(
            kdg: isset($row['kdg']) ? (int) $row['kdg'] : null,
            stambuk: (string) ($row['stambuk'] ?? ''),
            nama: (string) ($row['nama'] ?? ''),
            daerah: (string) ($row['daerah'] ?? ''),
            konsulatId: isset($row['konsulat_id']) && $row['konsulat_id'] !== '' ? (int) $row['konsulat_id'] : null,
            email: (string) ($row['email'] ?? ''),
            noTelp: (string) ($row['no_telp'] ?? ''),
            kamarId: isset($row['kamar_id']) && $row['kamar_id'] !== '' ? (int) $row['kamar_id'] : null,
        );
    }
}
