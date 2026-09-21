<?php

declare(strict_types=1);

namespace App\Web\Staf\Model;

use Cycle\Database\DatabaseInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Select\Repository;
use Throwable;

/**
 * Repository untuk data master staf perpustakaan (tabel list_staf).
 *
 * @extends Repository<StafEntity>
 */
class StafRepository extends Repository
{
    private ?DatabaseInterface $db = null;

    public function __construct(
        Select $select,
        private ?EntityManagerInterface $entityManager = null
    ) {
        parent::__construct($select);
        $this->ensureTable();
    }

    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function getDatabase(): DatabaseInterface
    {
        if ($this->db === null) {
            $this->db = $this->select()->getBuilder()->getLoader()->getSource()->getDatabase();
        }
        return $this->db;
    }

    /**
     * Memastikan tabel list_staf siap digunakan di database dan memiliki sample data jika masih kosong.
     */
    public function ensureTable(): void
    {
        try {
            $db = $this->getDatabase();
            $db->query("CREATE TABLE IF NOT EXISTS `list_staf` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nama_staf` VARCHAR(150) NOT NULL,
                `divisi` VARCHAR(50) NOT NULL DEFAULT 'Library',
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Cek apakah tabel masih kosong, jika kosong isi dengan data inisial
            $count = (int) $db->query("SELECT COUNT(*) as cnt FROM `list_staf`")->fetch()['cnt'];
            if ($count === 0) {
                $initialStaff = [
                    ['nama_staf' => 'Ust. Ahmad Fauzi, S.Pd.I', 'divisi' => 'Library', 'is_active' => 1],
                    ['nama_staf' => 'Ust. Muhammad Ihsan, S.Hum', 'divisi' => 'Library', 'is_active' => 1],
                    ['nama_staf' => 'Ustzh. Siti Nurhaliza, S.Pust', 'divisi' => 'Library', 'is_active' => 1],
                    ['nama_staf' => 'Ust. Budi Santoso', 'divisi' => 'Staff', 'is_active' => 1],
                    ['nama_staf' => 'Ust. Hendra Pratama', 'divisi' => 'Staff', 'is_active' => 1],
                    ['nama_staf' => 'Ust. Rizky Maulana', 'divisi' => 'Staff', 'is_active' => 1],
                ];
                foreach ($initialStaff as $staf) {
                    $db->insert('list_staf')->values($staf)->run();
                }
            }
        } catch (Throwable) {
            // Ignore if schema already managed
        }
    }

    /**
     * Mengambil daftar staf yang aktif dan mengelompokkannya berdasarkan divisi:
     * ['Library' => [...], 'Staff' => [...]]
     *
     * @return array{Library: StafEntity[], Staff: StafEntity[]}
     */
    public function findActiveGroupedByDivisi(): array
    {
        $grouped = [
            'Library' => [],
            'Staff' => [],
        ];

        try {
            /** @var StafEntity[] $results */
            $results = $this->select()
                ->where('is_active', true)
                ->orderBy('divisi', 'ASC')
                ->orderBy('nama_staf', 'ASC')
                ->fetchAll();

            foreach ($results as $staf) {
                $divisi = $staf->divisi === 'Staff' ? 'Staff' : 'Library';
                $grouped[$divisi][] = $staf;
            }
        } catch (Throwable) {
            // Fallback jika query Cycle ORM select terkendala
            $db = $this->getDatabase();
            $rows = $db->query("SELECT * FROM `list_staf` WHERE `is_active` = 1 ORDER BY `divisi` ASC, `nama_staf` ASC")->fetchAll();
            foreach ($rows as $row) {
                $entity = new StafEntity(
                    (string) $row['nama_staf'],
                    (string) $row['divisi'],
                    (bool) $row['is_active']
                );
                $entity->id = (int) $row['id'];
                $divisi = $entity->divisi === 'Staff' ? 'Staff' : 'Library';
                $grouped[$divisi][] = $entity;
            }
        }

        return $grouped;
    }

    /**
     * Mengambil semua daftar staf (aktif maupun non-aktif) untuk halaman manajemen.
     *
     * @return iterable<StafEntity>
     */
    public function findAll(array $scope = [], array $orderBy = []): iterable
    {
        if (empty($orderBy)) {
            $orderBy = ['divisi' => 'ASC', 'nama_staf' => 'ASC'];
        }

        try {
            return parent::findAll($scope, $orderBy);
        } catch (Throwable) {
            $db = $this->getDatabase();
            $rows = $db->query("SELECT * FROM `list_staf` ORDER BY `divisi` ASC, `nama_staf` ASC")->fetchAll();
            $results = [];
            foreach ($rows as $row) {
                $entity = new StafEntity(
                    (string) $row['nama_staf'],
                    (string) $row['divisi'],
                    (bool) $row['is_active']
                );
                $entity->id = (int) $row['id'];
                $results[] = $entity;
            }
            return $results;
        }
    }

    /**
     * Mencari staf berdasarkan ID primary key.
     */
    public function findById(int $id): ?StafEntity
    {
        try {
            /** @var StafEntity|null $staf */
            $staf = $this->findByPK($id);
            if ($staf !== null) {
                return $staf;
            }
        } catch (Throwable) {
            // fallback
        }

        $db = $this->getDatabase();
        $row = $db->query("SELECT * FROM `list_staf` WHERE `id` = :id LIMIT 1", [':id' => $id])->fetch();
        if (!$row) {
            return null;
        }

        $entity = new StafEntity(
            (string) $row['nama_staf'],
            (string) $row['divisi'],
            (bool) $row['is_active']
        );
        $entity->id = (int) $row['id'];
        return $entity;
    }

    /**
     * Menyimpan (Create / Update) data staf.
     */
    public function save(StafEntity $staf): bool
    {
        if ($this->entityManager !== null) {
            try {
                $this->entityManager->persist($staf)->run();
                return true;
            } catch (Throwable) {
                // fallback to direct SQL
            }
        }

        $db = $this->getDatabase();
        if ($staf->id !== null && $staf->id > 0) {
            // Update
            $db->update('list_staf', [
                'nama_staf' => $staf->nama_staf,
                'divisi'    => $staf->divisi,
                'is_active' => $staf->is_active ? 1 : 0,
            ], ['id' => $staf->id])->run();
        } else {
            // Insert
            $insertId = $db->insert('list_staf')->values([
                'nama_staf' => $staf->nama_staf,
                'divisi'    => $staf->divisi,
                'is_active' => $staf->is_active ? 1 : 0,
            ])->run();
            $staf->id = (int) $insertId;
        }

        return true;
    }

    /**
     * Mengubah status aktif / non-aktif staf.
     */
    public function toggleStatus(int $id): bool
    {
        $staf = $this->findById($id);
        if ($staf === null) {
            return false;
        }

        $staf->is_active = !$staf->is_active;
        return $this->save($staf);
    }

    /**
     * Menghapus record staf berdasarkan ID.
     */
    public function deleteById(int $id): bool
    {
        $staf = $this->findById($id);
        if ($staf === null) {
            return false;
        }

        if ($this->entityManager !== null) {
            try {
                $this->entityManager->delete($staf)->run();
                return true;
            } catch (Throwable) {
                // fallback
            }
        }

        $db = $this->getDatabase();
        $db->delete('list_staf', ['id' => $id])->run();
        return true;
    }
}
