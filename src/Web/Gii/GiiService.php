<?php

declare(strict_types=1);

namespace App\Web\Gii;

use App\Environment;
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

    public function generate(string $table, string $modelName, array &$logs): bool
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
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }
        if (!is_dir($dir . '/views')) {
            mkdir($dir . '/views', 0777, true);
            chmod($dir . '/views', 0777);
        }

        // 1. Generate Model
        $modelCode = $this->buildModelCode($table, $modelName, $columns, $primaryKey, $isAutoIncrement, $primaryKeyType);
        file_put_contents("{$dir}/{$modelName}.php", $modelCode);
        chmod("{$dir}/{$modelName}.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/{$modelName}.php";

        // 2. Generate Controller
        $controllerCode = $this->buildControllerCode($modelName, $lowerModel, $primaryKey, $primaryKeyType);
        file_put_contents("{$dir}/{$modelName}Controller.php", $controllerCode);
        chmod("{$dir}/{$modelName}Controller.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/{$modelName}Controller.php";

        // 3. Generate Views
        $indexCode = $this->buildIndexView($modelName, $lowerModel, $columns, $primaryKey);
        file_put_contents("{$dir}/views/index.php", $indexCode);
        chmod("{$dir}/views/index.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/views/index.php";

        $createCode = $this->buildCreateView($modelName, $lowerModel, $columns, $primaryKey, $isAutoIncrement);
        file_put_contents("{$dir}/views/create.php", $createCode);
        chmod("{$dir}/views/create.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/views/create.php";

        $updateCode = $this->buildUpdateView($modelName, $lowerModel, $columns, $primaryKey, $isAutoIncrement);
        file_put_contents("{$dir}/views/update.php", $updateCode);
        chmod("{$dir}/views/update.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/views/update.php";

        // 4. Inject Routes
        $this->injectRoutes($modelName, $lowerModel, $logs);

        // 5. Inject RBAC Seeding
        $this->injectRbacSeeding($modelName, $lowerModel, $logs);

        // 6. Inject Layout Navbar
        $this->injectLayoutNavbar($modelName, $lowerModel, $logs);

        return true;
    }

    private function buildModelCode(string $table, string $modelName, array $columns, string $primaryKey, bool $isAutoIncrement, string $primaryKeyType): string
    {
        $properties = '';
        $loadFields = '';
        $validationRules = '';
        $dbMappings = '';
        $insertParams = [];
        $insertBindings = [];
        $updateBindings = [];

        foreach ($columns as $col) {
            $field = $col['Field'];
            $type = $col['Type'];
            $isNull = $col['Null'] === 'YES';
            $extra = $col['Extra'];

            // Property data type mapping
            $phpType = 'string';
            if (str_contains($type, 'int')) {
                $phpType = 'int';
            } elseif (str_contains($type, 'float') || str_contains($type, 'double') || str_contains($type, 'decimal')) {
                $phpType = 'float';
            }

            if ($field === $primaryKey) {
                $properties .= "    public ?{$primaryKeyType} \${$primaryKey} = null;\n";
                
                // If not auto-increment, load and validate
                if (!$isAutoIncrement) {
                    if ($primaryKeyType === 'int') {
                        $loadFields .= "        \$this->{$primaryKey} = isset(\$data['{$primaryKey}']) && \$data['{$primaryKey}'] !== '' ? (int)\$data['{$primaryKey}'] : null;\n";
                    } else {
                        $loadFields .= "        \$this->{$primaryKey} = isset(\$data['{$primaryKey}']) ? trim((string)\$data['{$primaryKey}']) : null;\n";
                    }
                    
                    $validationRules .= "        if (\$this->{$primaryKey} === '' || \$this->{$primaryKey} === null) {\n";
                    $validationRules .= "            \$this->errors['{$primaryKey}'] = 'Kolom " . ucfirst($primaryKey) . " tidak boleh kosong.';\n";
                    $validationRules .= "        }\n";
                    
                    $insertParams[] = "`{$primaryKey}`";
                    $insertBindings[] = ":{$primaryKey}";
                }
            } else {
                $defaultValue = $isNull ? 'null' : ($phpType === 'int' || $phpType === 'float' ? '0' : "''");
                $nullablePrefix = $isNull ? '?' : '';
                $properties .= "    public {$nullablePrefix}{$phpType} \${$field} = {$defaultValue};\n";

                // Load mapping
                if ($phpType === 'int') {
                    $loadFields .= "        \$this->{$field} = isset(\$data['{$field}']) && \$data['{$field}'] !== '' ? (int)\$data['{$field}'] : " . ($isNull ? 'null' : '0') . ";\n";
                } elseif ($phpType === 'float') {
                    $loadFields .= "        \$this->{$field} = isset(\$data['{$field}']) && \$data['{$field}'] !== '' ? (float)\$data['{$field}'] : " . ($isNull ? 'null' : '0.0') . ";\n";
                } else {
                    $loadFields .= "        \$this->{$field} = trim((string)(\$data['{$field}'] ?? \$this->{$field}));\n";
                }

                // Validation rules
                if (!$isNull && !str_contains($extra, 'auto_increment') && $col['Default'] === null) {
                    $validationRules .= "        if (\$this->{$field} === '' || \$this->{$field} === null) {\n";
                    $validationRules .= "            \$this->errors['{$field}'] = 'Kolom " . ucfirst($field) . " tidak boleh kosong.';\n";
                    $validationRules .= "        }\n";
                }

                $insertParams[] = "`{$field}`";
                $insertBindings[] = ":{$field}";
                $updateBindings[] = "`{$field}` = :{$field}";
            }

            // DB Mapper from Row
            if ($field === $primaryKey) {
                $dbMappings .= "            \$model->{$primaryKey} = isset(\$row['{$primaryKey}']) ? ({$primaryKeyType}) \$row['{$primaryKey}'] : null;\n";
            } else {
                if ($phpType === 'int') {
                    $dbMappings .= "            \$model->{$field} = isset(\$row['{$field}']) ? (int) \$row['{$field}'] : null;\n";
                } elseif ($phpType === 'float') {
                    $dbMappings .= "            \$model->{$field} = isset(\$row['{$field}']) ? (float) \$row['{$field}'] : null;\n";
                } else {
                    $dbMappings .= "            \$model->{$field} = (string) (\$row['{$field}'] ?? '');\n";
                }
            }
        }

        $insertParamsStr = implode(', ', $insertParams);
        $insertBindingsStr = implode(', ', $insertBindings);
        $updateBindingsStr = implode(', ', $updateBindings);

        // Execute Bindings
        $insertBindingsSql = '';
        $updateBindingsSql = '';
        foreach ($columns as $col) {
            $field = $col['Field'];
            if ($field === $primaryKey) {
                if (!$isAutoIncrement) {
                    $insertBindingsSql .= "                '{$field}' => \$this->{$field},\n";
                }
            } else {
                $insertBindingsSql .= "                '{$field}' => \$this->{$field},\n";
                $updateBindingsSql .= "                '{$field}' => \$this->{$field},\n";
            }
        }
        $updateBindingsSql .= "                '{$primaryKey}' => \$this->{$primaryKey},\n";

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "namespace App\Web\\{$modelName};\n\n";
        $code .= "use App\Environment;\n";
        $code .= "use PDO;\n\n";
        $code .= "final class {$modelName}\n";
        $code .= "{\n";
        $code .= $properties;
        $code .= "\n    public bool \$isNewRecord = true;\n";
        $code .= "    public array \$errors = [];\n\n";
        $code .= "    private static ?PDO \$pdo = null;\n\n";
        $code .= "    private static function getDb(): PDO\n";
        $code .= "    {\n";
        $code .= "        if (self::\$pdo === null) {\n";
        $code .= "            \$host = Environment::dbHost();\n";
        $code .= "            \$port = Environment::dbPort();\n";
        $code .= "            \$dbname = Environment::dbName();\n";
        $code .= "            \$user = Environment::dbUser();\n";
        $code .= "            \$password = Environment::dbPassword();\n\n";
        $code .= "            \$dsn = \"mysql:host={\$host};port={\$port};dbname={\$dbname};charset=utf8mb4\";\n";
        $code .= "            self::\$pdo = new PDO(\$dsn, \$user, \$password, [\n";
        $code .= "                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n";
        $code .= "                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n";
        $code .= "            ]);\n";
        $code .= "        }\n";
        $code .= "        return self::\$pdo;\n";
        $code .= "    }\n\n";
        $code .= "    public function load(array \$data): bool\n";
        $code .= "    {\n";
        $code .= $loadFields;
        $code .= "        return !empty(\$data);\n";
        $code .= "    }\n\n";
        $code .= "    public function validate(): bool\n";
        $code .= "    {\n";
        $code .= "        \$this->errors = [];\n";
        $code .= $validationRules;
        $code .= "        return empty(\$this->errors);\n";
        $code .= "    }\n\n";
        $code .= "    public function save(): bool\n";
        $code .= "    {\n";
        $code .= "        if (!\$this->validate()) {\n";
        $code .= "            return false;\n";
        $code .= "        }\n\n";
        $code .= "        \$db = self::getDb();\n";
        $code .= "        if (\$this->isNewRecord) {\n";
        $code .= "            \$stmt = \$db->prepare(\"INSERT INTO `{$table}` ({$insertParamsStr}) VALUES ({$insertBindingsStr})\");\n";
        $code .= "            \$result = \$stmt->execute([\n";
        $code .= $insertBindingsSql;
        $code .= "            ]);\n";
        $code .= "            if (\$result) {\n";
        if ($isAutoIncrement) {
            $code .= "                \$this->{$primaryKey} = (int) \$db->lastInsertId();\n";
        }
        $code .= "                \$this->isNewRecord = false;\n";
        $code .= "                return true;\n";
        $code .= "            }\n";
        $code .= "            return false;\n";
        $code .= "        }\n\n";
        $code .= "        \$stmt = \$db->prepare(\"UPDATE `{$table}` SET {$updateBindingsStr} WHERE `{$primaryKey}` = :{$primaryKey}\");\n";
        $code .= "        return \$stmt->execute([\n";
        $code .= $updateBindingsSql;
        $code .= "        ]);\n";
        $code .= "    }\n\n";
        $code .= "    public function delete(): bool\n";
        $code .= "    {\n";
        $code .= "        if (\$this->{$primaryKey} === null) {\n";
        $code .= "            return false;\n";
        $code .= "        }\n";
        $code .= "        \$db = self::getDb();\n";
        $code .= "        \$stmt = \$db->prepare(\"DELETE FROM `{$table}` WHERE `{$primaryKey}` = :{$primaryKey}\");\n";
        $code .= "        return \$stmt->execute(['{$primaryKey}' => \$this->{$primaryKey}]);\n";
        $code .= "    }\n\n";
        $code .= "    /**\n";
        $code .= "     * @return self[]\n";
        $code .= "     */\n";
        $code .= "    public static function find(): array\n";
        $code .= "    {\n";
        $code .= "        \$db = self::getDb();\n";
        $code .= "        \$stmt = \$db->query(\"SELECT * FROM `{$table}` ORDER BY `{$primaryKey}` DESC\");\n";
        $code .= "        \$rows = \$stmt->fetchAll();\n\n";
        $code .= "        \$models = [];\n";
        $code .= "        foreach (\$rows as \$row) {\n";
        $code .= "            \$model = new self();\n";
        $code .= "            \$model->isNewRecord = false;\n";
        $code .= $dbMappings;
        $code .= "            \$models[] = \$model;\n";
        $code .= "        }\n";
        $code .= "        return \$models;\n";
        $code .= "    }\n\n";
        $code .= "    public static function findOne({$primaryKeyType} \${$primaryKey}): ?self\n";
        $code .= "    {\n";
        $code .= "        \$db = self::getDb();\n";
        $code .= "        \$stmt = \$db->prepare(\"SELECT * FROM `{$table}` WHERE `{$primaryKey}` = :{$primaryKey} LIMIT 1\");\n";
        $code .= "        \$stmt->execute(['{$primaryKey}' => \${$primaryKey}]);\n";
        $code .= "        \$row = \$stmt->fetch();\n\n";
        $code .= "        if (!\$row) {\n";
        $code .= "            return null;\n";
        $code .= "        }\n\n";
        $code .= "        \$model = new self();\n";
        $code .= "        \$model->isNewRecord = false;\n";
        $code .= $dbMappings;
        $code .= "        return \$model;\n";
        $code .= "    }\n";
        $code .= "}\n";

        return $code;
    }

    private function buildControllerCode(string $modelName, string $lowerModel, string $primaryKey, string $primaryKeyType): string
    {
        $pkCast = $primaryKeyType === 'int' ? '(int)' : '(string)';

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "namespace App\Web\\{$modelName};\n\n";
        $code .= "use Psr\Http\Message\ResponseInterface;\n";
        $code .= "use Psr\Http\Message\ServerRequestInterface;\n";
        $code .= "use Psr\Http\Message\ResponseFactoryInterface;\n";
        $code .= "use Yiisoft\Router\CurrentRoute;\n";
        $code .= "use Yiisoft\Router\UrlGeneratorInterface;\n";
        $code .= "use Yiisoft\Yii\View\Renderer\WebViewRenderer;\n";
        $code .= "use Yiisoft\Session\Flash\FlashInterface;\n\n";
        $code .= "final class {$modelName}Controller\n";
        $code .= "{\n";
        $code .= "    public function __construct(\n";
        $code .= "        private WebViewRenderer \$viewRenderer,\n";
        $code .= "        private UrlGeneratorInterface \$urlGenerator,\n";
        $code .= "        private ResponseFactoryInterface \$responseFactory,\n";
        $code .= "        private CurrentRoute \$currentRoute,\n";
        $code .= "        private FlashInterface \$flash\n";
        $code .= "    ) {\n";
        $code .= "    }\n\n";
        $code .= "    public function index(): ResponseInterface\n";
        $code .= "    {\n";
        $code .= "        \$models = {$modelName}::find();\n";
        $code .= "        return \$this->viewRenderer->render(__DIR__ . '/views/index', [\n";
        $code .= "            'models' => \$models,\n";
        $code .= "            'successMsg' => \$this->flash->get('success'),\n";
        $code .= "            'errorMsgs' => \$this->flash->get('errors') ?? [],\n";
        $code .= "        ]);\n";
        $code .= "    }\n\n";
        $code .= "    public function create(ServerRequestInterface \$request): ResponseInterface\n";
        $code .= "    {\n";
        $code .= "        \$model = new {$modelName}();\n\n";
        $code .= "        if (\$request->getMethod() === 'POST') {\n";
        $code .= "            \$data = (array) \$request->getParsedBody();\n";
        $code .= "            \$model->load(\$data);\n";
        $code .= "            if (\$model->save()) {\n";
        $code .= "                \$this->flash->set('success', 'Data {$modelName} berhasil ditambahkan.');\n";
        $code .= "                return \$this->responseFactory->createResponse(302)\n";
        $code .= "                    ->withHeader('Location', \$this->urlGenerator->generate('{$lowerModel}/index'));\n";
        $code .= "            }\n";
        $code .= "        }\n\n";
        $code .= "        return \$this->viewRenderer->render(__DIR__ . '/views/create', [\n";
        $code .= "            'model' => \$model,\n";
        $code .= "        ]);\n";
        $code .= "    }\n\n";
        $code .= "    public function update(ServerRequestInterface \$request): ResponseInterface\n";
        $code .= "    {\n";
        $code .= "        \$id = {$pkCast} \$this->currentRoute->getArgument('id');\n";
        $code .= "        \$model = {$modelName}::findOne(\$id);\n\n";
        $code .= "        if (\$model === null) {\n";
        $code .= "            return \$this->responseFactory->createResponse(404);\n";
        $code .= "        }\n\n";
        $code .= "        if (\$request->getMethod() === 'POST') {\n";
        $code .= "            \$data = (array) \$request->getParsedBody();\n";
        $code .= "            \$model->load(\$data);\n";
        $code .= "            if (\$model->save()) {\n";
        $code .= "                \$this->flash->set('success', 'Data {$modelName} berhasil diperbarui.');\n";
        $code .= "                return \$this->responseFactory->createResponse(302)\n";
        $code .= "                    ->withHeader('Location', \$this->urlGenerator->generate('{$lowerModel}/index'));\n";
        $code .= "            }\n";
        $code .= "        }\n\n";
        $code .= "        return \$this->viewRenderer->render(__DIR__ . '/views/update', [\n";
        $code .= "            'model' => \$model,\n";
        $code .= "        ]);\n";
        $code .= "    }\n\n";
        $code .= "    public function delete(): ResponseInterface\n";
        $code .= "    {\n";
        $code .= "        \$id = {$pkCast} \$this->currentRoute->getArgument('id');\n";
        $code .= "        \$model = {$modelName}::findOne(\$id);\n\n";
        $code .= "        if (\$model !== null) {\n";
        $code .= "            \$model->delete();\n";
        $code .= "            \$this->flash->set('success', 'Data {$modelName} berhasil dihapus.');\n";
        $code .= "        }\n\n";
        $code .= "        return \$this->responseFactory->createResponse(302)\n";
        $code .= "            ->withHeader('Location', \$this->urlGenerator->generate('{$lowerModel}/index'));\n";
        $code .= "    }\n";
        $code .= "}\n";

        return $code;
    }

    private function buildIndexView(string $modelName, string $lowerModel, array $columns, string $primaryKey): string
    {
        $headers = '';
        $values = '';

        foreach ($columns as $col) {
            $field = $col['Field'];
            if ($field === $primaryKey) {
                $headers .= "                        <th style=\"width: 80px;\">" . strtoupper($primaryKey) . "</th>\n";
                $values .= "                            <td><span class=\"badge badge-id\"><?= Html::encode((string)\$model->{$primaryKey}) ?></span></td>\n";
            } else {
                $headers .= "                        <th>" . ucfirst($field) . "</th>\n";
                $values .= "                            <td><?= Html::encode((string)\$model->{$field}) ?></td>\n";
            }
        }

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "use Yiisoft\Html\Html;\n";
        $code .= "use Yiisoft\View\WebView;\n";
        $code .= "use Yiisoft\Router\UrlGeneratorInterface;\n\n";
        $code .= "/**\n";
        $code .= " * @var WebView \$this\n";
        $code .= " * @var App\Web\\{$modelName}\\{$modelName}[] \$models\n";
        $code .= " * @var UrlGeneratorInterface \$urlGenerator\n";
        $code .= " * @var \App\Web\Auth\UserSession \$userSession\n";
        $code .= " * @var string|null \$successMsg\n";
        $code .= " * @var array \$errorMsgs\n";
        $code .= " */\n\n";
        $code .= "\$this->setTitle('Data {$modelName}');\n";
        $code .= "?>\n\n";
        $code .= "<div class=\"crud-container\">\n";
        $code .= "    <div class=\"crud-header\">\n";
        $code .= "        <div>\n";
        $code .= "            <h1 class=\"crud-title\">Data {$modelName}</h1>\n";
        $code .= "            <p class=\"crud-subtitle\">Kelola informasi data {$lowerModel} di sistem.</p>\n";
        $code .= "        </div>\n";
        $code .= "        \n";
        $code .= "        <?php if (\$userSession->hasPermission('create_{$lowerModel}')): " . '?' . ">\n";
        $code .= "            <a href=\"<?= \$urlGenerator->generate('{$lowerModel}/create') ?>\" class=\"btn btn-primary\">\n";
        $code .= "                <svg class=\"btn-icon\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\">\n";
        $code .= "                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M12 4.5v15m7.5-7.5h-15\" />\n";
        $code .= "                </svg>\n";
        $code .= "                Tambah {$modelName}\n";
        $code .= "            </a>\n";
        $code .= "        <?php endif; " . '?' . ">\n";
        $code .= "    </div>\n\n";
        $code .= "    <?php if (\$successMsg): " . '?' . ">\n";
        $code .= "        <div class=\"alert alert-success\">\n";
        $code .= "            <svg class=\"alert-icon\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\">\n";
        $code .= "                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z\" />\n";
        $code .= "            </svg>\n";
        $code .= "            <div><?= Html::encode(\$successMsg) ?></div>\n";
        $code .= "        </div>\n";
        $code .= "    <?php endif; " . '?' . ">\n\n";
        $code .= "    <?php if (empty(\$models)): " . '?' . ">\n";
        $code .= "        <div class=\"card empty-state text-center\">\n";
        $code .= "            <svg class=\"empty-icon\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\">\n";
        $code .= "                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v3m0 0h4.5V12c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v9\" />\n";
        $code .= "            </svg>\n";
        $code .= "            <h3>Belum Ada Data {$modelName}</h3>\n";
        $code .= "            <p class=\"text-muted\">Gunakan tombol di atas untuk menambahkan data baru.</p>\n";
        $code .= "        </div>\n";
        $code .= "    <?php else: " . '?' . ">\n";
        $code .= "        <div class=\"table-responsive card\">\n";
        $code .= "            <table class=\"table\">\n";
        $code .= "                <thead>\n";
        $code .= "                    <tr>\n";
        $code .= $headers;
        $code .= "                        <?php if (\$userSession->hasPermission('update_{$lowerModel}') || \$userSession->hasPermission('delete_{$lowerModel}')): " . '?' . ">\n";
        $code .= "                            <th class=\"text-center\" style=\"width: 180px;\">Aksi</th>\n";
        $code .= "                        <?php endif; " . '?' . ">\n";
        $code .= "                    </tr>\n";
        $code .= "                </thead>\n";
        $code .= "                <tbody>\n";
        $code .= "                    <?php foreach (\$models as \$model): " . '?' . ">\n";
        $code .= "                        <tr>\n";
        $code .= $values;
        $code .= "                            <?php if (\$userSession->hasPermission('update_{$lowerModel}') || \$userSession->hasPermission('delete_{$lowerModel}')): " . '?' . ">\n";
        $code .= "                                <td>\n";
        $code .= "                                    <div class=\"action-buttons\">\n";
        $code .= "                                        <?php if (\$userSession->hasPermission('update_{$lowerModel}')): " . '?' . ">\n";
        $code .= "                                            <a href=\"<?= \$urlGenerator->generate('{$lowerModel}/update', ['id' => \$model->{$primaryKey}]) ?>\" class=\"btn-action btn-edit\">\n";
        $code .= "                                                Edit\n";
        $code .= "                                            </a>\n";
        $code .= "                                        <?php endif; " . '?' . ">\n\n";
        $code .= "                                        <?php if (\$userSession->hasPermission('delete_{$lowerModel}')): " . '?' . ">\n";
        $code .= "                                            <form action=\"<?= \$urlGenerator->generate('{$lowerModel}/delete', ['id' => \$model->{$primaryKey}]) ?>\" method=\"POST\" onsubmit=\"return confirm('Apakah Anda yakin ingin menghapus data ini?');\" style=\"display:inline;\">\n";
        $code .= "                                                <input type=\"hidden\" name=\"_csrf\" value=\"<?= Html::encode(\$this->getParameter('csrf')) ?>\">\n";
        $code .= "                                                <button type=\"submit\" class=\"btn-action btn-delete\">\n";
        $code .= "                                                    Hapus\n";
        $code .= "                                                </button>\n";
        $code .= "                                            </form>\n";
        $code .= "                                        <?php endif; " . '?' . ">\n";
        $code .= "                                    </div>\n";
        $code .= "                                </td>\n";
        $code .= "                            <?php endif; " . '?' . ">\n";
        $code .= "                        </tr>\n";
        $code .= "                    <?php endforeach; " . '?' . ">\n";
        $code .= "                </tbody>\n";
        $code .= "            </table>\n";
        $code .= "        </div>\n";
        $code .= "    <?php endif; " . '?' . ">\n";
        $code .= "</div>\n";

        return $code;
    }

    private function buildCreateView(string $modelName, string $lowerModel, array $columns, string $primaryKey, bool $isAutoIncrement): string
    {
        $fieldsHtml = '';
        foreach ($columns as $col) {
            $field = $col['Field'];
            if ($field === $primaryKey && $isAutoIncrement) continue;

            $fieldsHtml .= "            <div class=\"form-group\">\n";
            $fieldsHtml .= "                <label for=\"{$field}\" class=\"form-label\">" . ucfirst($field) . "</label>\n";
            $fieldsHtml .= "                <input type=\"text\" id=\"{$field}\" name=\"{$field}\" class=\"form-control <?= isset(\$model->errors['{$field}']) ? 'is-invalid' : '' ?>\" value=\"<?= Html::encode(\$model->{$field}) ?>\">\n";
            $fieldsHtml .= "                <?php if (isset(\$model->errors['{$field}'])): " . '?' . ">\n";
            $fieldsHtml .= "                    <div class=\"invalid-feedback\"><?= Html::encode(\$model->errors['{$field}']) ?></div>\n";
            $fieldsHtml .= "                <?php endif; " . '?' . ">\n";
            $fieldsHtml .= "            </div>\n\n";
        }

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "use Yiisoft\Html\Html;\n";
        $code .= "use Yiisoft\View\WebView;\n";
        $code .= "use Yiisoft\Router\UrlGeneratorInterface;\n\n";
        $code .= "/**\n";
        $code .= " * @var WebView \$this\n";
        $code .= " * @var App\Web\\{$modelName}\\{$modelName} \$model\n";
        $code .= " * @var UrlGeneratorInterface \$urlGenerator\n";
        $code .= " */\n\n";
        $code .= "\$this->setTitle('Tambah {$modelName} Baru');\n";
        $code .= "?>\n\n";
        $code .= "<div class=\"crud-container max-w-lg\">\n";
        $code .= "    <div class=\"crud-header\">\n";
        $code .= "        <div>\n";
        $code .= "            <h1 class=\"crud-title\">Tambah {$modelName}</h1>\n";
        $code .= "            <p class=\"crud-subtitle\">Isi form di bawah ini untuk menambahkan data {$lowerModel} baru.</p>\n";
        $code .= "        </div>\n";
        $code .= "        <a href=\"<?= \$urlGenerator->generate('{$lowerModel}/index') ?>\" class=\"btn btn-secondary\">\n";
        $code .= "            Kembali\n";
        $code .= "        </a>\n";
        $code .= "    </div>\n\n";
        $code .= "    <div class=\"card\">\n";
        $code .= "        <form action=\"<?= \$urlGenerator->generate('{$lowerModel}/create') ?>\" method=\"POST\" class=\"form-grid\">\n";
        $code .= "            <input type=\"hidden\" name=\"_csrf\" value=\"<?= Html::encode((string)\$this->getParameter('csrf')) ?>\">\n\n";
        $code .= $fieldsHtml;
        $code .= "            <div class=\"form-actions\">\n";
        $code .= "                <button type=\"reset\" class=\"btn btn-secondary\">Reset</button>\n";
        $code .= "                <button type=\"submit\" class=\"btn btn-primary\">\n";
        $code .= "                    Simpan Data\n";
        $code .= "                </button>\n";
        $code .= "            </div>\n";
        $code .= "        </form>\n";
        $code .= "    </div>\n";
        $code .= "</div>\n";

        return $code;
    }

    private function buildUpdateView(string $modelName, string $lowerModel, array $columns, string $primaryKey, bool $isAutoIncrement): string
    {
        $fieldsHtml = '';
        foreach ($columns as $col) {
            $field = $col['Field'];
            if ($field === $primaryKey && $isAutoIncrement) continue;

            $fieldsHtml .= "            <div class=\"form-group\">\n";
            $fieldsHtml .= "                <label for=\"{$field}\" class=\"form-label\">" . ucfirst($field) . "</label>\n";
            $fieldsHtml .= "                <input type=\"text\" id=\"{$field}\" name=\"{$field}\" class=\"form-control <?= isset(\$model->errors['{$field}']) ? 'is-invalid' : '' ?>\" value=\"<?= Html::encode(\$model->{$field}) ?>\">\n";
            $fieldsHtml .= "                <?php if (isset(\$model->errors['{$field}'])): " . '?' . ">\n";
            $fieldsHtml .= "                    <div class=\"invalid-feedback\"><?= Html::encode(\$model->errors['{$field}']) ?></div>\n";
            $fieldsHtml .= "                <?php endif; " . '?' . ">\n";
            $fieldsHtml .= "            </div>\n\n";
        }

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "use Yiisoft\Html\Html;\n";
        $code .= "use Yiisoft\View\WebView;\n";
        $code .= "use Yiisoft\Router\UrlGeneratorInterface;\n\n";
        $code .= "/**\n";
        $code .= " * @var WebView \$this\n";
        $code .= " * @var App\Web\\{$modelName}\\{$modelName} \$model\n";
        $code .= " * @var UrlGeneratorInterface \$urlGenerator\n";
        $code .= " */\n\n";
        $code .= "\$this->setTitle('Edit Data {$modelName}');\n";
        $code .= "?>\n\n";
        $code .= "<div class=\"crud-container max-w-lg\">\n";
        $code .= "    <div class=\"crud-header\">\n";
        $code .= "        <div>\n";
        $code .= "            <h1 class=\"crud-title\">Edit Data {$modelName}</h1>\n";
        $code .= "            <p class=\"crud-subtitle\">Perbarui data {$lowerModel} terpilih.</p>\n";
        $code .= "        </div>\n";
        $code .= "        <a href=\"<?= \$urlGenerator->generate('{$lowerModel}/index') ?>\" class=\"btn btn-secondary\">\n";
        $code .= "            Kembali\n";
        $code .= "        </a>\n";
        $code .= "    </div>\n\n";
        $code .= "    <div class=\"card\">\n";
        $code .= "        <form action=\"<?= \$urlGenerator->generate('{$lowerModel}/update', ['id' => \$model->{$primaryKey}]) ?>\" method=\"POST\" class=\"form-grid\">\n";
        $code .= "            <input type=\"hidden\" name=\"_csrf\" value=\"<?= Html::encode((string)\$this->getParameter('csrf')) ?>\">\n\n";
        $code .= $fieldsHtml;
        $code .= "            <div class=\"form-actions\">\n";
        $code .= "                <a href=\"<?= \$urlGenerator->generate('{$lowerModel}/index') ?>\" class=\"btn btn-secondary\">Batal</a>\n";
        $code .= "                <button type=\"submit\" class=\"btn btn-primary\">\n";
        $code .= "                    Simpan Perubahan\n";
        $code .= "                </button>\n";
        $code .= "            </div>\n";
        $code .= "        </form>\n";
        $code .= "    </div>\n";
        $code .= "</div>\n";

        return $code;
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
        $inject .= "                ->action([Web\\{$modelName}\\{$modelName}Controller::class, 'index'])\n";
        $inject .= "                ->name('{$lowerModel}/index'),\n";
        $inject .= "            Route::get('/create')\n";
        $inject .= "                ->action([Web\\{$modelName}\\{$modelName}Controller::class, 'create'])\n";
        $inject .= "                ->name('{$lowerModel}/create'),\n";
        $inject .= "            Route::post('/create')\n";
        $inject .= "                ->action([Web\\{$modelName}\\{$modelName}Controller::class, 'create'])\n";
        $inject .= "                ->name('{$lowerModel}/create/post'),\n";
        $inject .= "            Route::get('/update/{id:\d+}')\n";
        $inject .= "                ->action([Web\\{$modelName}\\{$modelName}Controller::class, 'update'])\n";
        $inject .= "                ->name('{$lowerModel}/update'),\n";
        $inject .= "            Route::post('/update/{id:\d+}')\n";
        $inject .= "                ->action([Web\\{$modelName}\\{$modelName}Controller::class, 'update'])\n";
        $inject .= "                ->name('{$lowerModel}/update/post'),\n";
        $inject .= "            Route::post('/delete/{id:\d+}')\n";
        $inject .= "                ->action([Web\\{$modelName}\\{$modelName}Controller::class, 'delete'])\n";
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

        $inject = "\n            <" . "?php if (\$userSession->isLoggedIn() && \$userSession->hasPermission('view_{$lowerModel}')): " . "?" . ">\n";
        $inject .= "                <a href=\"<" . "?= \$urlGenerator->generate('{$lowerModel}/index') ?" . ">\" class=\"<" . "?= str_starts_with((string)(\$currentRoute->getName() ?? ''), '{$lowerModel}') ? 'active' : '' ?" . ">\">Data {$modelName}</a>\n";
        $inject .= "            <" . "?php endif; " . "?" . ">";

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
