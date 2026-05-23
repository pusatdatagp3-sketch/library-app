<?php

declare(strict_types=1);

namespace App\Web\Kamar;

use App\Environment;
use PDO;

final class KamarRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $host = Environment::dbHost();
        $port = Environment::dbPort();
        $dbname = Environment::dbName();
        $user = Environment::dbUser();
        $password = Environment::dbPassword();

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->initializeTable();
    }

    private function initializeTable(): void
    {
        // 1. Buat tabel kamar
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `kamar` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nama_kamar` VARCHAR(100) NOT NULL UNIQUE,
                `kapasitas` INT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 2. Seeding awal jika kosong
        $count = (int) $this->pdo->query("SELECT COUNT(*) FROM `kamar`")->fetchColumn();
        if ($count === 0) {
            $stmt = $this->pdo->prepare("INSERT INTO `kamar` (`nama_kamar`, `kapasitas`) VALUES (?, ?)");
            $stmt->execute(['Kamar Abu Bakar', 10]);
            $stmt->execute(['Kamar Umar bin Khattab', 12]);
            $stmt->execute(['Kamar Utsman bin Affan', 8]);
            $stmt->execute(['Kamar Ali bin Abi Thalib', 15]);
        }
    }

    /**
     * @return Kamar[]
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM `kamar` ORDER BY `id` DESC");
        $rows = $stmt->fetchAll();

        $list = [];
        foreach ($rows as $row) {
            $list[] = new Kamar(
                id: (int) $row['id'],
                namaKamar: $row['nama_kamar'],
                kapasitas: (int) $row['kapasitas']
            );
        }
        return $list;
    }

    public function findById(int $id): ?Kamar
    {
        $stmt = $this->pdo->prepare("SELECT * FROM `kamar` WHERE `id` = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new Kamar(
            id: (int) $row['id'],
            namaKamar: $row['nama_kamar'],
            kapasitas: (int) $row['kapasitas']
        );
    }

    public function save(KamarDto $dto, ?int $id = null): bool
    {
        if ($id === null) {
            $stmt = $this->pdo->prepare("INSERT INTO `kamar` (`nama_kamar`, `kapasitas`) VALUES (:nama, :kapasitas)");
            return $stmt->execute([
                'nama' => $dto->namaKamar,
                'kapasitas' => $dto->kapasitas,
            ]);
        }

        $stmt = $this->pdo->prepare("UPDATE `kamar` SET `nama_kamar` = :nama, `kapasitas` = :kapasitas WHERE `id` = :id");
        return $stmt->execute([
            'id' => $id,
            'nama' => $dto->namaKamar,
            'kapasitas' => $dto->kapasitas,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM `kamar` WHERE `id` = :id");
        return $stmt->execute(['id' => $id]);
    }
}
