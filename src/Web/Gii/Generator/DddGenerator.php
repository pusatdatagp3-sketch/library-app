<?php

declare(strict_types=1);

namespace App\Web\Gii\Generator;

final class DddGenerator
{
    /**
     * Generates DDD component files.
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
        // 1. Entity
        $entityCode = $this->buildDddEntityCode($modelName, $columns, $primaryKey, $primaryKeyType);
        file_put_contents("{$dir}/{$modelName}.php", $entityCode);
        chmod("{$dir}/{$modelName}.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/{$modelName}.php";

        // 2. DTO
        $dtoCode = $this->buildDddDtoCode($modelName, $columns, $primaryKey, $isAutoIncrement);
        file_put_contents("{$dir}/{$modelName}Dto.php", $dtoCode);
        chmod("{$dir}/{$modelName}Dto.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/{$modelName}Dto.php";

        // 3. Factory
        $factoryCode = $this->buildDddFactoryCode($modelName, $columns, $primaryKey, $primaryKeyType);
        file_put_contents("{$dir}/{$modelName}Factory.php", $factoryCode);
        chmod("{$dir}/{$modelName}Factory.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/{$modelName}Factory.php";

        // 4. Repository
        $repositoryCode = $this->buildDddRepositoryCode($table, $modelName, $columns, $primaryKey, $isAutoIncrement, $primaryKeyType);
        file_put_contents("{$dir}/{$modelName}Repository.php", $repositoryCode);
        chmod("{$dir}/{$modelName}Repository.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/{$modelName}Repository.php";

        // 5. Service
        $serviceCode = $this->buildDddServiceCode($modelName, $lowerModel, $columns, $primaryKey, $isAutoIncrement, $primaryKeyType);
        file_put_contents("{$dir}/{$modelName}Service.php", $serviceCode);
        chmod("{$dir}/{$modelName}Service.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/{$modelName}Service.php";

        // 6. Controller
        $controllerCode = $this->buildDddControllerCode($modelName, $lowerModel, $primaryKey, $primaryKeyType, $columns, $isAutoIncrement);
        file_put_contents("{$dir}/{$modelName}Controller.php", $controllerCode);
        chmod("{$dir}/{$modelName}Controller.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/{$modelName}Controller.php";

        // 7. Views
        $indexCode = $this->buildDddIndexView($modelName, $lowerModel, $columns, $primaryKey);
        file_put_contents("{$dir}/views/index.php", $indexCode);
        chmod("{$dir}/views/index.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/views/index.php";

        $createCode = $this->buildDddCreateView($modelName, $lowerModel, $columns, $primaryKey, $isAutoIncrement);
        file_put_contents("{$dir}/views/create.php", $createCode);
        chmod("{$dir}/views/create.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/views/create.php";

        $updateCode = $this->buildDddUpdateView($modelName, $lowerModel, $columns, $primaryKey, $isAutoIncrement);
        file_put_contents("{$dir}/views/update.php", $updateCode);
        chmod("{$dir}/views/update.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/views/update.php";

        return true;
    }

    private function toCamelCase(string $string): string
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        if ($str === '') return '';
        return lcfirst($str);
    }

    private function buildDddEntityCode(
        string $modelName,
        array $columns,
        string $primaryKey,
        string $primaryKeyType
    ): string {
        $props = [];
        foreach ($columns as $col) {
            $field = $col['Field'];
            $fieldCamel = $this->toCamelCase($field);
            $type = $col['Type'];
            $isNull = $col['Null'] === 'YES';

            if (str_contains($type, 'int')) {
                $phpType = 'int';
            } elseif (str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
                $phpType = 'float';
            } else {
                $phpType = 'string';
            }

            if ($field === $primaryKey) {
                $props[] = "        public ?{$phpType} \${$fieldCamel}";
            } else {
                $prefix = $isNull ? '?' : '';
                $suffix = $isNull ? ' = null' : '';
                $props[] = "        public {$prefix}{$phpType} \${$fieldCamel}{$suffix}";
            }
        }

        $propsStr = implode(",\n", $props);

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "namespace App\Web\\{$modelName};\n\n";
        $code .= "final class {$modelName}\n";
        $code .= "{\n";
        $code .= "    public function __construct(\n";
        $code .= $propsStr . "\n";
        $code .= "    ) {\n";
        $code .= "    }\n";
        $code .= "}\n";

        return $code;
    }

    private function buildDddDtoCode(
        string $modelName,
        array $columns,
        string $primaryKey,
        bool $isAutoIncrement
    ): string {
        $props = [];
        foreach ($columns as $col) {
            $field = $col['Field'];
            if ($field === $primaryKey && $isAutoIncrement) {
                continue;
            }
            $fieldCamel = $this->toCamelCase($field);
            $type = $col['Type'];
            $isNull = $col['Null'] === 'YES';

            if (str_contains($type, 'int')) {
                $phpType = 'int';
            } elseif (str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
                $phpType = 'float';
            } else {
                $phpType = 'string';
            }

            $prefix = $isNull ? '?' : '';
            $suffix = $isNull ? ' = null' : '';
            $props[] = "        public {$prefix}{$phpType} \${$fieldCamel}{$suffix}";
        }

        $propsStr = implode(",\n", $props);

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "namespace App\Web\\{$modelName};\n\n";
        $code .= "final class {$modelName}Dto\n";
        $code .= "{\n";
        $code .= "    public function __construct(\n";
        $code .= $propsStr . "\n";
        $code .= "    ) {\n";
        $code .= "    }\n";
        $code .= "}\n";

        return $code;
    }

    private function buildDddFactoryCode(
        string $modelName,
        array $columns,
        string $primaryKey,
        string $primaryKeyType
    ): string {
        $mappings = [];
        foreach ($columns as $col) {
            $field = $col['Field'];
            $fieldCamel = $this->toCamelCase($field);
            $type = $col['Type'];

            if (str_contains($type, 'int')) {
                $cast = '(int)';
            } elseif (str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
                $cast = '(float)';
            } else {
                $cast = '(string)';
            }

            $mappings[] = "            {$fieldCamel}: \$row['{$field}'] !== null ? {$cast} \$row['{$field}'] : null";
        }

        $mappingsStr = implode(",\n", $mappings);

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "namespace App\Web\\{$modelName};\n\n";
        $code .= "final class {$modelName}Factory\n";
        $code .= "{\n";
        $code .= "    public static function createFromRow(array \$row): {$modelName}\n";
        $code .= "    {\n";
        $code .= "        return new {$modelName}(\n";
        $code .= $mappingsStr . "\n";
        $code .= "        );\n";
        $code .= "    }\n";
        $code .= "}\n";

        return $code;
    }

    private function buildDddRepositoryCode(
        string $table,
        string $modelName,
        array $columns,
        string $primaryKey,
        bool $isAutoIncrement,
        string $primaryKeyType
    ): string {
        $sqlColumns = [];
        foreach ($columns as $col) {
            $field = $col['Field'];
            $type = $col['Type'];
            $null = $col['Null'] === 'NO' ? 'NOT NULL' : 'DEFAULT NULL';
            $extra = $col['Extra'] !== '' ? ' ' . $col['Extra'] : '';
            
            $sqlColumns[] = "                `{$field}` {$type} {$null}{$extra}";
        }
        $sqlColumns[] = "                PRIMARY KEY (`{$primaryKey}`)";
        $sqlColumnsStr = implode(",\n", $sqlColumns);

        $insertCols = [];
        $insertParams = [];
        $insertParamsMap = [];
        $updateSets = [];
        $updateParamsMap = [];

        foreach ($columns as $col) {
            $field = $col['Field'];
            $fieldCamel = $this->toCamelCase($field);
            if ($field === $primaryKey && $isAutoIncrement) {
                continue;
            }

            $insertCols[] = "`{$field}`";
            $insertParams[] = ":{$fieldCamel}";
            $insertParamsMap[] = "            '{$fieldCamel}' => \$dto->{$fieldCamel},";

            $updateSets[] = "`{$field}` = :{$fieldCamel}";
            $updateParamsMap[] = "            '{$fieldCamel}' => \$dto->{$fieldCamel},";
        }

        $insertColsStr = implode(', ', $insertCols);
        $insertParamsStr = implode(', ', $insertParams);
        $insertParamsMapStr = implode("\n", $insertParamsMap);
        $updateSetsStr = implode(', ', $updateSets);
        $updateParamsMapStr = implode("\n", $updateParamsMap);

        $factoryCall = "{$modelName}Factory::createFromRow(\$row)";

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "namespace App\Web\\{$modelName};\n\n";
        $code .= "use App\Environment;\n";
        $code .= "use PDO;\n\n";
        $code .= "final class {$modelName}Repository\n";
        $code .= "{\n";
        $code .= "    private PDO \$pdo;\n\n";
        $code .= "    public function __construct()\n";
        $code .= "    {\n";
        $code .= "        \$host = Environment::dbHost();\n";
        $code .= "        \$port = Environment::dbPort();\n";
        $code .= "        \$dbname = Environment::dbName();\n";
        $code .= "        \$user = Environment::dbUser();\n";
        $code .= "        \$password = Environment::dbPassword();\n\n";
        $code .= "        \$dsn = \"mysql:host={\$host};port={\$port};dbname={\$dbname};charset=utf8mb4\";\n";
        $code .= "        \$this->pdo = new PDO(\$dsn, \$user, \$password, [\n";
        $code .= "            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n";
        $code .= "            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n";
        $code .= "        ]);\n\n";
        $code .= "        \$this->initializeTable();\n";
        $code .= "    }\n\n";
        $code .= "    private function initializeTable(): void\n";
        $code .= "    {\n";
        $code .= "        \$this->pdo->exec(\"\n";
        $code .= "            CREATE TABLE IF NOT EXISTS `{$table}` (\n";
        $code .= $sqlColumnsStr . "\n";
        $code .= "            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\n";
        $code .= "        \");\n";
        $code .= "    }\n\n";
        $code .= "    /**\n";
        $code .= "     * @return {$modelName}[]\n";
        $code .= "     */\n";
        $code .= "    public function findAll(): array\n";
        $code .= "    {\n";
        $code .= "        \$stmt = \$this->pdo->query(\"SELECT * FROM `{$table}` ORDER BY `{$primaryKey}` DESC\");\n";
        $code .= "        \$rows = \$stmt->fetchAll();\n\n";
        $code .= "        \$list = [];\n";
        $code .= "        foreach (\$rows as \$row) {\n";
        $code .= "            \$list[] = {$factoryCall};\n";
        $code .= "        }\n";
        $code .= "        return \$list;\n";
        $code .= "    }\n\n";
        $code .= "    public function findById({$primaryKeyType} \$id): ?{$modelName}\n";
        $code .= "    {\n";
        $code .= "        \$stmt = \$this->pdo->prepare(\"SELECT * FROM `{$table}` WHERE `{$primaryKey}` = :id\");\n";
        $code .= "        \$stmt->execute(['id' => \$id]);\n";
        $code .= "        \$row = \$stmt->fetch();\n\n";
        $code .= "        if (!\$row) {\n";
        $code .= "            return null;\n";
        $code .= "        }\n\n";
        $code .= "        return {$factoryCall};\n";
        $code .= "    }\n\n";
        $code .= "    public function save({$modelName}Dto \$dto, ?{$primaryKeyType} \$id = null): bool\n";
        $code .= "    {\n";
        $code .= "        if (\$id === null) {\n";
        $code .= "            \$stmt = \$this->pdo->prepare(\"INSERT INTO `{$table}` ({$insertColsStr}) VALUES ({$insertParamsStr})\");\n";
        $code .= "            return \$stmt->execute([\n";
        $code .= $insertParamsMapStr . "\n";
        $code .= "            ]);\n";
        $code .= "        }\n\n";
        $code .= "        \$stmt = \$this->pdo->prepare(\"UPDATE `{$table}` SET {$updateSetsStr} WHERE `{$primaryKey}` = :id\");\n";
        $code .= "        return \$stmt->execute([\n";
        $code .= "            'id' => \$id,\n";
        $code .= $updateParamsMapStr . "\n";
        $code .= "        ]);\n";
        $code .= "    }\n\n";
        $code .= "    public function delete({$primaryKeyType} \$id): bool\n";
        $code .= "    {\n";
        $code .= "        \$stmt = \$this->pdo->prepare(\"DELETE FROM `{$table}` WHERE `{$primaryKey}` = :id\");\n";
        $code .= "        return \$stmt->execute(['id' => \$id]);\n";
        $code .= "    }\n";
        $code .= "}\n";

        return $code;
    }

    private function buildDddServiceCode(
        string $modelName,
        string $lowerModel,
        array $columns,
        string $primaryKey,
        bool $isAutoIncrement,
        string $primaryKeyType
    ): string {
        $dtoMappings = [];
        $validationRules = [];

        foreach ($columns as $col) {
            $field = $col['Field'];
            if ($field === $primaryKey && $isAutoIncrement) {
                continue;
            }
            $fieldCamel = $this->toCamelCase($field);
            $type = $col['Type'];

            if (str_contains($type, 'int')) {
                $dtoMappings[] = "        \${$fieldCamel} = (int) (\$rawData['{$field}'] ?? 0);";
            } elseif (str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
                $dtoMappings[] = "        \${$fieldCamel} = (float) (\$rawData['{$field}'] ?? 0.0);";
            } else {
                $dtoMappings[] = "        \${$fieldCamel} = trim((string) (\$rawData['{$field}'] ?? ''));";
            }

            if ($col['Null'] === 'NO') {
                if (str_contains($type, 'int') || str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
                    $validationRules[] = "        if (\$dto->{$fieldCamel} <= 0) {\n            \$errors['{$field}'] = '" . ucfirst(str_replace('_', ' ', $field)) . " harus lebih besar dari 0.';\n        }";
                } else {
                    $validationRules[] = "        if (\$dto->{$fieldCamel} === '') {\n            \$errors['{$field}'] = '" . ucfirst(str_replace('_', ' ', $field)) . " tidak boleh kosong.';\n        }";
                }
            }
        }

        $dtoMappingsStr = implode("\n", $dtoMappings);
        $dtoFieldsList = [];
        foreach ($columns as $col) {
            $field = $col['Field'];
            if ($field === $primaryKey && $isAutoIncrement) {
                continue;
            }
            $dtoFieldsList[] = "\${$this->toCamelCase($field)}";
        }
        $dtoConstructorArgs = implode(", ", $dtoFieldsList);
        $validationRulesStr = implode("\n", $validationRules);

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "namespace App\Web\\{$modelName};\n\n";
        $code .= "final class {$modelName}Service\n";
        $code .= "{\n";
        $code .= "    public function __construct(\n";
        $code .= "        private {$modelName}Repository \${$lowerModel}Repository\n";
        $code .= "    ) {\n";
        $code .= "    }\n\n";
        $code .= "    /**\n";
        $code .= "     * @return {$modelName}[]\n";
        $code .= "     */\n";
        $code .= "    public function getAll{$modelName}(): array\n";
        $code .= "    {\n";
        $code .= "        return \$this->{$lowerModel}Repository->findAll();\n";
        $code .= "    }\n\n";
        $code .= "    public function get{$modelName}ById({$primaryKeyType} \$id): ?{$modelName}\n";
        $code .= "    {\n";
        $code .= "        return \$this->{$lowerModel}Repository->findById(\$id);\n";
        $code .= "    }\n\n";
        $code .= "    public function create{$modelName}(array \$rawData, array &\$errors): bool\n";
        $code .= "    {\n";
        $code .= "        \$dto = \$this->createDtoFromRaw(\$rawData);\n";
        $code .= "        \$errors = \$this->validateDto(\$dto);\n\n";
        $code .= "        if (!empty(\$errors)) {\n";
        $code .= "            return false;\n";
        $code .= "        }\n\n";
        $code .= "        try {\n";
        $code .= "            return \$this->{$lowerModel}Repository->save(\$dto);\n";
        $code .= "        } catch (\\PDOException \$e) {\n";
        $code .= "            \$errors['general'] = 'Gagal menyimpan data: ' . \$e->getMessage();\n";
        $code .= "            return false;\n";
        $code .= "        }\n";
        $code .= "    }\n\n";
        $code .= "    public function update{$modelName}({$primaryKeyType} \$id, array \$rawData, array &\$errors): bool\n";
        $code .= "    {\n";
        $code .= "        \$dto = \$this->createDtoFromRaw(\$rawData);\n";
        $code .= "        \$errors = \$this->validateDto(\$dto);\n\n";
        $code .= "        if (!empty(\$errors)) {\n";
        $code .= "            return false;\n";
        $code .= "        }\n\n";
        $code .= "        try {\n";
        $code .= "            return \$this->{$lowerModel}Repository->save(\$dto, \$id);\n";
        $code .= "        } catch (\\PDOException \$e) {\n";
        $code .= "            \$errors['general'] = 'Gagal memperbarui data: ' . \$e->getMessage();\n";
        $code .= "            return false;\n";
        $code .= "        }\n";
        $code .= "    }\n\n";
        $code .= "    public function delete{$modelName}({$primaryKeyType} \$id): bool\n";
        $code .= "    {\n";
        $code .= "        return \$this->{$lowerModel}Repository->delete(\$id);\n";
        $code .= "    }\n\n";
        $code .= "    private function createDtoFromRaw(array \$rawData): {$modelName}Dto\n";
        $code .= "    {\n";
        $code .= $dtoMappingsStr . "\n\n";
        $code .= "        return new {$modelName}Dto({$dtoConstructorArgs});\n";
        $code .= "    }\n\n";
        $code .= "    private function validateDto({$modelName}Dto \$dto): array\n";
        $code .= "    {\n";
        $code .= "        \$errors = [];\n";
        $code .= $validationRulesStr . "\n\n";
        $code .= "        return \$errors;\n";
        $code .= "    }\n";
        $code .= "}\n";

        return $code;
    }

    private function buildDddControllerCode(
        string $modelName,
        string $lowerModel,
        string $primaryKey,
        string $primaryKeyType,
        array $columns,
        bool $isAutoIncrement
    ): string {
        $primaryKeyCamel = $this->toCamelCase($primaryKey);
        $pkCast = $primaryKeyType === 'int' ? '(int)' : '(string)';

        $emptyData = [];
        $entityData = [];

        foreach ($columns as $col) {
            $field = $col['Field'];
            $fieldCamel = $this->toCamelCase($field);
            if ($field === $primaryKey && $isAutoIncrement) continue;

            $emptyData[] = "            '{$field}' => '',";
            $entityData[] = "            '{$field}' => (string) \${$lowerModel}->{$fieldCamel},";
        }

        $emptyDataStr = implode("\n", $emptyData);
        $entityDataStr = implode("\n", $entityData);

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
        $code .= "        private {$modelName}Service \${$lowerModel}Service,\n";
        $code .= "        private UrlGeneratorInterface \$urlGenerator,\n";
        $code .= "        private ResponseFactoryInterface \$responseFactory,\n";
        $code .= "        private CurrentRoute \$currentRoute,\n";
        $code .= "        private FlashInterface \$flash\n";
        $code .= "    ) {\n";
        $code .= "    }\n\n";
        $code .= "    public function index(): ResponseInterface\n";
        $code .= "    {\n";
        $code .= "        \${$lowerModel}List = \$this->{$lowerModel}Service->getAll{$modelName}();\n";
        $code .= "        return \$this->viewRenderer->render(__DIR__ . '/views/index', [\n";
        $code .= "            '{$lowerModel}List' => \${$lowerModel}List,\n";
        $code .= "            'successMsg' => \$this->flash->get('success'),\n";
        $code .= "            'errorMsgs' => \$this->flash->get('errors') ?? [],\n";
        $code .= "        ]);\n";
        $code .= "    }\n\n";
        $code .= "    public function create(ServerRequestInterface \$request): ResponseInterface\n";
        $code .= "    {\n";
        $code .= "        \$errors = [];\n";
        $code .= "        \$data = [\n";
        $code .= $emptyDataStr . "\n";
        $code .= "        ];\n\n";
        $code .= "        if (\$request->getMethod() === 'POST') {\n";
        $code .= "            \$data = (array) \$request->getParsedBody();\n";
        $code .= "            if (\$this->{$lowerModel}Service->create{$modelName}(\$data, \$errors)) {\n";
        $code .= "                \$this->flash->set('success', 'Data {$modelName} berhasil ditambahkan.');\n";
        $code .= "                return \$this->responseFactory->createResponse(302)\n";
        $code .= "                    ->withHeader('Location', \$this->urlGenerator->generate('{$lowerModel}/index'));\n";
        $code .= "            }\n";
        $code .= "        }\n\n";
        $code .= "        return \$this->viewRenderer->render(__DIR__ . '/views/create', [\n";
        $code .= "            'errors' => \$errors,\n";
        $code .= "            'data' => \$data,\n";
        $code .= "        ]);\n";
        $code .= "    }\n\n";
        $code .= "    public function update(ServerRequestInterface \$request): ResponseInterface\n";
        $code .= "    {\n";
        $code .= "        \${$primaryKeyCamel} = {$pkCast} \$this->currentRoute->getArgument('id');\n";
        $code .= "        \${$lowerModel} = \$this->{$lowerModel}Service->get{$modelName}ById(\${$primaryKeyCamel});\n\n";
        $code .= "        if (\${$lowerModel} === null) {\n";
        $code .= "            return \$this->responseFactory->createResponse(404);\n";
        $code .= "        }\n\n";
        $code .= "        \$errors = [];\n";
        $code .= "        \$data = [\n";
        $code .= $entityDataStr . "\n";
        $code .= "        ];\n\n";
        $code .= "        if (\$request->getMethod() === 'POST') {\n";
        $code .= "            \$data = (array) \$request->getParsedBody();\n";
        $code .= "            if (\$this->{$lowerModel}Service->update{$modelName}(\${$primaryKeyCamel}, \$data, \$errors)) {\n";
        $code .= "                \$this->flash->set('success', 'Data {$modelName} berhasil diperbarui.');\n";
        $code .= "                return \$this->responseFactory->createResponse(302)\n";
        $code .= "                    ->withHeader('Location', \$this->urlGenerator->generate('{$lowerModel}/index'));\n";
        $code .= "            }\n";
        $code .= "        }\n\n";
        $code .= "        return \$this->viewRenderer->render(__DIR__ . '/views/update', [\n";
        $code .= "            '{$lowerModel}' => \${$lowerModel},\n";
        $code .= "            'errors' => \$errors,\n";
        $code .= "            'data' => \$data,\n";
        $code .= "        ]);\n";
        $code .= "    }\n\n";
        $code .= "    public function delete(): ResponseInterface\n";
        $code .= "    {\n";
        $code .= "        \${$primaryKeyCamel} = {$pkCast} \$this->currentRoute->getArgument('id');\n";
        $code .= "        try {\n";
        $code .= "            \$this->{$lowerModel}Service->delete{$modelName}(\${$primaryKeyCamel});\n";
        $code .= "            \$this->flash->set('success', 'Data {$modelName} berhasil dihapus.');\n";
        $code .= "        } catch (\\Throwable \$e) {\n";
        $code .= "            \$this->flash->set('errors', ['Gagal menghapus data: ' . \$e->getMessage()]);\n";
        $code .= "        }\n\n";
        $code .= "        return \$this->responseFactory->createResponse(302)\n";
        $code .= "            ->withHeader('Location', \$this->urlGenerator->generate('{$lowerModel}/index'));\n";
        $code .= "    }\n";
        $code .= "}\n";

        return $code;
    }

    private function buildDddIndexView(
        string $modelName,
        string $lowerModel,
        array $columns,
        string $primaryKey
    ): string {
        $primaryKeyCamel = $this->toCamelCase($primaryKey);
        
        $headers = [];
        $rowData = [];
        foreach ($columns as $col) {
            $field = $col['Field'];
            $fieldCamel = $this->toCamelCase($field);
            $label = ucfirst(str_replace('_', ' ', $field));

            if ($field === $primaryKey) {
                $headers[] = "                        <th>{$label}</th>";
                $rowData[] = "                            <td><span class=\"badge badge-id\"><?= Html::encode((string)\${$lowerModel}->{$fieldCamel}) ?></span></td>";
            } else {
                $headers[] = "                        <th>{$label}</th>";
                $rowData[] = "                            <td><?= Html::encode((string)\${$lowerModel}->{$fieldCamel}) ?></td>";
            }
        }

        $headersStr = implode("\n", $headers);
        $rowDataStr = implode("\n", $rowData);

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "use Yiisoft\Html\Html;\n";
        $code .= "use Yiisoft\View\WebView;\n";
        $code .= "use Yiisoft\Router\UrlGeneratorInterface;\n\n";
        $code .= "/**\n";
        $code .= " * @var WebView \$this\n";
        $code .= " * @var App\Web\\{$modelName}\\{$modelName}[] \${$lowerModel}List\n";
        $code .= " * @var UrlGeneratorInterface \$urlGenerator\n";
        $code .= " * @var string|null \$csrf\n";
        $code .= " * @var string|null \$successMsg\n";
        $code .= " * @var array \$errorMsgs\n";
        $code .= " * @var \\App\Web\\Auth\\UserSession \$userSession\n";
        $code .= " */\n\n";
        $code .= "\$this->setTitle('Data {$modelName} - List');\n";
        $code .= "?>\n\n";
        $code .= "<div class=\"crud-container\">\n";
        
        $code .= "    <?php if (!empty(\$successMsg)): ?>\n";
        $code .= "        <div class=\"alert alert-success\">\n";
        $code .= "            <svg class=\"alert-icon\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\">\n";
        $code .= "                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\" />\n";
        $code .= "            </svg>\n";
        $code .= "            <div class=\"alert-content\"><?= Html::encode(\$successMsg) ?></div>\n";
        $code .= "        </div>\n";
        $code .= "    <?php endif; ?>\n\n";

        $code .= "    <?php if (!empty(\$errorMsgs)): ?>\n";
        $code .= "        <div class=\"alert alert-danger\">\n";
        $code .= "            <svg class=\"alert-icon\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\">\n";
        $code .= "                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z\" />\n";
        $code .= "            </svg>\n";
        $code .= "            <div class=\"alert-content\">\n";
        $code .= "                <ul style=\"margin:0;padding-left:16px;font-size:0.875rem;line-height:1.5;\">\n";
        $code .= "                    <?php foreach (\$errorMsgs as \$err): ?>\n";
        $code .= "                        <li><?= Html::encode(\$err) ?></li>\n";
        $code .= "                    <?php endforeach; ?>\n";
        $code .= "                </ul>\n";
        $code .= "            </div>\n";
        $code .= "        </div>\n";
        $code .= "    <?php endif; ?>\n\n";

        $code .= "    <div class=\"crud-header\">\n";
        $code .= "        <div>\n";
        $code .= "            <h1 class=\"crud-title\">Data {$modelName}</h1>\n";
        $code .= "            <p class=\"crud-subtitle\">Kelola informasi {$lowerModel} pondok pesantren dengan mudah.</p>\n";
        $code .= "        </div>\n";
        $code .= "        <?php if (\$userSession->hasPermission('create_{$lowerModel}')): ?>\n";
        $code .= "            <a href=\"<?= \$urlGenerator->generate('{$lowerModel}/create') ?>\" class=\"btn btn-primary\">\n";
        $code .= "                <svg class=\"btn-icon\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\">\n";
        $code .= "                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M12 4.5v15m7.5-7.5h-15\" />\n";
        $code .= "                </svg>\n";
        $code .= "                Tambah {$modelName}\n";
        $code .= "            </a>\n";
        $code .= "        <?php endif; ?>\n";
        $code .= "    </div>\n\n";

        $code .= "    <?php if (empty(\${$lowerModel}List)): ?>\n";
        $code .= "        <div class=\"empty-state\">\n";
        $code .= "            <svg class=\"empty-icon\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\">\n";
        $code .= "                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M2.25 21h19.5M3 11.25a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 11.25V3.545M3 11.25V3.545A2.25 2.25 0 015.25 1.5h13.5A2.25 2.25 0 0121 3.545\" />\n";
        $code .= "            </svg>\n";
        $code .= "            <h3>Belum ada data {$lowerModel}</h3>\n";
        $code .= "            <p>Klik tombol di atas untuk menambahkan data {$lowerModel} pertama Anda.</p>\n";
        $code .= "        </div>\n";
        $code .= "    <?php else: ?>\n";
        $code .= "        <div class=\"table-responsive card\">\n";
        $code .= "            <table class=\"table\">\n";
        $code .= "                <thead>\n";
        $code .= "                    <tr>\n";
        $code .= $headersStr . "\n";
        $code .= "                        <?php if (\$userSession->hasPermission('update_{$lowerModel}') || \$userSession->hasPermission('delete_{$lowerModel}')): ?>\n";
        $code .= "                            <th class=\"text-center\">Aksi</th>\n";
        $code .= "                        <?php endif; ?>\n";
        $code .= "                    </tr>\n";
        $code .= "                </thead>\n";
        $code .= "                <tbody>\n";
        $code .= "                    <?php foreach (\${$lowerModel}List as \${$lowerModel}): ?>\n";
        $code .= "                        <tr>\n";
        $code .= $rowDataStr . "\n";
        $code .= "                            <?php if (\$userSession->hasPermission('update_{$lowerModel}') || \$userSession->hasPermission('delete_{$lowerModel}')): ?>\n";
        $code .= "                                <td>\n";
        $code .= "                                    <div class=\"action-buttons\">\n";
        $code .= "                                        <?php if (\$userSession->hasPermission('update_{$lowerModel}')): ?>\n";
        $code .= "                                            <a href=\"<?= \$urlGenerator->generate('{$lowerModel}/update', ['id' => \${$lowerModel}->{$primaryKeyCamel}]) ?>\" class=\"btn-action btn-edit\" title=\"Edit\">\n";
        $code .= "                                                <svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\">\n";
        $code .= "                                                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10\" />\n";
        $code .= "                                                </svg>\n";
        $code .= "                                                Edit\n";
        $code .= "                                            </a>\n";
        $code .= "                                        <?php endif; ?>\n";
        $code .= "                                        <?php if (\$userSession->hasPermission('delete_{$lowerModel}')): ?>\n";
        $code .= "                                            <form action=\"<?= \$urlGenerator->generate('{$lowerModel}/delete', ['id' => \${$lowerModel}->{$primaryKeyCamel}]) ?>\" method=\"POST\" onsubmit=\"return confirm('Apakah Anda yakin ingin menghapus data ini?');\" style=\"display:inline;\">\n";
        $code .= "                                                <input type=\"hidden\" name=\"_csrf\" value=\"<?= Html::encode(\$csrf) ?>\">\n";
        $code .= "                                                <button type=\"submit\" class=\"btn-action btn-delete\" title=\"Hapus\">\n";
        $code .= "                                                    <svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\">\n";
        $code .= "                                                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0\" />\n";
        $code .= "                                                    </svg>\n";
        $code .= "                                                    Hapus\n";
        $code .= "                                                </button>\n";
        $code .= "                                            </form>\n";
        $code .= "                                        <?php endif; ?>\n";
        $code .= "                                    </div>\n";
        $code .= "                                </td>\n";
        $code .= "                            <?php endif; ?>\n";
        $code .= "                        </tr>\n";
        $code .= "                    <?php endforeach; ?>\n";
        $code .= "                </tbody>\n";
        $code .= "            </table>\n";
        $code .= "        </div>\n";
        $code .= "    <?php endif; ?>\n";
        $code .= "</div>\n";

        return $code;
    }

    private function buildDddCreateView(
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

            $label = ucfirst(str_replace('_', ' ', $field));
            $fieldsHtml .= "            <div class=\"form-group\">\n";
            $fieldsHtml .= "                <label for=\"{$field}\" class=\"form-label\">{$label}</label>\n";
            $fieldsHtml .= "                <input type=\"text\" id=\"{$field}\" name=\"{$field}\" class=\"form-control <?= isset(\$errors['{$field}']) ? 'is-invalid' : '' ?>\" value=\"<?= Html::encode(\$data['{$field}'] ?? '') ?>\">\n";
            $fieldsHtml .= "                <?php if (isset(\$errors['{$field}'])): ?>\n";
            $fieldsHtml .= "                    <div class=\"invalid-feedback\"><?= Html::encode(\$errors['{$field}']) ?></div>\n";
            $fieldsHtml .= "                <?php endif; ?>\n";
            $fieldsHtml .= "            </div>\n\n";
        }

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "use Yiisoft\Html\Html;\n";
        $code .= "use Yiisoft\View\WebView;\n";
        $code .= "use Yiisoft\Router\UrlGeneratorInterface;\n\n";
        $code .= "/**\n";
        $code .= " * @var WebView \$this\n";
        $code .= " * @var array \$errors\n";
        $code .= " * @var array \$data\n";
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
        $code .= "            <?php if (isset(\$errors['general'])): ?>\n";
        $code .= "                <div class=\"alert alert-danger\" style=\"grid-column: span 2;\">\n";
        $code .= "                    <div class=\"alert-content\"><?= Html::encode(\$errors['general']) ?></div>\n";
        $code .= "                </div>\n";
        $code .= "            <?php endif; ?>\n\n";
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

    private function buildDddUpdateView(
        string $modelName,
        string $lowerModel,
        array $columns,
        string $primaryKey,
        bool $isAutoIncrement
    ): string {
        $primaryKeyCamel = $this->toCamelCase($primaryKey);
        $fieldsHtml = '';
        foreach ($columns as $col) {
            $field = $col['Field'];
            if ($field === $primaryKey && $isAutoIncrement) continue;

            $label = ucfirst(str_replace('_', ' ', $field));
            $fieldsHtml .= "            <div class=\"form-group\">\n";
            $fieldsHtml .= "                <label for=\"{$field}\" class=\"form-label\">{$label}</label>\n";
            $fieldsHtml .= "                <input type=\"text\" id=\"{$field}\" name=\"{$field}\" class=\"form-control <?= isset(\$errors['{$field}']) ? 'is-invalid' : '' ?>\" value=\"<?= Html::encode(\$data['{$field}'] ?? '') ?>\">\n";
            $fieldsHtml .= "                <?php if (isset(\$errors['{$field}'])): ?>\n";
            $fieldsHtml .= "                    <div class=\"invalid-feedback\"><?= Html::encode(\$errors['{$field}']) ?></div>\n";
            $fieldsHtml .= "                <?php endif; ?>\n";
            $fieldsHtml .= "            </div>\n\n";
        }

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "use Yiisoft\Html\Html;\n";
        $code .= "use Yiisoft\View\WebView;\n";
        $code .= "use Yiisoft\Router\UrlGeneratorInterface;\n\n";
        $code .= "/**\n";
        $code .= " * @var WebView \$this\n";
        $code .= " * @var App\Web\\{$modelName}\\{$modelName} \${$lowerModel}\n";
        $code .= " * @var array \$errors\n";
        $code .= " * @var array \$data\n";
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
        $code .= "        <form action=\"<?= \$urlGenerator->generate('{$lowerModel}/update', ['id' => \${$lowerModel}->{$primaryKeyCamel}]) ?>\" method=\"POST\" class=\"form-grid\">\n";
        $code .= "            <input type=\"hidden\" name=\"_csrf\" value=\"<?= Html::encode((string)\$this->getParameter('csrf')) ?>\">\n\n";
        $code .= "            <?php if (isset(\$errors['general'])): ?>\n";
        $code .= "                <div class=\"alert alert-danger\" style=\"grid-column: span 2;\">\n";
        $code .= "                    <div class=\"alert-content\"><?= Html::encode(\$errors['general']) ?></div>\n";
        $code .= "                </div>\n";
        $code .= "            <?php endif; ?>\n\n";
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
