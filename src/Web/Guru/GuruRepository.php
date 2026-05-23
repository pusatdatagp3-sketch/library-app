<?php

declare(strict_types=1);

namespace App\Web\Guru;

use App\Environment;
use PDO;

final class GuruRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $host = Environment::dbHost();
        $port = Environment::dbPort();
        $dbname = Environment::dbName();
        $user = Environment::dbUser();
        $password = Environment::dbPassword();

        // Hubungkan ke server MySQL terlebih dahulu (tanpa dbname agar aman jika database belum ada)
        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $this->pdo->exec("USE `{$dbname}`");

        $this->initializeTable();
    }

    private function initializeTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `guru` (
                `kdg` INT AUTO_INCREMENT PRIMARY KEY,
                `stambuk` VARCHAR(50) NOT NULL,
                `nama` VARCHAR(150) NOT NULL,
                `daerah` VARCHAR(100) NOT NULL,
                `konsulat` VARCHAR(100) NOT NULL,
                `email` VARCHAR(100) NOT NULL,
                `no_telp` VARCHAR(20) NOT NULL,
                INDEX (`stambuk`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * @return GuruEntity[]
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM `guru` ORDER BY `kdg` DESC");
        $rows = $stmt->fetchAll();

        $entities = [];
        foreach ($rows as $row) {
            $entities[] = GuruFactory::createFromRow($row);
        }
        return $entities;
    }

    public function getById(int $kdg): ?GuruEntity
    {
        $stmt = $this->pdo->prepare("SELECT * FROM `guru` WHERE `kdg` = :kdg");
        $stmt->execute(['kdg' => $kdg]);
        $row = $stmt->fetch();
        return $row ? GuruFactory::createFromRow($row) : null;
    }

    public function create(GuruDto $dto): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO `guru` (`stambuk`, `nama`, `daerah`, `konsulat`, `email`, `no_telp`)
            VALUES (:stambuk, :nama, :daerah, :konsulat, :email, :no_telp)
        ");
        return $stmt->execute([
            'stambuk' => $dto->stambuk,
            'nama' => $dto->nama,
            'daerah' => $dto->daerah,
            'konsulat' => $dto->konsulat,
            'email' => $dto->email,
            'no_telp' => $dto->noTelp,
        ]);
    }

    public function update(int $kdg, GuruDto $dto): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE `guru`
            SET `stambuk` = :stambuk,
                `nama` = :nama,
                `daerah` = :daerah,
                `konsulat` = :konsulat,
                `email` = :email,
                `no_telp` = :no_telp
            WHERE `kdg` = :kdg
        ");
        return $stmt->execute([
            'kdg' => $kdg,
            'stambuk' => $dto->stambuk,
            'nama' => $dto->nama,
            'daerah' => $dto->daerah,
            'konsulat' => $dto->konsulat,
            'email' => $dto->email,
            'no_telp' => $dto->noTelp,
        ]);
    }

    public function delete(int $kdg): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM `guru` WHERE `kdg` = :kdg");
        return $stmt->execute(['kdg' => $kdg]);
    }
}
