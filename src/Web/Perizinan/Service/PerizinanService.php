<?php

declare(strict_types=1);

namespace App\Web\Perizinan\Service;

use App\Web\Perizinan\Model\Perizinan;
use App\Web\Perizinan\Model\PerizinanDto;
use App\Web\Perizinan\Model\PerizinanRepository;
use Cycle\ORM\EntityManagerInterface;

final class PerizinanService
{
    public function __construct(
        private PerizinanRepository $perizinanRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return Perizinan[]
     */
    public function getAllPerizinan(array $filters = []): array
    {
        return $this->perizinanRepository->getAllFiltered($filters);
    }

    public function getPerizinanById(int $id): ?Perizinan
    {
        return $this->perizinanRepository->getById($id);
    }

    public function createPerizinan(array $rawData, array &$errors): bool
    {
        $dto = $this->createDtoFromRaw($rawData);
        $errors = $this->validateDto($dto);

        if (!empty($errors)) {
            return false;
        }

        try {
            $entity = new Perizinan(null, $dto->kemana, $dto->samaSiapa, $dto->berapaOrang);
            $this->entityManager->persist($entity)->run();
            return true;
        } catch (\Throwable $e) {
            $errors['general'] = 'Gagal menyimpan data: ' . $e->getMessage();
            return false;
        }
    }

    public function updatePerizinan(int $id, array $rawData, array &$errors): bool
    {
        $dto = $this->createDtoFromRaw($rawData);
        $errors = $this->validateDto($dto);

        if (!empty($errors)) {
            return false;
        }

        $entity = $this->perizinanRepository->getById($id);
        if ($entity === null) {
            $errors['general'] = 'Data tidak ditemukan.';
            return false;
        }

        try {
            $entity->kemana = $dto->kemana;
            $entity->samaSiapa = $dto->samaSiapa;
            $entity->berapaOrang = $dto->berapaOrang;
            $this->entityManager->persist($entity)->run();
            return true;
        } catch (\Throwable $e) {
            $errors['general'] = 'Gagal memperbarui data: ' . $e->getMessage();
            return false;
        }
    }

    public function deletePerizinan(int $id): bool
    {
        $entity = $this->perizinanRepository->getById($id);
        if ($entity === null) {
            return false;
        }
        try {
            $this->entityManager->delete($entity)->run();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function createDtoFromRaw(array $rawData): PerizinanDto
    {
        $kemana = trim((string) ($rawData['kemana'] ?? ''));
        $samaSiapa = trim((string) ($rawData['sama_siapa'] ?? ''));
        $berapaOrang = trim((string) ($rawData['berapa_orang'] ?? ''));

        return new PerizinanDto($kemana, $samaSiapa, $berapaOrang);
    }

    private function validateDto(PerizinanDto $dto): array
    {
        $errors = [];
        if ($dto->kemana === '') {
            $errors['kemana'] = 'Kemana tidak boleh kosong.';
        }
        if ($dto->samaSiapa === '') {
            $errors['sama_siapa'] = 'Sama siapa tidak boleh kosong.';
        }
        if ($dto->berapaOrang === '') {
            $errors['berapa_orang'] = 'Berapa orang tidak boleh kosong.';
        }

        return $errors;
    }
}
