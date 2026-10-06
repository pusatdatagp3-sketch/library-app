<?php

declare(strict_types=1);

namespace App\Web\Perpustakaan\Model;

use App\Shared\Service\SidasApiClient;
use Cycle\Database\DatabaseInterface;
use DateTimeImmutable;
use Throwable;

use function error_log;
use function is_array;
use function str_ends_with;
use function trim;

/**
 * SiswaRepository
 *
 * Mengambil data santri/siswa untuk kebutuhan modul perpustakaan.
 * Mengintegrasikan pencarian real-time ke SIDAS API (sidas-yii3) dengan
 * fallback resilien ke tabel cache lokal `santri_cache`.
 */
class SiswaRepository
{
    private bool $tableChecked = false;

    public function __construct(
        private ?DatabaseInterface $db = null,
        private ?SidasApiClient $sidasClient = null
    ) {
    }

    /**
     * Mencari data santri berdasarkan nomor stambuk.
     * Alur:
     * 1. Coba request real-time ke SIDAS API via SidasApiClient.
     *    - Jika format stambuk barcode hanya angka belakang (misal 51125), coba findByStambuk lalu fallback search.
     *    - Jika sukses, update/simpan data santri ke cache lokal `santri_cache`.
     * 2. Jika SIDAS offline atau gagal respon, cari di tabel cache lokal `santri_cache` (bisa exact match atau suffix).
     * 3. Jika tidak ditemukan di manapun, return null.
     *
     * @return array{
     *     santri_id: int,
     *     kds: int,
     *     stambuk: string,
     *     nama: string,
     *     kelas: string,
     *     rayon: string,
     *     kamar: string,
     *     konsulat: string,
     *     kampus: string,
     *     status: string,
     *     aktif: bool,
     *     warning?: string|null,
     *     from_cache?: bool
     * }|null
     */
    public function findByStambuk(string $stambuk): ?array
    {
        $stambuk = trim($stambuk);
        if ($stambuk === '') {
            return null;
        }

        // Normalisasi input barcode scanner: jika 8 digit angka tanpa titik (contoh 24038666),
        // ubah ke format standar stambuk dengan titik X.XX.XXXXX (contoh 2.40.38666)
        if (preg_match('/^\d{8}$/', $stambuk)) {
            $stambuk = substr($stambuk, 0, 1) . '.' . substr($stambuk, 1, 2) . '.' . substr($stambuk, 3);
        }

        // ── 1. Coba ambil dari SIDAS API (Real-Time) ─────────────────────
        if ($this->sidasClient !== null) {
            try {
                $sidasData = $this->sidasClient->findByStambuk($stambuk);

                // Jika tidak ditemukan dengan exact stambuk dan input berupa angka,
                // coba pencarian via endpoint search (untuk mencocokkan stambuk berakhiran angka tsb)
                if ($sidasData === null) {
                    $searchResults = $this->sidasClient->search($stambuk, 5);
                    foreach ($searchResults as $item) {
                        $fullStambuk = (string) ($item['stambuk'] ?? '');
                        if ($fullStambuk === $stambuk || str_ends_with($fullStambuk, '.' . $stambuk) || str_ends_with($fullStambuk, $stambuk)) {
                            $sidasData = $item;
                            break;
                        }
                    }
                }

                if ($sidasData !== null && is_array($sidasData)) {
                    $normalized = $this->normalizeSantriData($sidasData);

                    // Simpan ke cache lokal untuk cadangan saat jaringan putus
                    $this->saveToCache($normalized);

                    return $normalized;
                }
            } catch (Throwable $e) {
                error_log('[SiswaRepository] Gagal menghubungi SIDAS API: ' . $e->getMessage());
                // Lanjut ke fallback cache lokal di bawah
            }
        }

        // ── 2. Fallback: Cari di cache lokal `santri_cache` ───────────────
        $cached = $this->findInCache($stambuk);
        if ($cached !== null) {
            $cached['from_cache'] = true;
            return $cached;
        }

        // ── 3. Tidak ditemukan ───────────────────────────────────────────
        return null;
    }

    /**
     * Mencari data santri langsung di tabel cache lokal.
     * Mendukung pencarian exact match maupun suffix stambuk (misal '51125' cocok dengan '2.46.51125').
     *
     * @return array{
     *     santri_id: int,
     *     kds: int,
     *     stambuk: string,
     *     nama: string,
     *     kelas: string,
     *     rayon: string,
     *     kamar: string,
     *     konsulat: string,
     *     kampus: string,
     *     status: string,
     *     aktif: bool
     * }|null
     */
    public function findInCache(string $stambuk): ?array
    {
        if ($this->db === null) {
            return null;
        }

        $stambuk = trim($stambuk);
        if (preg_match('/^\d{8}$/', $stambuk)) {
            $stambuk = substr($stambuk, 0, 1) . '.' . substr($stambuk, 1, 2) . '.' . substr($stambuk, 3);
        }

        $this->ensureTableExists();

        try {
            // Cek exact match terlebih dahulu
            $row = $this->db->select()
                ->from('santri_cache')
                ->where('stambuk', $stambuk)
                ->run()
                ->fetch();

            // Jika tidak ditemukan dan stambuk pendek, cari dengan suffix match
            if (!$row) {
                $row = $this->db->select()
                    ->from('santri_cache')
                    ->where('stambuk', 'LIKE', '%.' . $stambuk)
                    ->orWhere('stambuk', 'LIKE', '%' . $stambuk)
                    ->run()
                    ->fetch();
            }

            if ($row) {
                return [
                    'santri_id' => (int) ($row['santri_id'] ?? 0),
                    'kds' => (int) ($row['kds'] ?? $row['santri_id'] ?? 0),
                    'stambuk' => (string) ($row['stambuk'] ?? $stambuk),
                    'nama' => (string) ($row['nama'] ?? ''),
                    'kelas' => (string) ($row['kelas'] ?? '-'),
                    'rayon' => (string) ($row['rayon'] ?? '-'),
                    'kamar' => (string) ($row['kamar'] ?? '-'),
                    'konsulat' => (string) ($row['konsulat'] ?? '-'),
                    'kampus' => (string) ($row['kampus'] ?? '-'),
                    'status' => (string) ($row['status'] ?? 'Aktif'),
                    'aktif' => (bool) ($row['aktif'] ?? true),
                ];
            }
        } catch (Throwable $e) {
            error_log('[SiswaRepository] Error membaca cache lokal: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Menyimpan atau memperbarui data santri ke dalam tabel cache lokal.
     *
     * @param array<string, mixed> $data
     */
    public function saveToCache(array $data): bool
    {
        if ($this->db === null) {
            return false;
        }

        $this->ensureTableExists();

        try {
            $stambuk = trim((string) ($data['stambuk'] ?? ''));
            $santriId = (int) ($data['santri_id'] ?? $data['kds'] ?? 0);

            if ($stambuk === '' && $santriId === 0) {
                return false;
            }

            $payload = [
                'santri_id' => $santriId,
                'kds' => (int) ($data['kds'] ?? $santriId),
                'stambuk' => $stambuk,
                'nama' => trim((string) ($data['nama'] ?? $data['nama_santri'] ?? '')),
                'kelas' => trim((string) ($data['kelas'] ?? '')),
                'rayon' => trim((string) ($data['rayon'] ?? '')),
                'kamar' => trim((string) ($data['kamar'] ?? '')),
                'konsulat' => trim((string) ($data['konsulat'] ?? '')),
                'kampus' => trim((string) ($data['kampus'] ?? '')),
                'jenis_kelamin' => trim((string) ($data['jenis_kelamin'] ?? '')),
                'status' => trim((string) ($data['status'] ?? 'Aktif')),
                'aktif' => isset($data['aktif']) ? (bool) $data['aktif'] : true,
                'synced_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ];

            // Cek apakah data sudah ada berdasarkan stambuk atau santri_id
            $existing = $this->db->select('id')
                ->from('santri_cache')
                ->where('stambuk', $stambuk)
                ->orWhere('santri_id', $santriId)
                ->run()
                ->fetch();

            if ($existing && !empty($existing['id'])) {
                $this->db->update('santri_cache', $payload, ['id' => (int) $existing['id']])->run();
            } else {
                $this->db->insert('santri_cache')->values($payload)->run();
            }

            return true;
        } catch (Throwable $e) {
            error_log('[SiswaRepository] Gagal menyimpan ke santri_cache: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch insert / update untuk command sinkronisasi malam (list-active).
     *
     * @param array<int, array<string, mixed>> $items
     * @return int Jumlah data yang berhasil disimpan
     */
    public function bulkSaveToCache(array $items): int
    {
        if ($this->db === null || empty($items)) {
            return 0;
        }

        $this->ensureTableExists();

        $saved = 0;
        foreach ($items as $item) {
            if ($this->saveToCache($item)) {
                $saved++;
            }
        }

        return $saved;
    }

    /**
     * Menghitung total data di tabel cache lokal.
     */
    public function countCache(): int
    {
        if ($this->db === null) {
            return 0;
        }

        $this->ensureTableExists();

        try {
            $row = $this->db->select()
                ->from('santri_cache')
                ->count();
            return (int) $row;
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * Normalisasi format respons santri dari SIDAS API.
     *
     * @param array<string, mixed> $data
     * @return array{
     *     santri_id: int,
     *     kds: int,
     *     stambuk: string,
     *     nama: string,
     *     kelas: string,
     *     rayon: string,
     *     kamar: string,
     *     konsulat: string,
     *     kampus: string,
     *     jenis_kelamin: string,
     *     status: string,
     *     aktif: bool
     * }
     */
    private function normalizeSantriData(array $data): array
    {
        $santriId = (int) ($data['santri_id'] ?? $data['kds'] ?? 0);
        $kds = (int) ($data['kds'] ?? $santriId);
        $isAktif = isset($data['aktif']) ? (bool) $data['aktif'] : true;

        return [
            'santri_id' => $santriId,
            'kds' => $kds,
            'stambuk' => (string) ($data['stambuk'] ?? ''),
            'nama' => (string) ($data['nama'] ?? $data['nama_santri'] ?? ''),
            'kelas' => (string) ($data['kelas'] ?? '-'),
            'rayon' => (string) ($data['rayon'] ?? '-'),
            'kamar' => (string) ($data['kamar'] ?? '-'),
            'konsulat' => (string) ($data['konsulat'] ?? '-'),
            'kampus' => (string) ($data['kampus'] ?? '-'),
            'jenis_kelamin' => (string) ($data['jenis_kelamin'] ?? '-'),
            'status' => (string) ($data['status'] ?? 'Aktif'),
            'aktif' => $isAktif,
        ];
    }

    /**
     * Memastikan tabel santri_cache tersedia di database.
     */
    private function ensureTableExists(): void
    {
        if ($this->tableChecked || $this->db === null) {
            return;
        }

        try {
            if (!$this->db->hasTable('santri_cache')) {
                $sql = 'CREATE TABLE IF NOT EXISTS `santri_cache` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `santri_id` INT NOT NULL,
                    `kds` INT NULL,
                    `stambuk` VARCHAR(30) NOT NULL,
                    `nama` VARCHAR(150) NOT NULL,
                    `kelas` VARCHAR(50) NULL,
                    `rayon` VARCHAR(100) NULL,
                    `kamar` VARCHAR(50) NULL,
                    `konsulat` VARCHAR(100) NULL,
                    `kampus` VARCHAR(50) NULL,
                    `jenis_kelamin` VARCHAR(20) NULL,
                    `status` VARCHAR(50) NULL,
                    `aktif` TINYINT(1) NOT NULL DEFAULT 1,
                    `synced_at` DATETIME NOT NULL,
                    INDEX `idx_sc_santri_id` (`santri_id`),
                    INDEX `idx_sc_stambuk` (`stambuk`),
                    INDEX `idx_sc_aktif` (`aktif`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

                $this->db->query($sql);
            }
            $this->tableChecked = true;
        } catch (Throwable $e) {
            error_log('[SiswaRepository] Gagal membuat tabel santri_cache: ' . $e->getMessage());
        }
    }
}
