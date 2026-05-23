<?php

declare(strict_types=1);

namespace App\Web\Rbac;

use App\Environment;
use PDO;

final class RbacRepository
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

        $this->initializeTables();
    }

    private function initializeTables(): void
    {
        // 1. Create rbac_roles table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `rbac_roles` (
                `name` VARCHAR(50) PRIMARY KEY,
                `description` VARCHAR(255) NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 2. Create rbac_permissions table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `rbac_permissions` (
                `name` VARCHAR(50) PRIMARY KEY,
                `description` VARCHAR(255) NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 3. Create rbac_role_permissions table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `rbac_role_permissions` (
                `role_name` VARCHAR(50) NOT NULL,
                `permission_name` VARCHAR(50) NOT NULL,
                PRIMARY KEY (`role_name`, `permission_name`),
                FOREIGN KEY (`role_name`) REFERENCES `rbac_roles` (`name`) ON DELETE CASCADE,
                FOREIGN KEY (`permission_name`) REFERENCES `rbac_permissions` (`name`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 4. Create rbac_route_permissions table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `rbac_route_permissions` (
                `route_name` VARCHAR(100) PRIMARY KEY,
                `permission_name` VARCHAR(50) NULL,
                FOREIGN KEY (`permission_name`) REFERENCES `rbac_permissions` (`name`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Seed Roles
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM `rbac_roles`");
        if ($stmt->fetchColumn() == 0) {
            $roles = [
                ['name' => 'Admin', 'description' => 'Administrator dengan akses penuh ke sistem dan konfigurasi RBAC.'],
                ['name' => 'Operator', 'description' => 'Staf / Operator yang dapat melakukan CRUD pada modul Guru.'],
                ['name' => 'Guru', 'description' => 'Pengguna Guru yang hanya memiliki hak akses untuk melihat data.'],
            ];
            $roleStmt = $this->pdo->prepare("INSERT INTO `rbac_roles` (`name`, `description`) VALUES (:name, :description)");
            foreach ($roles as $role) {
                $roleStmt->execute($role);
            }
        }

        // Seed Permissions
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM `rbac_permissions`");
        if ($stmt->fetchColumn() == 0) {
            $permissions = [
                ['name' => 'view_guru', 'description' => 'Melihat daftar dan detail data Guru.'],
                ['name' => 'create_guru', 'description' => 'Menambahkan data Guru baru dan mengunggah Excel.'],
                ['name' => 'update_guru', 'description' => 'Mengubah detail data Guru.'],
                ['name' => 'delete_guru', 'description' => 'Menghapus data Guru.'],
                ['name' => 'manage_rbac', 'description' => 'Mengelola RBAC (Peran, Hak Akses, Matrix, & Pengguna).'],
            ];
            $permStmt = $this->pdo->prepare("INSERT INTO `rbac_permissions` (`name`, `description`) VALUES (:name, :description)");
            foreach ($permissions as $perm) {
                $permStmt->execute($perm);
            }
        }

        // Seed Role Permissions (Matrix)
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM `rbac_role_permissions`");
        if ($stmt->fetchColumn() == 0) {
            $matrix = [
                'Admin' => ['view_guru', 'create_guru', 'update_guru', 'delete_guru', 'manage_rbac'],
                'Operator' => ['view_guru', 'create_guru', 'update_guru', 'delete_guru'],
                'Guru' => ['view_guru'],
            ];

            $matrixStmt = $this->pdo->prepare("
                INSERT INTO `rbac_role_permissions` (`role_name`, `permission_name`)
                VALUES (:role_name, :permission_name)
            ");

            foreach ($matrix as $roleName => $perms) {
                foreach ($perms as $permName) {
                    $matrixStmt->execute([
                        'role_name' => $roleName,
                        'permission_name' => $permName,
                    ]);
                }
            }
        }

        // Seed Route Permissions Mapping
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM `rbac_route_permissions`");
        if ($stmt->fetchColumn() == 0) {
            $routeMappings = [
                ['route_name' => 'guru/index', 'permission_name' => 'view_guru'],
                ['route_name' => 'guru/create', 'permission_name' => 'create_guru'],
                ['route_name' => 'guru/create/post', 'permission_name' => 'create_guru'],
                ['route_name' => 'guru/update', 'permission_name' => 'update_guru'],
                ['route_name' => 'guru/update/post', 'permission_name' => 'update_guru'],
                ['route_name' => 'guru/delete', 'permission_name' => 'delete_guru'],
                ['route_name' => 'guru/download-template', 'permission_name' => 'view_guru'],
                ['route_name' => 'guru/upload', 'permission_name' => 'create_guru'],
                ['route_name' => 'rbac/index', 'permission_name' => 'manage_rbac'],
                ['route_name' => 'rbac/save-matrix', 'permission_name' => 'manage_rbac'],
                ['route_name' => 'rbac/update-user-role', 'permission_name' => 'manage_rbac'],
                ['route_name' => 'rbac/create-user', 'permission_name' => 'manage_rbac'],
            ];

            $routeStmt = $this->pdo->prepare("
                INSERT INTO `rbac_route_permissions` (`route_name`, `permission_name`)
                VALUES (:route_name, :permission_name)
            ");

            foreach ($routeMappings as $mapping) {
                $routeStmt->execute($mapping);
            }
        }

        // Seed Kamar Permissions jika belum ada (untuk database yang sudah ter-seed sebelumnya)
        $newPermissions = [
            ['name' => 'view_kamar', 'description' => 'Melihat daftar dan detail data Kamar.'],
            ['name' => 'create_kamar', 'description' => 'Menambahkan data Kamar baru.'],
            ['name' => 'update_kamar', 'description' => 'Mengubah detail data Kamar.'],
            ['name' => 'delete_kamar', 'description' => 'Menghapus data Kamar.'],
        ];
        $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM `rbac_permissions` WHERE `name` = :name");
        $insertPermStmt = $this->pdo->prepare("INSERT INTO `rbac_permissions` (`name`, `description`) VALUES (:name, :description)");
        foreach ($newPermissions as $perm) {
            $checkStmt->execute(['name' => $perm['name']]);
            if ($checkStmt->fetchColumn() == 0) {
                $insertPermStmt->execute($perm);
                
                // Tambahkan relasi hak akses ke Admin secara default
                $this->pdo->prepare("INSERT IGNORE INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES ('Admin', :perm)")
                    ->execute(['perm' => $perm['name']]);
                
                // Tambahkan relasi hak akses ke Operator secara default (kecuali delete)
                if ($perm['name'] !== 'delete_kamar') {
                    $this->pdo->prepare("INSERT IGNORE INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES ('Operator', :perm)")
                        ->execute(['perm' => $perm['name']]);
                }
            }
        }

        // Seed Kamar Route Mappings jika belum ada
        $newRouteMappings = [
            ['route_name' => 'kamar/index', 'permission_name' => 'view_kamar'],
            ['route_name' => 'kamar/create', 'permission_name' => 'create_kamar'],
            ['route_name' => 'kamar/create/post', 'permission_name' => 'create_kamar'],
            ['route_name' => 'kamar/update', 'permission_name' => 'update_kamar'],
            ['route_name' => 'kamar/update/post', 'permission_name' => 'update_kamar'],
            ['route_name' => 'kamar/delete', 'permission_name' => 'delete_kamar'],
        ];
        $routeCheckStmt = $this->pdo->prepare("SELECT COUNT(*) FROM `rbac_route_permissions` WHERE `route_name` = :route_name");
        $routeInsertStmt = $this->pdo->prepare("INSERT INTO `rbac_route_permissions` (`route_name`, `permission_name`) VALUES (:route_name, :permission_name)");
        foreach ($newRouteMappings as $mapping) {
            $routeCheckStmt->execute(['route_name' => $mapping['route_name']]);
            if ($routeCheckStmt->fetchColumn() == 0) {
                $routeInsertStmt->execute($mapping);
            }
        }

        // Seed Konsulat Permissions jika belum ada
        $konsulatPermissions = [
            ['name' => 'view_konsulat', 'description' => 'Melihat daftar dan detail data Konsulat.'],
            ['name' => 'create_konsulat', 'description' => 'Menambahkan data Konsulat baru.'],
            ['name' => 'update_konsulat', 'description' => 'Mengubah detail data Konsulat.'],
            ['name' => 'delete_konsulat', 'description' => 'Menghapus data Konsulat.'],
        ];
        foreach ($konsulatPermissions as $perm) {
            $checkStmt->execute(['name' => $perm['name']]);
            if ($checkStmt->fetchColumn() == 0) {
                $insertPermStmt->execute($perm);
                
                // Tambahkan relasi hak akses ke Admin secara default
                $this->pdo->prepare("INSERT IGNORE INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES ('Admin', :perm)")
                    ->execute(['perm' => $perm['name']]);
                
                // Tambahkan relasi hak akses ke Operator secara default (kecuali delete)
                if ($perm['name'] !== 'delete_konsulat') {
                    $this->pdo->prepare("INSERT IGNORE INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES ('Operator', :perm)")
                        ->execute(['perm' => $perm['name']]);
                }
            }
        }

        // Seed Konsulat Route Mappings jika belum ada
        $konsulatRouteMappings = [
            ['route_name' => 'konsulat/index', 'permission_name' => 'view_konsulat'],
            ['route_name' => 'konsulat/create', 'permission_name' => 'create_konsulat'],
            ['route_name' => 'konsulat/create/post', 'permission_name' => 'create_konsulat'],
            ['route_name' => 'konsulat/update', 'permission_name' => 'update_konsulat'],
            ['route_name' => 'konsulat/update/post', 'permission_name' => 'update_konsulat'],
            ['route_name' => 'konsulat/delete', 'permission_name' => 'delete_konsulat'],
        ];
        foreach ($konsulatRouteMappings as $mapping) {
            $routeCheckStmt->execute(['route_name' => $mapping['route_name']]);
            if ($routeCheckStmt->fetchColumn() == 0) {
                $routeInsertStmt->execute($mapping);
            }
        }

        // Seed Gii Permissions
        $giiPermissions = [
            ['name' => 'manage_gii', 'description' => 'Akses dan penggunaan Gii Generator.'],
        ];
        foreach ($giiPermissions as $perm) {
            $checkStmt->execute(['name' => $perm['name']]);
            if ($checkStmt->fetchColumn() == 0) {
                $insertPermStmt->execute($perm);
                $this->pdo->prepare("INSERT IGNORE INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES ('Admin', :perm)")
                    ->execute(['perm' => $perm['name']]);
            }
        }
        $giiRouteMappings = [
            ['route_name' => 'gii/index', 'permission_name' => 'manage_gii'],
            ['route_name' => 'gii/generate', 'permission_name' => 'manage_gii'],
        ];
        foreach ($giiRouteMappings as $mapping) {
            $routeCheckStmt->execute(['route_name' => $mapping['route_name']]);
            if ($routeCheckStmt->fetchColumn() == 0) {
                $routeInsertStmt->execute($mapping);
            }
        }

        // Seed Pelanggaran Permissions jika belum ada
        $pelanggaranPermissions = [
            ['name' => 'view_pelanggaran', 'description' => 'Melihat daftar dan detail data Pelanggaran.'],
            ['name' => 'create_pelanggaran', 'description' => 'Menambahkan data Pelanggaran baru.'],
            ['name' => 'update_pelanggaran', 'description' => 'Mengubah detail data Pelanggaran.'],
            ['name' => 'delete_pelanggaran', 'description' => 'Menghapus data Pelanggaran.'],
        ];
        foreach ($pelanggaranPermissions as $perm) {
            $checkStmt->execute(['name' => $perm['name']]);
            if ($checkStmt->fetchColumn() == 0) {
                $insertPermStmt->execute($perm);
                $this->pdo->prepare("INSERT IGNORE INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES ('Admin', :perm)")
                    ->execute(['perm' => $perm['name']]);
                if ($perm['name'] !== 'delete_pelanggaran') {
                    $this->pdo->prepare("INSERT IGNORE INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES ('Operator', :perm)")
                        ->execute(['perm' => $perm['name']]);
                }
            }
        }
        $pelanggaranRouteMappings = [
            ['route_name' => 'pelanggaran/index', 'permission_name' => 'view_pelanggaran'],
            ['route_name' => 'pelanggaran/create', 'permission_name' => 'create_pelanggaran'],
            ['route_name' => 'pelanggaran/create/post', 'permission_name' => 'create_pelanggaran'],
            ['route_name' => 'pelanggaran/update', 'permission_name' => 'update_pelanggaran'],
            ['route_name' => 'pelanggaran/update/post', 'permission_name' => 'update_pelanggaran'],
            ['route_name' => 'pelanggaran/delete', 'permission_name' => 'delete_pelanggaran'],
        ];
        foreach ($pelanggaranRouteMappings as $mapping) {
            $routeCheckStmt->execute(['route_name' => $mapping['route_name']]);
            if ($routeCheckStmt->fetchColumn() == 0) {
                $routeInsertStmt->execute($mapping);
            }
        }

        // Seed Santri Permissions jika belum ada
        $santriPermissions = [
            ['name' => 'view_santri', 'description' => 'Melihat daftar dan detail data Santri.'],
            ['name' => 'create_santri', 'description' => 'Menambahkan data Santri baru.'],
            ['name' => 'update_santri', 'description' => 'Mengubah detail data Santri.'],
            ['name' => 'delete_santri', 'description' => 'Menghapus data Santri.'],
        ];
        foreach ($santriPermissions as $perm) {
            $checkStmt->execute(['name' => $perm['name']]);
            if ($checkStmt->fetchColumn() == 0) {
                $insertPermStmt->execute($perm);
                $this->pdo->prepare("INSERT IGNORE INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES ('Admin', :perm)")
                    ->execute(['perm' => $perm['name']]);
                if ($perm['name'] !== 'delete_santri') {
                    $this->pdo->prepare("INSERT IGNORE INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES ('Operator', :perm)")
                        ->execute(['perm' => $perm['name']]);
                }
            }
        }
        $santriRouteMappings = [
            ['route_name' => 'santri/index', 'permission_name' => 'view_santri'],
            ['route_name' => 'santri/create', 'permission_name' => 'create_santri'],
            ['route_name' => 'santri/create/post', 'permission_name' => 'create_santri'],
            ['route_name' => 'santri/update', 'permission_name' => 'update_santri'],
            ['route_name' => 'santri/update/post', 'permission_name' => 'update_santri'],
            ['route_name' => 'santri/delete', 'permission_name' => 'delete_santri'],
        ];
        foreach ($santriRouteMappings as $mapping) {
            $routeCheckStmt->execute(['route_name' => $mapping['route_name']]);
            if ($routeCheckStmt->fetchColumn() == 0) {
                $routeInsertStmt->execute($mapping);
            }
        }
    }

    public function getAllRoles(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM `rbac_roles` ORDER BY `name` ASC");
        return $stmt->fetchAll();
    }

    public function getAllPermissions(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM `rbac_permissions` ORDER BY `name` ASC");
        return $stmt->fetchAll();
    }

    /**
     * @return string[] List of permission names for a given role
     */
    public function getRolePermissions(string $role): array
    {
        $stmt = $this->pdo->prepare("
            SELECT `permission_name` 
            FROM `rbac_role_permissions` 
            WHERE `role_name` = :role
        ");
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function saveRolePermissionsMatrix(array $matrix): void
    {
        $this->pdo->beginTransaction();
        try {
            // Hapus semua relasi role_permissions terlebih dahulu
            $this->pdo->exec("DELETE FROM `rbac_role_permissions`");

            // Masukkan kembali relasi baru
            $stmt = $this->pdo->prepare("
                INSERT INTO `rbac_role_permissions` (`role_name`, `permission_name`)
                VALUES (:role_name, :permission_name)
            ");

            foreach ($matrix as $roleName => $permissions) {
                foreach ($permissions as $permissionName) {
                    $stmt->execute([
                        'role_name' => $roleName,
                        'permission_name' => $permissionName,
                    ]);
                }
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function hasPermission(string $role, string $permission): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM `rbac_role_permissions` 
            WHERE `role_name` = :role AND `permission_name` = :permission
        ");
        $stmt->execute([
            'role' => $role,
            'permission' => $permission,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function getRoutePermissionMap(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM `rbac_route_permissions` ORDER BY `route_name` ASC");
        $rows = $stmt->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['route_name']] = $row['permission_name'];
        }
        return $map;
    }

    public function saveRoutePermissions(array $mappings): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->exec("DELETE FROM `rbac_route_permissions`");

            $stmt = $this->pdo->prepare("
                INSERT INTO `rbac_route_permissions` (`route_name`, `permission_name`)
                VALUES (:route_name, :permission_name)
            ");

            foreach ($mappings as $routeName => $permName) {
                $dbPermName = ($permName === '' || $permName === null) ? null : $permName;

                $stmt->execute([
                    'route_name' => $routeName,
                    'permission_name' => $dbPermName,
                ]);
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
