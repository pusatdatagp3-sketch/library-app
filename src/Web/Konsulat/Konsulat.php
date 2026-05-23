<?php

declare(strict_types=1);

namespace App\Web\Konsulat;

use App\Environment;
use PDO;

final class Konsulat
{
    public ?int $id = null;
    public string $konsulat = '';
    public string $kampus = '';

    public array $errors = [];

    private static ?PDO $pdo = null;

    private static function getDb(): PDO
    {
        if (self::$pdo === null) {
            $host = Environment::dbHost();
            $port = Environment::dbPort();
            $dbname = Environment::dbName();
            $user = Environment::dbUser();
            $password = Environment::dbPassword();

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            self::$pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Ensure table exists
            self::$pdo->exec("
                CREATE TABLE IF NOT EXISTS `konsulat` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `konsulat` VARCHAR(100) NOT NULL UNIQUE,
                    `kampus` VARCHAR(100) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Seed default data if empty
            $count = (int) self::$pdo->query("SELECT COUNT(*) FROM `konsulat`")->fetchColumn();
            if ($count === 0) {
                $stmt = self::$pdo->prepare("INSERT INTO `konsulat` (`konsulat`, `kampus`) VALUES (?, ?)");
                $stmt->execute(['Surabaya', 'Kampus 1']);
                $stmt->execute(['Jakarta', 'Kampus 2']);
                $stmt->execute(['Ponorogo', 'Kampus 3']);
            }
        }
        return self::$pdo;
    }

    public function load(array $data): bool
    {
        $this->konsulat = trim((string)($data['konsulat'] ?? $this->konsulat));
        $this->kampus = trim((string)($data['kampus'] ?? $this->kampus));
        return !empty($data);
    }

    public function validate(): bool
    {
        $this->errors = [];
        if ($this->konsulat === '') {
            $this->errors['konsulat'] = 'Nama Konsulat tidak boleh kosong.';
        }
        if ($this->kampus === '') {
            $this->errors['kampus'] = 'Nama Kampus tidak boleh kosong.';
        }

        // Cek keunikan nama konsulat
        if (empty($this->errors['konsulat'])) {
            $db = self::getDb();
            if ($this->id === null) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM `konsulat` WHERE `konsulat` = :konsulat");
                $stmt->execute(['konsulat' => $this->konsulat]);
            } else {
                $stmt = $db->prepare("SELECT COUNT(*) FROM `konsulat` WHERE `konsulat` = :konsulat AND `id` != :id");
                $stmt->execute(['konsulat' => $this->konsulat, 'id' => $this->id]);
            }
            if ((int)$stmt->fetchColumn() > 0) {
                $this->errors['konsulat'] = 'Nama Konsulat ini sudah terdaftar.';
            }
        }

        return empty($this->errors);
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $db = self::getDb();
        if ($this->id === null) {
            $stmt = $db->prepare("INSERT INTO `konsulat` (`konsulat`, `kampus`) VALUES (:konsulat, :kampus)");
            $result = $stmt->execute([
                'konsulat' => $this->konsulat,
                'kampus' => $this->kampus,
            ]);
            if ($result) {
                $this->id = (int) $db->lastInsertId();
                return true;
            }
            return false;
        }

        $stmt = $db->prepare("UPDATE `konsulat` SET `konsulat` = :konsulat, `kampus` = :kampus WHERE `id` = :id");
        return $stmt->execute([
            'id' => $this->id,
            'konsulat' => $this->konsulat,
            'kampus' => $this->kampus,
        ]);
    }

    public function delete(): bool
    {
        if ($this->id === null) {
            return false;
        }
        $db = self::getDb();
        $stmt = $db->prepare("DELETE FROM `konsulat` WHERE `id` = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * @return self[]
     */
    public static function find(): array
    {
        $db = self::getDb();
        $stmt = $db->query("SELECT * FROM `konsulat` ORDER BY `id` DESC");
        $rows = $stmt->fetchAll();

        $models = [];
        foreach ($rows as $row) {
            $model = new self();
            $model->id = (int) $row['id'];
            $model->konsulat = $row['konsulat'];
            $model->kampus = $row['kampus'];
            $models[] = $model;
        }
        return $models;
    }

    public static function findOne(int $id): ?self
    {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM `konsulat` WHERE `id` = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $model = new self();
        $model->id = (int) $row['id'];
        $model->konsulat = $row['konsulat'];
        $model->kampus = $row['kampus'];
        return $model;
    }
}
