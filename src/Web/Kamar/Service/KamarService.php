<?php

declare(strict_types=1);

namespace App\Web\Kamar\Service;

use App\Web\Kamar\Model\Kamar;
use App\Web\Kamar\Model\KamarDto;
use App\Web\Kamar\Model\KamarRepository;
use Cycle\ORM\EntityManagerInterface;

final class KamarService
{
    public function __construct(
        private KamarRepository $kamarRepository,
        private EntityManagerInterface $entityManager
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
            $kamar = new Kamar(null, $dto->namaKamar, $dto->kapasitas);
            $this->entityManager->persist($kamar)->run();
            return true;
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint')) {
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

        $kamar = $this->kamarRepository->findById($id);
        if ($kamar === null) {
            $errors['general'] = 'Kamar tidak ditemukan.';
            return false;
        }

        try {
            $kamar->namaKamar = $dto->namaKamar;
            $kamar->kapasitas = $dto->kapasitas;
            $this->entityManager->persist($kamar)->run();
            return true;
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint')) {
                $errors['nama_kamar'] = 'Nama kamar ini sudah digunakan.';
            } else {
                $errors['general'] = 'Gagal memperbarui data kamar: ' . $e->getMessage();
            }
            return false;
        }
    }

    public function deleteKamar(int $id): bool
    {
        $kamar = $this->kamarRepository->findById($id);
        if ($kamar === null) {
            return false;
        }
        $this->entityManager->delete($kamar)->run();
        return true;
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
