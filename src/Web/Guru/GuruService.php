<?php

declare(strict_types=1);

namespace App\Web\Guru;

final class GuruService
{
    public function __construct(
        private GuruRepository $guruRepository
    ) {
    }

    /**
     * @return GuruEntity[]
     */
    public function getAllGuru(): array
    {
        return $this->guruRepository->getAll();
    }

    public function getGuruById(int $kdg): ?GuruEntity
    {
        return $this->guruRepository->getById($kdg);
    }

    public function createGuru(array $rawData, array &$errors): bool
    {
        $dto = $this->createDtoFromRaw($rawData);
        $errors = $this->validateDto($dto);

        if (!empty($errors)) {
            return false;
        }

        return $this->guruRepository->create($dto);
    }

    public function updateGuru(int $kdg, array $rawData, array &$errors): bool
    {
        $dto = $this->createDtoFromRaw($rawData);
        $errors = $this->validateDto($dto);

        if (!empty($errors)) {
            return false;
        }

        return $this->guruRepository->update($kdg, $dto);
    }

    public function deleteGuru(int $kdg): bool
    {
        return $this->guruRepository->delete($kdg);
    }

    public function importExcel(string $filePath, array &$errors): int
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        } catch (\Throwable $e) {
            $errors[] = "Gagal membaca file Excel: " . $e->getMessage();
            return 0;
        }

        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $successCount = 0;
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue; // Skip header row
            }

            // Skip entirely empty rows
            $hasData = false;
            foreach ($row as $cell) {
                if (trim((string)$cell) !== '') {
                    $hasData = true;
                    break;
                }
            }
            if (!$hasData) {
                continue;
            }

            $stambuk = trim((string)($row[0] ?? ''));
            $nama = trim((string)($row[1] ?? ''));
            $daerah = trim((string)($row[2] ?? ''));
            $konsulat = trim((string)($row[3] ?? ''));
            $email = trim((string)($row[4] ?? ''));
            $noTelp = PhoneHelper::format(trim((string)($row[5] ?? '')));

            $rowNum = $index + 1;

            if ($stambuk === '') {
                $errors[] = "Baris {$rowNum}: Stambuk tidak boleh kosong.";
                continue;
            }
            if ($nama === '') {
                $errors[] = "Baris {$rowNum}: Nama tidak boleh kosong.";
                continue;
            }
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Baris {$rowNum}: Format email '{$email}' tidak valid.";
                continue;
            }

            $dto = new GuruDto($stambuk, $nama, $daerah, $konsulat, $email, $noTelp);

            $existing = $this->guruRepository->getByStambuk($stambuk);
            if ($existing !== null) {
                $this->guruRepository->update((int)$existing->kdg, $dto);
            } else {
                $this->guruRepository->create($dto);
            }

            $successCount++;
        }

        return $successCount;
    }

    private function createDtoFromRaw(array $rawData): GuruDto
    {
        $stambuk = trim($rawData['stambuk'] ?? '');
        $nama = trim($rawData['nama'] ?? '');
        $daerah = trim($rawData['daerah'] ?? '');
        $konsulat = trim($rawData['konsulat'] ?? '');
        $email = trim($rawData['email'] ?? '');
        
        // Membersihkan nomor telepon menggunakan PhoneHelper
        $noTelp = isset($rawData['no_telp']) ? PhoneHelper::format($rawData['no_telp']) : '';
        $kamarId = isset($rawData['kamar_id']) && $rawData['kamar_id'] !== '' ? (int) $rawData['kamar_id'] : null;

        return new GuruDto($stambuk, $nama, $daerah, $konsulat, $email, $noTelp, $kamarId);
    }

    private function validateDto(GuruDto $dto): array
    {
        $errors = [];

        if ($dto->stambuk === '') {
            $errors['stambuk'] = 'Stambuk tidak boleh kosong.';
        }
        if ($dto->nama === '') {
            $errors['nama'] = 'Nama tidak boleh kosong.';
        }
        if ($dto->daerah === '') {
            $errors['daerah'] = 'Daerah tidak boleh kosong.';
        }
        if ($dto->konsulat === '') {
            $errors['konsulat'] = 'Konsulat tidak boleh kosong.';
        }
        if ($dto->email === '') {
            $errors['email'] = 'Email tidak boleh kosong.';
        } elseif (!filter_var($dto->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        }
        if ($dto->noTelp === '') {
            $errors['no_telp'] = 'Nomor telefon tidak boleh kosong.';
        }

        return $errors;
    }
}
