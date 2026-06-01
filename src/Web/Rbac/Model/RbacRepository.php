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
            ['name' => 'manage_rbac',          'description' => 'Mengelola RBAC (Role, Permission, User).'],
            ['name' => 'manage_gii',           'description' => 'Mengakses Gii Code Generator.'],
            ['name' => 'view_fungsionaris',    'description' => 'Melihat entitas Fungsionaris KMI.'],
            ['name' => 'create_fungsionaris',  'description' => 'Menambahkan entitas Fungsionaris KMI.'],
            ['name' => 'update_fungsionaris',  'description' => 'Mengubah entitas Fungsionaris KMI.'],
            ['name' => 'delete_fungsionaris',  'description' => 'Menghapus entitas Fungsionaris KMI.'],
            ['name' => 'view_kepanitiaan',     'description' => 'Melihat entitas Kepanitiaan KMI.'],
            ['name' => 'create_kepanitiaan',   'description' => 'Menambahkan entitas Kepanitiaan KMI.'],
            ['name' => 'update_kepanitiaan',   'description' => 'Mengubah entitas Kepanitiaan KMI.'],
            ['name' => 'delete_kepanitiaan',   'description' => 'Menghapus entitas Kepanitiaan KMI.'],
            ['name' => 'view_empowering',      'description' => 'Melihat entitas Empowering KMI.'],
            ['name' => 'create_empowering',    'description' => 'Menambahkan entitas Empowering KMI.'],
            ['name' => 'update_empowering',    'description' => 'Mengubah entitas Empowering KMI.'],
            ['name' => 'delete_empowering',    'description' => 'Menghapus entitas Empowering KMI.'],
            ['name' => 'view_koordinator',     'description' => 'Melihat entitas Koordinator KMI.'],
            ['name' => 'create_koordinator',   'description' => 'Menambahkan entitas Koordinator KMI.'],
            ['name' => 'update_koordinator',   'description' => 'Mengubah entitas Koordinator KMI.'],
            ['name' => 'delete_koordinator',   'description' => 'Menghapus entitas Koordinator KMI.'],
            ['name' => 'view_program',         'description' => 'Melihat Program Kerja.'],
            ['name' => 'create_program',       'description' => 'Menambahkan Program Kerja.'],
            ['name' => 'update_program',       'description' => 'Mengubah/mengelola Program Kerja.'],
            ['name' => 'delete_program',       'description' => 'Menghapus Program Kerja.'],
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
                'Admin'    => ['manage_rbac', 'manage_gii', 'view_fungsionaris', 'create_fungsionaris', 'update_fungsionaris', 'delete_fungsionaris', 'view_kepanitiaan', 'create_kepanitiaan', 'update_kepanitiaan', 'delete_kepanitiaan', 'view_empowering', 'create_empowering', 'update_empowering', 'delete_empowering', 'view_koordinator', 'create_koordinator', 'update_koordinator', 'delete_koordinator', 'view_program', 'create_program', 'update_program', 'delete_program'],
                'Operator' => ['view_fungsionaris', 'view_kepanitiaan', 'view_empowering', 'view_koordinator', 'view_program'],
                'Guru'     => ['view_program'],
            ];
            foreach ($matrix as $roleName => $perms) {
                foreach ($perms as $permName) {
                    $this->db->execute("INSERT IGNORE INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES (?, ?)", [$roleName, $permName]);
                }
            }
        }

        // Auto-assign all new permissions to Admin role
        $this->db->execute("
            INSERT IGNORE INTO `rbac_role_permissions` (`role_name`, `permission_name`)
            SELECT 'Admin', `name` FROM `rbac_permissions`
        ");

        // Seed Route Permissions
        $allRoutes = [
            ['route_name' => 'gii/index',                    'permission_name' => 'manage_gii'],
            ['route_name' => 'gii/generate',                 'permission_name' => 'manage_gii'],
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
            ['route_name' => 'fungsionaris/index',           'permission_name' => 'view_fungsionaris'],
            ['route_name' => 'fungsionaris/create',          'permission_name' => 'create_fungsionaris'],
            ['route_name' => 'fungsionaris/create/post',     'permission_name' => 'create_fungsionaris'],
            ['route_name' => 'fungsionaris/update',          'permission_name' => 'update_fungsionaris'],
            ['route_name' => 'fungsionaris/update/post',     'permission_name' => 'update_fungsionaris'],
            ['route_name' => 'fungsionaris/delete',          'permission_name' => 'delete_fungsionaris'],
            ['route_name' => 'fungsionaris/view',            'permission_name' => 'view_fungsionaris'],
            ['route_name' => 'fungsionaris/add-member',      'permission_name' => 'update_fungsionaris'],
            ['route_name' => 'fungsionaris/delete-member',   'permission_name' => 'update_fungsionaris'],
            ['route_name' => 'kepanitiaan/index',            'permission_name' => 'view_kepanitiaan'],
            ['route_name' => 'kepanitiaan/create',           'permission_name' => 'create_kepanitiaan'],
            ['route_name' => 'kepanitiaan/create/post',      'permission_name' => 'create_kepanitiaan'],
            ['route_name' => 'kepanitiaan/update',           'permission_name' => 'update_kepanitiaan'],
            ['route_name' => 'kepanitiaan/update/post',      'permission_name' => 'update_kepanitiaan'],
            ['route_name' => 'kepanitiaan/delete',           'permission_name' => 'delete_kepanitiaan'],
            ['route_name' => 'kepanitiaan/view',             'permission_name' => 'view_kepanitiaan'],
            ['route_name' => 'kepanitiaan/add-member',       'permission_name' => 'update_kepanitiaan'],
            ['route_name' => 'kepanitiaan/delete-member',    'permission_name' => 'update_kepanitiaan'],
            ['route_name' => 'empowering/index',            'permission_name' => 'view_empowering'],
            ['route_name' => 'empowering/create',           'permission_name' => 'create_empowering'],
            ['route_name' => 'empowering/create/post',      'permission_name' => 'create_empowering'],
            ['route_name' => 'empowering/update',           'permission_name' => 'update_empowering'],
            ['route_name' => 'empowering/update/post',      'permission_name' => 'update_empowering'],
            ['route_name' => 'empowering/delete',           'permission_name' => 'delete_empowering'],
            ['route_name' => 'empowering/view',             'permission_name' => 'view_empowering'],
            ['route_name' => 'empowering/add-member',       'permission_name' => 'update_empowering'],
            ['route_name' => 'empowering/delete-member',    'permission_name' => 'update_empowering'],
            ['route_name' => 'koordinator/index',            'permission_name' => 'view_koordinator'],
            ['route_name' => 'koordinator/create',           'permission_name' => 'create_koordinator'],
            ['route_name' => 'koordinator/create/post',      'permission_name' => 'create_koordinator'],
            ['route_name' => 'koordinator/update',           'permission_name' => 'update_koordinator'],
            ['route_name' => 'koordinator/update/post',      'permission_name' => 'update_koordinator'],
            ['route_name' => 'koordinator/delete',           'permission_name' => 'delete_koordinator'],
            ['route_name' => 'koordinator/view',             'permission_name' => 'view_koordinator'],
            ['route_name' => 'koordinator/add-member',       'permission_name' => 'update_koordinator'],
            ['route_name' => 'koordinator/delete-member',    'permission_name' => 'update_koordinator'],
            ['route_name' => 'koordinator/update-member',    'permission_name' => 'update_koordinator'],
            ['route_name' => 'program/create',               'permission_name' => 'create_program'],
            ['route_name' => 'program/create/post',          'permission_name' => 'create_program'],
            ['route_name' => 'program/update',               'permission_name' => 'update_program'],
            ['route_name' => 'program/update/post',          'permission_name' => 'update_program'],
            ['route_name' => 'program/delete',               'permission_name' => 'delete_program'],
            ['route_name' => 'program/view',                 'permission_name' => 'view_program'],
            ['route_name' => 'program/add-kendala',          'permission_name' => 'update_program'],
            ['route_name' => 'program/resolve-kendala',      'permission_name' => 'update_program'],
            ['route_name' => 'program/delete-kendala',       'permission_name' => 'update_program'],
            ['route_name' => 'program/add-notulensi',        'permission_name' => 'update_program'],
            ['route_name' => 'program/delete-notulensi',     'permission_name' => 'update_program'],
            ['route_name' => 'program/add-dokumentasi',      'permission_name' => 'update_program'],
            ['route_name' => 'program/delete-dokumentasi',   'permission_name' => 'update_program'],
            ['route_name' => 'monitor/index',                'permission_name' => null],
            ['route_name' => 'kanban/board',                 'permission_name' => 'view_program'],
            ['route_name' => 'kanban/add-task',              'permission_name' => 'update_program'],
            ['route_name' => 'kanban/edit-task',             'permission_name' => 'update_program'],
            ['route_name' => 'kanban/delete-task',            'permission_name' => 'update_program'],
            ['route_name' => 'kanban/move-task',             'permission_name' => 'update_program'],
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
