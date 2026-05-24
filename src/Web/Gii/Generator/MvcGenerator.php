<?php

declare(strict_types=1);

namespace App\Web\Gii\Generator;

final class MvcGenerator
{
    /**
     * Generates MVC component files.
     */
    public function generate(
        string $table,
        string $modelName,
        string $lowerModel,
        array $columns,
        string $primaryKey,
        bool $isAutoIncrement,
        string $primaryKeyType,
        string $dir,
        array &$logs
    ): bool {
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

        return true;
    }

    private function buildModelCode(
        string $table,
        string $modelName,
        array $columns,
        string $primaryKey,
        bool $isAutoIncrement,
        string $primaryKeyType
    ): string {
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

    private function buildControllerCode(
        string $modelName,
        string $lowerModel,
        string $primaryKey,
        string $primaryKeyType
    ): string {
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

    private function buildIndexView(
        string $modelName,
        string $lowerModel,
        array $columns,
        string $primaryKey
    ): string {
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

    private function buildCreateView(
        string $modelName,
        string $lowerModel,
        array $columns,
        string $primaryKey,
        bool $isAutoIncrement
    ): string {
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

    private function buildUpdateView(
        string $modelName,
        string $lowerModel,
        array $columns,
        string $primaryKey,
        bool $isAutoIncrement
    ): string {
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
}
