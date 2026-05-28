<?php

declare(strict_types=1);

namespace App\Web\Gii\Model;

use App\Environment;
use App\Web\Gii\Model\Generator\MvcGenerator;
use App\Web\Gii\Model\Generator\DddGenerator;
use PDO;

final class GiiService
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
    }

    public function getTables(): array
    {
        $stmt = $this->pdo->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getColumns(string $table): array
    {
        $stmt = $this->pdo->prepare("DESCRIBE `" . str_replace('`','', $table) . "`");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function generate(string $table, string $modelName, string $architecture, array &$logs): bool
    {
        $modelName = ucfirst(trim($modelName));
        $lowerModel = strtolower($modelName);

        if ($modelName === '') {
            $logs[] = 'Nama Model tidak boleh kosong.';
            return false;
        }

        $columns = $this->getColumns($table);
        if (empty($columns)) {
            $logs[] = "Tabel '{$table}' tidak ditemukan atau tidak memiliki kolom.";
            return false;
        }

        // Detect Primary Key details
        $primaryKey = 'id';
        $isAutoIncrement = false;
        $primaryKeyType = 'int';

        foreach ($columns as $col) {
            if ($col['Key'] === 'PRI') {
                $primaryKey = $col['Field'];
                if (str_contains($col['Extra'], 'auto_increment')) {
                    $isAutoIncrement = true;
                }
                if (!str_contains($col['Type'], 'int')) {
                    $primaryKeyType = 'string';
                }
                break;
            }
        }

        $dir = "/var/www/html/teqic-yii3/src/Web/{$modelName}";
        $subdirs = ['Controller', 'Model', 'View'];
        if ($architecture === 'ddd') {
            $subdirs[] = 'Service';
        }
        foreach ($subdirs as $subdir) {
            $path = "{$dir}/{$subdir}";
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
                chmod($path, 0777);
            }
        }

        if ($architecture === 'ddd') {
            $generator = new DddGenerator();
            $success = $generator->generate($table, $modelName, $lowerModel, $columns, $primaryKey, $isAutoIncrement, $primaryKeyType, $dir, $logs);
        } else {
            $generator = new MvcGenerator();
            $success = $generator->generate($table, $modelName, $lowerModel, $columns, $primaryKey, $isAutoIncrement, $primaryKeyType, $dir, $logs);
        }

        if ($success) {
            // 4. Inject Routes
            $this->injectRoutes($modelName, $lowerModel, $logs);

            // 5. Inject RBAC Seeding
            $this->injectRbacSeeding($modelName, $lowerModel, $logs);

            // 6. Inject Layout Navbar
            $this->injectLayoutNavbar($modelName, $lowerModel, $logs);
        }

        return $success;
    }

    private function injectRoutes(string $modelName, string $lowerModel, array &$logs): void
    {
        $routesPath = '/var/www/html/teqic-yii3/config/common/routes.php';
        $content = file_get_contents($routesPath);

        // Check if route group already exists
        if (str_contains($content, "Group::create('/{$lowerModel}')")) {
            $logs[] = "Rute '/{$lowerModel}' sudah terdaftar di routes.php.";
            return;
        }

        $inject = "\n    // {$modelName} Group protected by RBAC middleware\n";
        $inject .= "    Group::create('/{$lowerModel}')\n";
        $inject .= "        ->middleware(RbacAccessControlMiddleware::class)\n";
        $inject .= "        ->routes(\n";
        $inject .= "            Route::get('')\n";
        $inject .= "                ->action([Web\\{$modelName}\\Controller\\{$modelName}Controller::class, 'index'])\n";
        $inject .= "                ->name('{$lowerModel}/index'),\n";
        $inject .= "            Route::get('/create')\n";
        $inject .= "                ->action([Web\\{$modelName}\\Controller\\{$modelName}Controller::class, 'create'])\n";
        $inject .= "                ->name('{$lowerModel}/create'),\n";
        $inject .= "            Route::post('/create')\n";
        $inject .= "                ->action([Web\\{$modelName}\\Controller\\{$modelName}Controller::class, 'create'])\n";
        $inject .= "                ->name('{$lowerModel}/create/post'),\n";
        $inject .= "            Route::get('/update/{id:\d+}')\n";
        $inject .= "                ->action([Web\\{$modelName}\\Controller\\{$modelName}Controller::class, 'update'])\n";
        $inject .= "                ->name('{$lowerModel}/update'),\n";
        $inject .= "            Route::post('/update/{id:\d+}')\n";
        $inject .= "                ->action([Web\\{$modelName}\\Controller\\{$modelName}Controller::class, 'update'])\n";
        $inject .= "                ->name('{$lowerModel}/update/post'),\n";
        $inject .= "            Route::post('/delete/{id:\d+}')\n";
        $inject .= "                ->action([Web\\{$modelName}\\Controller\\{$modelName}Controller::class, 'delete'])\n";
        $inject .= "                ->name('{$lowerModel}/delete'),\n";
        $inject .= "        ),\n";

        $target = "    // RBAC Group protected by RBAC middleware";
        if (str_contains($content, $target)) {
            $content = str_replace($target, $inject . $target, $content);
            file_put_contents($routesPath, $content);
            $logs[] = "Rute otomatis didaftarkan di config/common/routes.php.";
        } else {
            $logs[] = "PERINGATAN: Target routes.php tidak ditemukan. Daftarkan rute secara manual.";
        }
    }

    private function injectRbacSeeding(string $modelName, string $lowerModel, array &$logs): void
    {
        $rbacPath = '/var/www/html/teqic-yii3/src/Web/Rbac/RbacRepository.php';
        $content = file_get_contents($rbacPath);

        if (str_contains($content, "view_{$lowerModel}")) {
            $logs[] = "Izin RBAC untuk '{$lowerModel}' sudah terdaftar.";
            return;
        }

        $inject = "\n        // Seed {$modelName} Permissions jika belum ada\n";
        $inject .= "        \${$lowerModel}Permissions = [\n";
        $inject .= "            ['name' => 'view_{$lowerModel}', 'description' => 'Melihat daftar dan detail data {$modelName}.'],\n";
        $inject .= "            ['name' => 'create_{$lowerModel}', 'description' => 'Menambahkan data {$modelName} baru.'],\n";
        $inject .= "            ['name' => 'update_{$lowerModel}', 'description' => 'Mengubah detail data {$modelName}.'],\n";
        $inject .= "            ['name' => 'delete_{$lowerModel}', 'description' => 'Menghapus data {$modelName}.'],\n";
        $inject .= "        ];\n";
        $inject .= "        foreach (\${$lowerModel}Permissions as \$perm) {\n";
        $inject .= "            \$checkStmt->execute(['name' => \$perm['name']]);\n";
        $inject .= "            if (\$checkStmt->fetchColumn() == 0) {\n";
        $inject .= "                \$insertPermStmt->execute(\$perm);\n";
        $inject .= "                \$this->pdo->prepare(\"INSERT IGNORE INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES ('Admin', :perm)\")\n";
        $inject .= "                    ->execute(['perm' => \$perm['name']]);\n";
        $inject .= "                if (\$perm['name'] !== 'delete_{$lowerModel}') {\n";
        $inject .= "                    \$this->pdo->prepare(\"INSERT IGNORE INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES ('Operator', :perm)\")\n";
        $inject .= "                        ->execute(['perm' => \$perm['name']]);\n";
        $inject .= "                }\n";
        $inject .= "            }\n";
        $inject .= "        }\n";
        $inject .= "        \${$lowerModel}RouteMappings = [\n";
        $inject .= "            ['route_name' => '{$lowerModel}/index', 'permission_name' => 'view_{$lowerModel}'],\n";
        $inject .= "            ['route_name' => '{$lowerModel}/create', 'permission_name' => 'create_{$lowerModel}'],\n";
        $inject .= "            ['route_name' => '{$lowerModel}/create/post', 'permission_name' => 'create_{$lowerModel}'],\n";
        $inject .= "            ['route_name' => '{$lowerModel}/update', 'permission_name' => 'update_{$lowerModel}'],\n";
        $inject .= "            ['route_name' => '{$lowerModel}/update/post', 'permission_name' => 'update_{$lowerModel}'],\n";
        $inject .= "            ['route_name' => '{$lowerModel}/delete', 'permission_name' => 'delete_{$lowerModel}'],\n";
        $inject .= "        ];\n";
        $inject .= "        foreach (\${$lowerModel}RouteMappings as \$mapping) {\n";
        $inject .= "            \$routeCheckStmt->execute(['route_name' => \$mapping['route_name']]);\n";
        $inject .= "            if (\$routeCheckStmt->fetchColumn() == 0) {\n";
        $inject .= "                \$routeInsertStmt->execute(\$mapping);\n";
        $inject .= "            }\n";
        $inject .= "        }\n";

        // Find the end of initializeTable() method
        // We'll search for the last mapping of konsulat:
        $target = "if (\$routeCheckStmt->fetchColumn() == 0) {\n                \$routeInsertStmt->execute(\$mapping);\n            }\n        }\n    }";

        if (str_contains($content, $target)) {
            $content = str_replace($target, "if (\$routeCheckStmt->fetchColumn() == 0) {\n                \$routeInsertStmt->execute(\$mapping);\n            }\n        }\n" . $inject . "    }", $content);
            file_put_contents($rbacPath, $content);
            $logs[] = "Izin RBAC & Route Mapping otomatis di-seed di RbacRepository.php.";
            
            // Execute RbacRepository constructor via reflection or directly inside the app to apply the seed right away
            try {
                $this->pdo->query("DELETE FROM `rbac_permissions` WHERE `name` LIKE '%{$lowerModel}%'");
                $this->pdo->query("DELETE FROM `rbac_route_permissions` WHERE `route_name` LIKE '%{$lowerModel}%'");
            } catch (\Exception $e) {}
        } else {
            $logs[] = "PERINGATAN: Target RbacRepository.php tidak ditemukan. Daftarkan izin secara manual.";
        }
    }

    private function injectLayoutNavbar(string $modelName, string $lowerModel, array &$logs): void
    {
        $layoutPath = '/var/www/html/teqic-yii3/src/Web/Shared/Layout/Main/layout.php';
        $content = file_get_contents($layoutPath);

        if (str_contains($content, "generate('{$lowerModel}/index')")) {
            $logs[] = "Link menu 'Data {$modelName}' sudah terdaftar di layout.php.";
            return;
        }

        $inject = "\n                    <" . "?php if (\$userSession->isLoggedIn() && \$userSession->hasPermission('view_{$lowerModel}')): " . "?" . ">\n";
        $inject .= "                        <a href=\"<" . "?= \$urlGenerator->generate('{$lowerModel}/index') ?" . ">\" class=\"<" . "?= str_starts_with((string)(\$currentRoute->getName() ?? ''), '{$lowerModel}') ? 'active' : '' ?" . ">\" title=\"Data {$modelName}\">\n";
        $inject .= "                            <svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\">\n";
        $inject .= "                                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5\" />\n";
        $inject .= "                            </svg>\n";
        $inject .= "                            <span>Data {$modelName}</span>\n";
        $inject .= "                        </a>\n";
        $inject .= "                    <" . "?php endif; " . "?" . ">";

        $target = "            <" . "?php if (\$userSession->isLoggedIn() && \$userSession->hasPermission('view_konsulat')): ?" . ">";
        if (str_contains($content, $target)) {
            // Find the end of that block
            $endBlock = "            <" . "?php endif; ?" . ">";
            $pos = strpos($content, $target);
            $endPos = strpos($content, $endBlock, $pos);
            if ($endPos !== false) {
                $insertPoint = $endPos + strlen($endBlock);
                $content = substr_replace($content, $inject, $insertPoint, 0);
                file_put_contents($layoutPath, $content);
                $logs[] = "Link menu otomatis ditambahkan ke navigasi layout.php.";
            }
        } else {
            $logs[] = "PERINGATAN: Menu navigasi layout.php tidak dapat diperbarui secara otomatis.";
        }
    }
}
