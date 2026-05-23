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
        // Pastikan tabel kamar ada terlebih dahulu sebelum foreign key dipasang
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `kamar` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nama_kamar` VARCHAR(100) NOT NULL UNIQUE,
                `kapasitas` INT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `guru` (
                `kdg` INT AUTO_INCREMENT PRIMARY KEY,
                `stambuk` VARCHAR(50) NOT NULL,
                `nama` VARCHAR(150) NOT NULL,
                `daerah` VARCHAR(100) NOT NULL,
                `konsulat` VARCHAR(100) NOT NULL,
                `email` VARCHAR(100) NOT NULL,
                `no_telp` VARCHAR(20) NOT NULL,
                `kamar_id` INT NULL,
                INDEX (`stambuk`),
                FOREIGN KEY (`kamar_id`) REFERENCES `kamar` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Tambahkan kolom kamar_id jika belum ada di tabel guru lama
        $columns = $this->pdo->query("SHOW COLUMNS FROM `guru` LIKE 'kamar_id'")->fetchAll();
        if (empty($columns)) {
            $this->pdo->exec("ALTER TABLE `guru` ADD COLUMN `kamar_id` INT NULL");
            $this->pdo->exec("ALTER TABLE `guru` ADD CONSTRAINT `fk_guru_kamar` FOREIGN KEY (`kamar_id`) REFERENCES `kamar` (`id`) ON DELETE SET NULL");
        }
    }

    /**
     * @return GuruEntity[]
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT g.*, k.nama_kamar 
            FROM `guru` g 
            LEFT JOIN `kamar` k ON g.kamar_id = k.id 
            ORDER BY g.kdg DESC
        ");
        $rows = $stmt->fetchAll();

        $entities = [];
        foreach ($rows as $row) {
            $entities[] = GuruFactory::createFromRow($row);
        }
        return $entities;
    }

    public function getById(int $kdg): ?GuruEntity
    {
        $stmt = $this->pdo->prepare("
            SELECT g.*, k.nama_kamar 
            FROM `guru` g 
            LEFT JOIN `kamar` k ON g.kamar_id = k.id 
            WHERE g.kdg = :kdg
        ");
        $stmt->execute(['kdg' => $kdg]);
        $row = $stmt->fetch();
        return $row ? GuruFactory::createFromRow($row) : null;
    }

    public function getByStambuk(string $stambuk): ?GuruEntity
    {
        $stmt = $this->pdo->prepare("
            SELECT g.*, k.nama_kamar 
            FROM `guru` g 
            LEFT JOIN `kamar` k ON g.kamar_id = k.id 
            WHERE g.stambuk = :stambuk
        ");
        $stmt->execute(['stambuk' => $stambuk]);
        $row = $stmt->fetch();
        return $row ? GuruFactory::createFromRow($row) : null;
    }

    public function create(GuruDto $dto): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO `guru` (`stambuk`, `nama`, `daerah`, `konsulat`, `email`, `no_telp`, `kamar_id`)
            VALUES (:stambuk, :nama, :daerah, :konsulat, :email, :no_telp, :kamar_id)
        ");
        return $stmt->execute([
            'stambuk' => $dto->stambuk,
            'nama' => $dto->nama,
            'daerah' => $dto->daerah,
            'konsulat' => $dto->konsulat,
            'email' => $dto->email,
            'no_telp' => $dto->noTelp,
            'kamar_id' => $dto->kamarId,
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
                `no_telp` = :no_telp,
                `kamar_id` = :kamar_id
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
            'kamar_id' => $dto->kamarId,
        ]);
    }

    public function delete(int $kdg): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM `guru` WHERE `kdg` = :kdg");
        return $stmt->execute(['kdg' => $kdg]);
    }
}
