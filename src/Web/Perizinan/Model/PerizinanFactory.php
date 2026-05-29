<?php

declare(strict_types=1);

namespace App\Web\Perizinan\Model;

final class PerizinanFactory
{
    public static function createFromRow(array $row): Perizinan
    {
        return new Perizinan(
            id: $row['id'] !== null ? (int) $row['id'] : null,
            kemana: $row['kemana'] !== null ? (string) $row['kemana'] : null,
            samaSiapa: $row['sama_siapa'] !== null ? (string) $row['sama_siapa'] : null,
            berapaOrang: $row['berapa_orang'] !== null ? (string) $row['berapa_orang'] : null
        );
    }
}
