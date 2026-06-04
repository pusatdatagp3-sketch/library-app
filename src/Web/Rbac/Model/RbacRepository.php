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
                ['name' => 'Operator', 'description' => 'Staf / Operator dengan akses terbatas.'],
                ['name' => 'Guru',     'description' => 'Pengguna Guru dengan akses dasar.'],
            ];
            foreach ($roles as $role) {
                $this->db->execute("INSERT INTO `rbac_roles` (`name`, `description`) VALUES (?, ?)", [$role['name'], $role['description']]);
            }
        }
        // Seed Permissions
        $allPermissions = [
            ['name' => 'manage_rbac',          'description' => 'Mengelola RBAC (Role, Permission, User).'],
            ['name' => 'manage_gii',           'description' => 'Mengakses Gii Code Generator.'],
        ];
        foreach ($allPermissions as $perm) {
            $exists = (int)$this->db->query("SELECT COUNT(*) FROM `rbac_permissions` WHERE `name` = ?", [$perm['name']])->fetchColumn();
            if ($exists === 0) {
                $this->db->execute("INSERT INTO `rbac_permissions` (`name`, `description`) VALUES (?, ?)", [$perm['name'], $perm['description']]);
            }
        }

        // Clean up permissions not in the seed list
        $permNames = array_column($allPermissions, 'name');
        if (!empty($permNames)) {
            $inClause = implode(',', array_map(fn($n) => $this->db->getDriver()->quote($n), $permNames));
            $this->db->execute("DELETE FROM `rbac_permissions` WHERE `name` NOT IN ($inClause)");
        } else {
            $this->db->execute("DELETE FROM `rbac_permissions`");
        }

        // Seed Role Permissions matrix
        if ((int)$this->db->query("SELECT COUNT(*) FROM `rbac_role_permissions`")->fetchColumn() === 0) {
            $matrix = [
                'Admin'    => ['manage_rbac', 'manage_gii'],
                'Operator' => [],
                'Guru'     => [],
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
        ];
        foreach ($allRoutes as $mapping) {
            $exists = (int)$this->db->query("SELECT COUNT(*) FROM `rbac_route_permissions` WHERE `route_name` = ?", [$mapping['route_name']])->fetchColumn();
            if ($exists === 0) {
                $this->db->execute("INSERT INTO `rbac_route_permissions` (`route_name`, `permission_name`) VALUES (?, ?)", [$mapping['route_name'], $mapping['permission_name']]);
            }
        }

        // Clean up route permissions not in the seed list
        $routeNames = array_column($allRoutes, 'route_name');
        if (!empty($routeNames)) {
            $inClause = implode(',', array_map(fn($n) => $this->db->getDriver()->quote($n), $routeNames));
            $this->db->execute("DELETE FROM `rbac_route_permissions` WHERE `route_name` NOT IN ($inClause)");
        } else {
            $this->db->execute("DELETE FROM `rbac_route_permissions`");
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
