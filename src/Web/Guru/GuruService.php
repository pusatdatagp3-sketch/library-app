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

    private function createDtoFromRaw(array $rawData): GuruDto
    {
        $stambuk = trim($rawData['stambuk'] ?? '');
        $nama = trim($rawData['nama'] ?? '');
        $daerah = trim($rawData['daerah'] ?? '');
        $konsulat = trim($rawData['konsulat'] ?? '');
        $email = trim($rawData['email'] ?? '');
        
        // Membersihkan nomor telepon menggunakan PhoneHelper
        $noTelp = isset($rawData['no_telp']) ? PhoneHelper::format($rawData['no_telp']) : '';

        return new GuruDto($stambuk, $nama, $daerah, $konsulat, $email, $noTelp);
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
