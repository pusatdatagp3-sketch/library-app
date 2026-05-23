<?php

declare(strict_types=1);

namespace App\Web\Santri;

use App\Environment;
use PDO;

final class Santri
{
    public ?int $kds = null;
    public ?string $nama = null;
    public ?string $kelas = null;
    public ?string $daerah = null;

    public bool $isNewRecord = true;
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
        }
        return self::$pdo;
    }

    public function load(array $data): bool
    {
        $this->nama = trim((string)($data['nama'] ?? $this->nama));
        $this->kelas = trim((string)($data['kelas'] ?? $this->kelas));
        $this->daerah = trim((string)($data['daerah'] ?? $this->daerah));
        return !empty($data);
    }

    public function validate(): bool
    {
        $this->errors = [];
        return empty($this->errors);
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $db = self::getDb();
        if ($this->isNewRecord) {
            $stmt = $db->prepare("INSERT INTO `santri` (`nama`, `kelas`, `daerah`) VALUES (:nama, :kelas, :daerah)");
            $result = $stmt->execute([
                'nama' => $this->nama,
                'kelas' => $this->kelas,
                'daerah' => $this->daerah,
            ]);
            if ($result) {
                $this->kds = (int) $db->lastInsertId();
                $this->isNewRecord = false;
                return true;
            }
            return false;
        }

        $stmt = $db->prepare("UPDATE `santri` SET `nama` = :nama, `kelas` = :kelas, `daerah` = :daerah WHERE `kds` = :kds");
        return $stmt->execute([
                'nama' => $this->nama,
                'kelas' => $this->kelas,
                'daerah' => $this->daerah,
                'kds' => $this->kds,
        ]);
    }

    public function delete(): bool
    {
        if ($this->kds === null) {
            return false;
        }
        $db = self::getDb();
        $stmt = $db->prepare("DELETE FROM `santri` WHERE `kds` = :kds");
        return $stmt->execute(['kds' => $this->kds]);
    }

    /**
     * @return self[]
     */
    public static function find(): array
    {
        $db = self::getDb();
        $stmt = $db->query("SELECT * FROM `santri` ORDER BY `kds` DESC");
        $rows = $stmt->fetchAll();

        $models = [];
        foreach ($rows as $row) {
            $model = new self();
            $model->isNewRecord = false;
            $model->kds = isset($row['kds']) ? (int) $row['kds'] : null;
            $model->nama = (string) ($row['nama'] ?? '');
            $model->kelas = (string) ($row['kelas'] ?? '');
            $model->daerah = (string) ($row['daerah'] ?? '');
            $models[] = $model;
        }
        return $models;
    }

    public static function findOne(int $kds): ?self
    {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM `santri` WHERE `kds` = :kds LIMIT 1");
        $stmt->execute(['kds' => $kds]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $model = new self();
        $model->isNewRecord = false;
            $model->kds = isset($row['kds']) ? (int) $row['kds'] : null;
            $model->nama = (string) ($row['nama'] ?? '');
            $model->kelas = (string) ($row['kelas'] ?? '');
            $model->daerah = (string) ($row['daerah'] ?? '');
        return $model;
    }
}
