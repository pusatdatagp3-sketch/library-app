<?php

declare(strict_types=1);

namespace App\Web\Kamar;

final class KamarService
{
    public function __construct(
        private KamarRepository $kamarRepository
    ) {
    }

    /**
     * @return Kamar[]
     */
    public function getAllKamar(): array
    {
        return $this->kamarRepository->findAll();
    }

    public function getKamarById(int $id): ?Kamar
    {
        return $this->kamarRepository->findById($id);
    }

    public function createKamar(array $rawData, array &$errors): bool
    {
        $dto = $this->createDtoFromRaw($rawData);
        $errors = $this->validateDto($dto);

        if (!empty($errors)) {
            return false;
        }

        try {
            return $this->kamarRepository->save($dto);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                $errors['nama_kamar'] = 'Nama kamar ini sudah terdaftar.';
            } else {
                $errors['general'] = 'Gagal menyimpan data kamar: ' . $e->getMessage();
            }
            return false;
        }
    }

    public function updateKamar(int $id, array $rawData, array &$errors): bool
    {
        $dto = $this->createDtoFromRaw($rawData);
        $errors = $this->validateDto($dto);

        if (!empty($errors)) {
            return false;
        }

        try {
            return $this->kamarRepository->save($dto, $id);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                $errors['nama_kamar'] = 'Nama kamar ini sudah digunakan.';
            } else {
                $errors['general'] = 'Gagal memperbarui data kamar: ' . $e->getMessage();
            }
            return false;
        }
    }

    public function deleteKamar(int $id): bool
    {
        return $this->kamarRepository->delete($id);
    }

    private function createDtoFromRaw(array $rawData): KamarDto
    {
        $namaKamar = trim((string) ($rawData['nama_kamar'] ?? ''));
        $kapasitas = (int) ($rawData['kapasitas'] ?? 0);

        return new KamarDto($namaKamar, $kapasitas);
    }

    private function validateDto(KamarDto $dto): array
    {
        $errors = [];

        if ($dto->namaKamar === '') {
            $errors['nama_kamar'] = 'Nama kamar tidak boleh kosong.';
        }
        if ($dto->kapasitas <= 0) {
            $errors['kapasitas'] = 'Kapasitas kamar harus lebih dari 0.';
        }

        return $errors;
    }
}
