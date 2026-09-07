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
     * Mengambil semua daftar kunjungan untuk minggu ini (Senin - Minggu),
     * diurutkan dari yang paling baru.
     *
     * @return KunjunganEntity[]
     */
    public function getKunjunganMingguIni(): array
    {
        $startOfWeek = (new DateTimeImmutable('monday this week'))->format('Y-m-d 00:00:00');
        $endOfWeek = (new DateTimeImmutable('sunday this week 23:59:59'))->format('Y-m-d 23:59:59');

        /** @var KunjunganEntity[] $results */
        $results = $this->select()
            ->where('waktu_kunjungan', '>=', $startOfWeek)
            ->where('waktu_kunjungan', '<=', $endOfWeek)
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
}
