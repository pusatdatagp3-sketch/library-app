<?php

declare(strict_types=1);

namespace App\Web\Pelanggaran;

use App\Environment;
use PDO;

final class Pelanggaran
{
    public ?int $id = null;
    public string $stambuk = '';
    public string $pelanggaran = '';
    public int $poin = 0;
    public ?string $keterangan = null;

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
        $this->stambuk = trim((string)($data['stambuk'] ?? $this->stambuk));
        $this->pelanggaran = trim((string)($data['pelanggaran'] ?? $this->pelanggaran));
        $this->poin = isset($data['poin']) && $data['poin'] !== '' ? (int)$data['poin'] : 0;
        $this->keterangan = trim((string)($data['keterangan'] ?? $this->keterangan));
        return !empty($data);
    }

    public function validate(): bool
    {
        $this->errors = [];
        if ($this->stambuk === '' || $this->stambuk === null) {
            $this->errors['stambuk'] = 'Kolom Stambuk tidak boleh kosong.';
        }
        if ($this->pelanggaran === '' || $this->pelanggaran === null) {
            $this->errors['pelanggaran'] = 'Kolom Pelanggaran tidak boleh kosong.';
        }
        if ($this->poin === '' || $this->poin === null) {
            $this->errors['poin'] = 'Kolom Poin tidak boleh kosong.';
        }
        return empty($this->errors);
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $db = self::getDb();
        if ($this->isNewRecord) {
            $stmt = $db->prepare("INSERT INTO `pelanggaran` (`stambuk`, `pelanggaran`, `poin`, `keterangan`) VALUES (:stambuk, :pelanggaran, :poin, :keterangan)");
            $result = $stmt->execute([
                'stambuk' => $this->stambuk,
                'pelanggaran' => $this->pelanggaran,
                'poin' => $this->poin,
                'keterangan' => $this->keterangan,
            ]);
            if ($result) {
                $this->id = (int) $db->lastInsertId();
                $this->isNewRecord = false;
                return true;
            }
            return false;
        }

        $stmt = $db->prepare("UPDATE `pelanggaran` SET `stambuk` = :stambuk, `pelanggaran` = :pelanggaran, `poin` = :poin, `keterangan` = :keterangan WHERE `id` = :id");
        return $stmt->execute([
                'stambuk' => $this->stambuk,
                'pelanggaran' => $this->pelanggaran,
                'poin' => $this->poin,
                'keterangan' => $this->keterangan,
                'id' => $this->id,
        ]);
    }

    public function delete(): bool
    {
        if ($this->id === null) {
            return false;
        }
        $db = self::getDb();
        $stmt = $db->prepare("DELETE FROM `pelanggaran` WHERE `id` = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    /**
     * @return self[]
     */
    public static function find(): array
    {
        $db = self::getDb();
        $stmt = $db->query("SELECT * FROM `pelanggaran` ORDER BY `id` DESC");
        $rows = $stmt->fetchAll();

        $models = [];
        foreach ($rows as $row) {
            $model = new self();
            $model->isNewRecord = false;
            $model->id = isset($row['id']) ? (int) $row['id'] : null;
            $model->stambuk = (string) ($row['stambuk'] ?? '');
            $model->pelanggaran = (string) ($row['pelanggaran'] ?? '');
            $model->poin = isset($row['poin']) ? (int) $row['poin'] : null;
            $model->keterangan = (string) ($row['keterangan'] ?? '');
            $models[] = $model;
        }
        return $models;
    }

    public static function findOne(int $id): ?self
    {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM `pelanggaran` WHERE `id` = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $model = new self();
        $model->isNewRecord = false;
            $model->id = isset($row['id']) ? (int) $row['id'] : null;
            $model->stambuk = (string) ($row['stambuk'] ?? '');
            $model->pelanggaran = (string) ($row['pelanggaran'] ?? '');
            $model->poin = isset($row['poin']) ? (int) $row['poin'] : null;
            $model->keterangan = (string) ($row['keterangan'] ?? '');
        return $model;
    }
}
