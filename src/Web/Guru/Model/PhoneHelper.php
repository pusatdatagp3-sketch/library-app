<?php

declare(strict_types=1);

namespace App\Web\Guru\Model;

final class PhoneHelper
{
    /**
     * Membersihkan nomor telepon dengan menyisakan hanya angka.
     */
    public static function format(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
