<?php

declare(strict_types=1);

namespace App\Web\Perpustakaan\Model;

use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Select\Repository;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Repository untuk data kunjungan perpustakaan (tabel record_perpustakaan_kunjungan).
 * Menggunakan Cycle ORM Select\Repository dan Constructor Property Promotion.
 *
 * @extends Repository<KunjunganEntity>
 */
class KunjunganRepository extends Repository
{
    public function __construct(
        Select $select,
        private ?EntityManagerInterface $entityManager = null
    ) {
        parent::__construct($select);
    }

    /**
     * Memungkinkan setter EntityManager jika di-instantiate langsung oleh Cycle ORM factory.
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Mengambil semua daftar kunjungan untuk hari ini (00:00:00 - 23:59:59),
     * diurutkan dari yang paling baru.
     *
     * @return KunjunganEntity[]
     */
    public function getKunjunganHariIni(): array
    {
        $startOfDay = (new DateTimeImmutable('today'))->format('Y-m-d 00:00:00');
        $endOfDay = (new DateTimeImmutable('today'))->format('Y-m-d 23:59:59');

        /** @var KunjunganEntity[] $results */
        $results = $this->select()
            ->where('waktu_kunjungan', '>=', $startOfDay)
            ->where('waktu_kunjungan', '<=', $endOfDay)
            ->orderBy('waktu_kunjungan', 'DESC')
            ->fetchAll();

        return $results;
    }

    /**
     * Mengambil instance database koneksi aktif dari loader Cycle ORM.
     */
    public function getDatabase()
    {
        return $this->select()->getBuilder()->getLoader()->getSource()->getDatabase();
    }

    /**
     * Mengambil semua daftar kunjungan untuk minggu ini (Sabtu - Jumat atau kustom),
     * diurutkan dari yang paling baru.
     *
     * @return KunjunganEntity[]
     */
    public function getKunjunganMingguIni(?string $startStr = null, ?string $endStr = null): array
    {
        if ($startStr === null || $endStr === null) {
            $now = new DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta'));
            $startOfWeek = ($now->format('w') == 6)
                ? $now->setTime(0, 0, 0)
                : $now->modify('last saturday')->setTime(0, 0, 0);
            $endOfWeek = ($now->format('w') == 5)
                ? $now->setTime(23, 59, 59)
                : $now->modify('next friday')->setTime(23, 59, 59);

            $startStr = $startOfWeek->format('Y-m-d H:i:s');
            $endStr = $endOfWeek->format('Y-m-d H:i:s');
        }

        /** @var KunjunganEntity[] $results */
        $results = $this->select()
            ->where('waktu_kunjungan', '>=', $startStr)
            ->where('waktu_kunjungan', '<=', $endStr)
            ->orderBy('waktu_kunjungan', 'DESC')
            ->fetchAll();

        return $results;
    }

    /**
     * Mencatat kunjungan baru ke tabel record_perpustakaan_kunjungan.
     *
     * @param int $santriId ID / kds santri
     * @param array{
     *     stambuk: string,
     *     nama_santri?: string|null,
     *     nama?: string|null,
     *     kelas?: string|null,
     *     rayon?: string|null,
     *     konsulat?: string|null,
     *     penginput?: string|null,
     *     waktu_kunjungan?: DateTimeInterface|string|null
     * } $data Data detail santri dan penginput
     */
    public function catatKunjungan(int $santriId, array $data): bool
    {
        $kunjungan = new KunjunganEntity();
        $kunjungan->santri_id = $santriId;
        $kunjungan->stambuk = trim((string) ($data['stambuk'] ?? ''));
        $kunjungan->nama_santri = $data['nama_santri'] ?? $data['nama'] ?? null;
        $kunjungan->kelas = $data['kelas'] ?? null;
        $kunjungan->rayon = $data['rayon'] ?? null;
        $kunjungan->konsulat = $data['konsulat'] ?? null;
        $kunjungan->penginput = (string) ($data['penginput'] ?? 'Petugas Perpustakaan');

        if (isset($data['waktu_kunjungan'])) {
            if ($data['waktu_kunjungan'] instanceof DateTimeInterface) {
                $kunjungan->waktu_kunjungan = DateTimeImmutable::createFromInterface($data['waktu_kunjungan']);
            } elseif (is_string($data['waktu_kunjungan']) && $data['waktu_kunjungan'] !== '') {
                $kunjungan->waktu_kunjungan = new DateTimeImmutable($data['waktu_kunjungan']);
            }
        } else {
            $kunjungan->waktu_kunjungan = new DateTimeImmutable();
        }

        $kunjungan->created_at = new DateTimeImmutable();

        if ($this->entityManager !== null) {
            $this->entityManager->persist($kunjungan)->run();
            return true;
        }

        // Fallback jika entityManager belum diset: gunakan database query langsung
        $db = $this->select()->getBuilder()->getLoader()->getSource()->getDatabase();
        $db->insert('record_perpustakaan_kunjungan')->values([
            'santri_id' => $kunjungan->santri_id,
            'stambuk' => $kunjungan->stambuk,
            'nama_santri' => $kunjungan->nama_santri,
            'kelas' => $kunjungan->kelas,
            'rayon' => $kunjungan->rayon,
            'konsulat' => $kunjungan->konsulat,
            'waktu_kunjungan' => $kunjungan->waktu_kunjungan?->format('Y-m-d H:i:s'),
            'penginput' => $kunjungan->penginput,
            'created_at' => $kunjungan->created_at?->format('Y-m-d H:i:s'),
        ])->run();

        return true;
    }

    /**
     * Mengambil rekap agregat jumlah kunjungan per kelas untuk chart (misal minggu ini atau hari ini).
     *
     * @param string $periode 'hari_ini' | 'minggu_ini'
     * @return array<string, int> [kelas => total]
     */
    public function getRekapPerKelas(string $periode = 'minggu_ini'): array
    {
        $kunjunganList = $periode === 'hari_ini'
            ? $this->getKunjunganHariIni()
            : $this->getKunjunganMingguIni();

        $rekap = [];
        foreach ($kunjunganList as $item) {
            $kelas = trim((string) ($item->kelas ?? 'Lainnya'));
            if ($kelas === '') {
                $kelas = 'Lainnya';
            }
            $rekap[$kelas] = ($rekap[$kelas] ?? 0) + 1;
        }

        ksort($rekap);
        return $rekap;
    }

    /**
     * Mengambil daftar riwayat kunjungan perpustakaan terbaru dengan batas limit,
     * filter rentang waktu, dan opsi pencarian kata kunci.
     *
     * @param int $limit Batas maksimal rekaman yang diambil (default: 100)
     * @param string|null $startStr Waktu awal rentang (Y-m-d H:i:s)
     * @param string|null $endStr Waktu akhir rentang (Y-m-d H:i:s)
     * @param string|null $search Kata kunci pencarian santri
     * @return KunjunganEntity[]
     */
    public function getRiwayatKunjungan(
        int $limit = 100,
        ?string $startStr = null,
        ?string $endStr = null,
        ?string $search = null
    ): array {
        $select = $this->select();

        if ($startStr !== null && $endStr !== null) {
            $select = $select
                ->where('waktu_kunjungan', '>=', $startStr)
                ->where('waktu_kunjungan', '<=', $endStr);
        }

        if ($search !== null && $search !== '') {
            $kw = '%' . trim($search) . '%';
            $select = $select->where(static function ($s) use ($kw) {
                $s->where('stambuk', 'LIKE', $kw)
                    ->orWhere('nama_santri', 'LIKE', $kw)
                    ->orWhere('kelas', 'LIKE', $kw)
                    ->orWhere('rayon', 'LIKE', $kw)
                    ->orWhere('konsulat', 'LIKE', $kw)
                    ->orWhere('penginput', 'LIKE', $kw);
            });
        }

        /** @var KunjunganEntity[] $results */
        $results = $select
            ->orderBy('waktu_kunjungan', 'DESC')
            ->limit($limit)
            ->fetchAll();

        return $results;
    }

    /**
     * Mengambil data agregasi kunjungan per group kolom (kelas, rayon, konsulat),
     * dengan opsi filter rentang waktu tertentu dan pencarian kategori.
     *
     * @param string $field 'kelas' | 'rayon' | 'konsulat'
     * @param string|null $startStr Waktu awal rentang (Y-m-d H:i:s)
     * @param string|null $endStr Waktu akhir rentang (Y-m-d H:i:s)
     * @param string|null $search Kata kunci pencarian nama kategori
     * @return array<array<string, mixed>>
     */
     public function getRekapAgregasi(
         string $field,
         ?string $startStr = null,
         ?string $endStr = null,
         ?string $search = null
     ): array {
         $allowed = ['kelas', 'rayon', 'konsulat'];
         if (!in_array($field, $allowed, true)) {
             $field = 'kelas';
         }

         $db = $this->getDatabase();

         $whereConditions = [];
         $params = [];

         if ($startStr !== null && $endStr !== null) {
             $whereConditions[] = '`waktu_kunjungan` BETWEEN :start_str AND :end_str';
             $params[':start_str'] = $startStr;
             $params[':end_str'] = $endStr;
         }

         if ($search !== null && $search !== '') {
             $whereConditions[] = "`{$field}` LIKE :search_kw";
             $params[':search_kw'] = '%' . trim($search) . '%';
         }

         $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

         $sql = "SELECT `{$field}`, COUNT(`id`) AS `total` 
                 FROM `record_perpustakaan_kunjungan` 
                 {$whereClause}
                 GROUP BY `{$field}` 
                 ORDER BY `{$field}` ASC";

         /** @var array<array<string, mixed>> $rows */
         $rows = $db->query($sql, $params)->fetchAll();

        return array_map(static function (array $row) use ($field) {
            $val = trim((string) ($row[$field] ?? ''));
            $displayName = $val !== '' ? $val : 'Lainnya / Tidak Terdata';
            return [
                $field => $displayName,
                'nama' => $displayName,
                'total' => (int) ($row['total'] ?? 0),
            ];
        }, $rows);
    }

    /**
     * Mengambil daftar santri yang berkunjung berdasarkan kategori dan nilainya (drill-down).
     *
     * @param string $field 'kelas' | 'rayon' | 'konsulat'
     * @param string $value Nilai filter (contoh: '5B', 'Syam 2')
     * @return array<array<string, mixed>>
     */
    public function getDetailKunjunganBy(string $field, string $value): array
    {
        $allowed = ['kelas', 'rayon', 'konsulat'];
        if (!in_array($field, $allowed, true)) {
            return [];
        }

        $db = $this->select()->getBuilder()->getLoader()->getSource()->getDatabase();

        if ($value === 'Lainnya / Tidak Terdata' || $value === '' || $value === '-') {
            $sql = "SELECT `id`, `santri_id`, `stambuk`, `nama_santri`, `kelas`, `rayon`, `konsulat`, `waktu_kunjungan`, `penginput`
                    FROM `record_perpustakaan_kunjungan`
                    WHERE (`{$field}` IS NULL OR `{$field}` = '' OR `{$field}` = :val)
                    ORDER BY `waktu_kunjungan` DESC";
            $params = [':val' => $value];
        } else {
            $sql = "SELECT `id`, `santri_id`, `stambuk`, `nama_santri`, `kelas`, `rayon`, `konsulat`, `waktu_kunjungan`, `penginput`
                    FROM `record_perpustakaan_kunjungan`
                    WHERE `{$field}` = :val
                    ORDER BY `waktu_kunjungan` DESC";
            $params = [':val' => $value];
        }

        /** @var array<array<string, mixed>> $rows */
        $rows = $db->query($sql, $params)->fetchAll();

        return $rows;
    }
}
