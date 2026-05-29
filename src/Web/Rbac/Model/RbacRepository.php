<?php

declare(strict_types=1);

namespace App\Web\Rbac\Model;

use Cycle\Database\DatabaseInterface;

final class RbacRepository
{
    public function __construct(
        private DatabaseInterface $db
    ) {
        $this->initializeTables();
    }

    private function initializeTables(): void
    {
        $this->db->execute("
            CREATE TABLE IF NOT EXISTS `rbac_roles` (
                `name` VARCHAR(50) PRIMARY KEY,
                `description` VARCHAR(255) NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->execute("
            CREATE TABLE IF NOT EXISTS `rbac_permissions` (
                `name` VARCHAR(50) PRIMARY KEY,
                `description` VARCHAR(255) NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->execute("
            CREATE TABLE IF NOT EXISTS `rbac_role_permissions` (
                `role_name` VARCHAR(50) NOT NULL,
                `permission_name` VARCHAR(50) NOT NULL,
                PRIMARY KEY (`role_name`, `permission_name`),
                FOREIGN KEY (`role_name`) REFERENCES `rbac_roles` (`name`) ON DELETE CASCADE,
                FOREIGN KEY (`permission_name`) REFERENCES `rbac_permissions` (`name`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->execute("
            CREATE TABLE IF NOT EXISTS `rbac_route_permissions` (
                `route_name` VARCHAR(100) PRIMARY KEY,
                `permission_name` VARCHAR(50) NULL,
                FOREIGN KEY (`permission_name`) REFERENCES `rbac_permissions` (`name`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Seed Roles
        if ((int)$this->db->query("SELECT COUNT(*) FROM `rbac_roles`")->fetchColumn() === 0) {
            $roles = [
                ['name' => 'Admin',    'description' => 'Administrator dengan akses penuh ke sistem dan konfigurasi RBAC.'],
                ['name' => 'Operator', 'description' => 'Staf / Operator yang dapat melakukan CRUD pada modul Guru.'],
                ['name' => 'Guru',     'description' => 'Pengguna Guru yang hanya memiliki hak akses untuk melihat data.'],
            ];
            foreach ($roles as $role) {
                $this->db->execute("INSERT INTO `rbac_roles` (`name`, `description`) VALUES (?, ?)", [$role['name'], $role['description']]);
            }
        }

        // Seed Permissions
        $allPermissions = [
            ['name' => 'view_guru',           'description' => 'Melihat daftar dan detail data Guru.'],
            ['name' => 'create_guru',          'description' => 'Menambahkan data Guru baru dan mengunggah Excel.'],
            ['name' => 'update_guru',          'description' => 'Mengubah detail data Guru.'],
            ['name' => 'delete_guru',          'description' => 'Menghapus data Guru.'],
            ['name' => 'manage_rbac',          'description' => 'Mengelola RBAC (Peran, Hak Akses, Matrix, & Pengguna).'],
            ['name' => 'view_kamar',           'description' => 'Melihat daftar dan detail data Kamar.'],
            ['name' => 'create_kamar',         'description' => 'Menambahkan data Kamar baru.'],
            ['name' => 'update_kamar',         'description' => 'Mengubah detail data Kamar.'],
            ['name' => 'delete_kamar',         'description' => 'Menghapus data Kamar.'],
            ['name' => 'view_konsulat',        'description' => 'Melihat daftar dan detail data Konsulat.'],
            ['name' => 'create_konsulat',      'description' => 'Menambahkan data Konsulat baru.'],
            ['name' => 'update_konsulat',      'description' => 'Mengubah detail data Konsulat.'],
            ['name' => 'delete_konsulat',      'description' => 'Menghapus data Konsulat.'],
            ['name' => 'manage_gii',           'description' => 'Akses dan penggunaan Gii Generator.'],
            ['name' => 'view_pelanggaran',     'description' => 'Melihat daftar dan detail data Pelanggaran.'],
            ['name' => 'create_pelanggaran',   'description' => 'Menambahkan data Pelanggaran baru.'],
            ['name' => 'update_pelanggaran',   'description' => 'Mengubah detail data Pelanggaran.'],
            ['name' => 'delete_pelanggaran',   'description' => 'Menghapus data Pelanggaran.'],
            ['name' => 'view_santri',          'description' => 'Melihat daftar dan detail data Santri.'],
            ['name' => 'create_santri',        'description' => 'Menambahkan data Santri baru.'],
            ['name' => 'update_santri',        'description' => 'Mengubah detail data Santri.'],
            ['name' => 'delete_santri',        'description' => 'Menghapus data Santri.'],
            ['name' => 'view_rayon',           'description' => 'Melihat daftar dan detail data Rayon.'],
            ['name' => 'create_rayon',         'description' => 'Menambahkan data Rayon baru.'],
            ['name' => 'update_rayon',         'description' => 'Mengubah detail data Rayon.'],
            ['name' => 'delete_rayon',         'description' => 'Menghapus data Rayon.'],
            ['name' => 'view_perizinan',           'description' => 'Melihat daftar dan detail data Perizinan.'],
            ['name' => 'create_perizinan',         'description' => 'Menambahkan data Perizinan baru.'],
            ['name' => 'update_perizinan',         'description' => 'Mengubah detail data Perizinan.'],
            ['name' => 'delete_perizinan',         'description' => 'Menghapus data Perizinan.'],
        ];
        foreach ($allPermissions as $perm) {
            $exists = (int)$this->db->query("SELECT COUNT(*) FROM `rbac_permissions` WHERE `name` = ?", [$perm['name']])->fetchColumn();
            if ($exists === 0) {
                $this->db->execute("INSERT INTO `rbac_permissions` (`name`, `description`) VALUES (?, ?)", [$perm['name'], $perm['description']]);
            }
        }

        // Seed Role Permissions matrix
        if ((int)$this->db->query("SELECT COUNT(*) FROM `rbac_role_permissions`")->fetchColumn() === 0) {
            $matrix = [
                'Admin'    => ['view_guru','create_guru','update_guru','delete_guru','manage_rbac','view_kamar','create_kamar','update_kamar','delete_kamar','view_konsulat','create_konsulat','update_konsulat','delete_konsulat','manage_gii','view_pelanggaran','create_pelanggaran','update_pelanggaran','delete_pelanggaran','view_santri','create_santri','update_santri','delete_santri','view_rayon','create_rayon','update_rayon','delete_rayon','view_perizinan','create_perizinan','update_perizinan','delete_perizinan'],
                'Operator' => ['view_guru','create_guru','update_guru','delete_guru','view_kamar','create_kamar','update_kamar','view_konsulat','create_konsulat','update_konsulat','view_pelanggaran','create_pelanggaran','update_pelanggaran','view_santri','create_santri','update_santri','view_rayon','create_rayon','update_rayon','view_perizinan','create_perizinan','update_perizinan'],
                'Guru'     => ['view_guru'],
            ];
            foreach ($matrix as $roleName => $perms) {
                foreach ($perms as $permName) {
                    $this->db->execute("INSERT IGNORE INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES (?, ?)", [$roleName, $permName]);
                }
            }
        }

        // Seed Route Permissions
        $allRoutes = [
            ['route_name' => 'guru/index',                   'permission_name' => 'view_guru'],
            ['route_name' => 'guru/create',                  'permission_name' => 'create_guru'],
            ['route_name' => 'guru/create/post',             'permission_name' => 'create_guru'],
            ['route_name' => 'guru/update',                  'permission_name' => 'update_guru'],
            ['route_name' => 'guru/update/post',             'permission_name' => 'update_guru'],
            ['route_name' => 'guru/delete',                  'permission_name' => 'delete_guru'],
            ['route_name' => 'guru/download-template',       'permission_name' => 'view_guru'],
            ['route_name' => 'guru/upload',                  'permission_name' => 'create_guru'],
            ['route_name' => 'kamar/index',                  'permission_name' => 'view_kamar'],
            ['route_name' => 'kamar/create',                 'permission_name' => 'create_kamar'],
            ['route_name' => 'kamar/create/post',            'permission_name' => 'create_kamar'],
            ['route_name' => 'kamar/update',                 'permission_name' => 'update_kamar'],
            ['route_name' => 'kamar/update/post',            'permission_name' => 'update_kamar'],
            ['route_name' => 'kamar/delete',                 'permission_name' => 'delete_kamar'],
            ['route_name' => 'konsulat/index',               'permission_name' => 'view_konsulat'],
            ['route_name' => 'konsulat/create',              'permission_name' => 'create_konsulat'],
            ['route_name' => 'konsulat/create/post',         'permission_name' => 'create_konsulat'],
            ['route_name' => 'konsulat/update',              'permission_name' => 'update_konsulat'],
            ['route_name' => 'konsulat/update/post',         'permission_name' => 'update_konsulat'],
            ['route_name' => 'konsulat/delete',              'permission_name' => 'delete_konsulat'],
            ['route_name' => 'gii/index',                    'permission_name' => 'manage_gii'],
            ['route_name' => 'gii/generate',                 'permission_name' => 'manage_gii'],
            ['route_name' => 'pelanggaran/index',            'permission_name' => 'view_pelanggaran'],
            ['route_name' => 'pelanggaran/create',           'permission_name' => 'create_pelanggaran'],
            ['route_name' => 'pelanggaran/create/post',      'permission_name' => 'create_pelanggaran'],
            ['route_name' => 'pelanggaran/update',           'permission_name' => 'update_pelanggaran'],
            ['route_name' => 'pelanggaran/update/post',      'permission_name' => 'update_pelanggaran'],
            ['route_name' => 'pelanggaran/delete',           'permission_name' => 'delete_pelanggaran'],
            ['route_name' => 'santri/index',                 'permission_name' => 'view_santri'],
            ['route_name' => 'santri/create',                'permission_name' => 'create_santri'],
            ['route_name' => 'santri/create/post',           'permission_name' => 'create_santri'],
            ['route_name' => 'santri/update',                'permission_name' => 'update_santri'],
            ['route_name' => 'santri/update/post',           'permission_name' => 'update_santri'],
            ['route_name' => 'santri/delete',                'permission_name' => 'delete_santri'],
            ['route_name' => 'users/index',                  'permission_name' => 'manage_rbac'],
            ['route_name' => 'users/create',                 'permission_name' => 'manage_rbac'],
            ['route_name' => 'users/create/post',            'permission_name' => 'manage_rbac'],
            ['route_name' => 'users/update',                 'permission_name' => 'manage_rbac'],
            ['route_name' => 'users/update/post',            'permission_name' => 'manage_rbac'],
            ['route_name' => 'users/delete',                 'permission_name' => 'manage_rbac'],
            ['route_name' => 'roles/index',                  'permission_name' => 'manage_rbac'],
            ['route_name' => 'roles/create',                 'permission_name' => 'manage_rbac'],
            ['route_name' => 'roles/create/post',            'permission_name' => 'manage_rbac'],
            ['route_name' => 'roles/update',                 'permission_name' => 'manage_rbac'],
            ['route_name' => 'roles/update/post',            'permission_name' => 'manage_rbac'],
            ['route_name' => 'roles/delete',                 'permission_name' => 'manage_rbac'],
            ['route_name' => 'roles/save-matrix',            'permission_name' => 'manage_rbac'],
            ['route_name' => 'permissions/index',            'permission_name' => 'manage_rbac'],
            ['route_name' => 'permissions/create',           'permission_name' => 'manage_rbac'],
            ['route_name' => 'permissions/create/post',      'permission_name' => 'manage_rbac'],
            ['route_name' => 'permissions/update',           'permission_name' => 'manage_rbac'],
            ['route_name' => 'permissions/update/post',      'permission_name' => 'manage_rbac'],
            ['route_name' => 'permissions/delete',           'permission_name' => 'manage_rbac'],
            ['route_name' => 'routes/index',                 'permission_name' => 'manage_rbac'],
            ['route_name' => 'routes/save-route-permissions','permission_name' => 'manage_rbac'],
            ['route_name' => 'rayon/index',                 'permission_name' => 'view_rayon'],
            ['route_name' => 'rayon/create',                'permission_name' => 'create_rayon'],
            ['route_name' => 'rayon/create/post',           'permission_name' => 'create_rayon'],
            ['route_name' => 'rayon/update',                'permission_name' => 'update_rayon'],
            ['route_name' => 'rayon/update/post',           'permission_name' => 'update_rayon'],
            ['route_name' => 'rayon/delete',                'permission_name' => 'delete_rayon'],
            ['route_name' => 'perizinan/index',                 'permission_name' => 'view_perizinan'],
            ['route_name' => 'perizinan/create',                'permission_name' => 'create_perizinan'],
            ['route_name' => 'perizinan/create/post',           'permission_name' => 'create_perizinan'],
            ['route_name' => 'perizinan/update',                'permission_name' => 'update_perizinan'],
            ['route_name' => 'perizinan/update/post',           'permission_name' => 'update_perizinan'],
            ['route_name' => 'perizinan/delete',                'permission_name' => 'delete_perizinan'],
        ];
        foreach ($allRoutes as $mapping) {
            $exists = (int)$this->db->query("SELECT COUNT(*) FROM `rbac_route_permissions` WHERE `route_name` = ?", [$mapping['route_name']])->fetchColumn();
            if ($exists === 0) {
                $this->db->execute("INSERT INTO `rbac_route_permissions` (`route_name`, `permission_name`) VALUES (?, ?)", [$mapping['route_name'], $mapping['permission_name']]);
            }
        }
    }

    public function getAllRoles(): array
    {
        return $this->db->query("SELECT * FROM `rbac_roles` ORDER BY `name` ASC")->fetchAll();
    }

    public function getAllPermissions(): array
    {
        return $this->db->query("SELECT * FROM `rbac_permissions` ORDER BY `name` ASC")->fetchAll();
    }

    public function getRolePermissions(string $role): array
    {
        return $this->db->query(
            "SELECT `permission_name` FROM `rbac_role_permissions` WHERE `role_name` = ?",
            [$role]
        )->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function saveRolePermissionsMatrix(array $matrix): void
    {
        $this->db->transaction(function () use ($matrix): void {
            $this->db->execute("DELETE FROM `rbac_role_permissions`");
            foreach ($matrix as $roleName => $permissions) {
                foreach ($permissions as $permissionName) {
                    $this->db->execute(
                        "INSERT INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES (?, ?)",
                        [$roleName, $permissionName]
                    );
                }
            }
        });
    }

    public function hasPermission(string $role, string $permission): bool
    {
        return (int)$this->db->query(
            "SELECT COUNT(*) FROM `rbac_role_permissions` WHERE `role_name` = ? AND `permission_name` = ?",
            [$role, $permission]
        )->fetchColumn() > 0;
    }

    public function getRoutePermissionMap(): array
    {
        $rows = $this->db->query("SELECT * FROM `rbac_route_permissions` ORDER BY `route_name` ASC")->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['route_name']] = $row['permission_name'];
        }
        return $map;
    }

    public function saveRoutePermissions(array $mappings): void
    {
        $this->db->transaction(function () use ($mappings): void {
            $this->db->execute("DELETE FROM `rbac_route_permissions`");
            foreach ($mappings as $routeName => $permName) {
                $dbPermName = ($permName === '' || $permName === null) ? null : $permName;
                $this->db->execute(
                    "INSERT INTO `rbac_route_permissions` (`route_name`, `permission_name`) VALUES (?, ?)",
                    [$routeName, $dbPermName]
                );
            }
        });
    }

    public function createRole(string $name, string $description): bool
    {
        $this->db->execute("INSERT INTO `rbac_roles` (`name`, `description`) VALUES (?, ?)", [$name, $description]);
        return true;
    }

    public function updateRole(string $name, string $description): bool
    {
        $this->db->execute("UPDATE `rbac_roles` SET `description` = ? WHERE `name` = ?", [$description, $name]);
        return true;
    }

    public function deleteRole(string $name): bool
    {
        $this->db->execute("DELETE FROM `rbac_roles` WHERE `name` = ?", [$name]);
        return true;
    }

    public function getRoleByName(string $name): ?array
    {
        $role = $this->db->query("SELECT * FROM `rbac_roles` WHERE `name` = ?", [$name])->fetch();
        return $role ?: null;
    }

    public function createPermission(string $name, string $description): bool
    {
        $this->db->execute("INSERT INTO `rbac_permissions` (`name`, `description`) VALUES (?, ?)", [$name, $description]);
        return true;
    }

    public function updatePermission(string $name, string $description): bool
    {
        $this->db->execute("UPDATE `rbac_permissions` SET `description` = ? WHERE `name` = ?", [$description, $name]);
        return true;
    }

    public function deletePermission(string $name): bool
    {
        $this->db->execute("DELETE FROM `rbac_permissions` WHERE `name` = ?", [$name]);
        return true;
    }

    public function getPermissionByName(string $name): ?array
    {
        $perm = $this->db->query("SELECT * FROM `rbac_permissions` WHERE `name` = ?", [$name])->fetch();
        return $perm ?: null;
    }
}
