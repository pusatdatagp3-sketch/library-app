<?php

declare(strict_types=1);

namespace App\Web\Gii\Model\Generator;

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
        $entityCode = $this->buildDddEntityCode($table, $modelName, $columns, $primaryKey, $primaryKeyType);
        file_put_contents("{$dir}/Model/{$modelName}.php", $entityCode);
        @chmod("{$dir}/Model/{$modelName}.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/Model/{$modelName}.php";

        // 2. DTO
        $dtoCode = $this->buildDddDtoCode($modelName, $columns, $primaryKey, $isAutoIncrement);
        file_put_contents("{$dir}/Model/{$modelName}Dto.php", $dtoCode);
        @chmod("{$dir}/Model/{$modelName}Dto.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/Model/{$modelName}Dto.php";

        // 3. Factory
        $factoryCode = $this->buildDddFactoryCode($modelName, $columns, $primaryKey, $primaryKeyType);
        file_put_contents("{$dir}/Model/{$modelName}Factory.php", $factoryCode);
        @chmod("{$dir}/Model/{$modelName}Factory.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/Model/{$modelName}Factory.php";

        // 4. Repository
        $repositoryCode = $this->buildDddRepositoryCode($table, $modelName, $columns, $primaryKey, $isAutoIncrement, $primaryKeyType);
        file_put_contents("{$dir}/Model/{$modelName}Repository.php", $repositoryCode);
        @chmod("{$dir}/Model/{$modelName}Repository.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/Model/{$modelName}Repository.php";

        // 5. Service
        $serviceCode = $this->buildDddServiceCode($modelName, $lowerModel, $columns, $primaryKey, $isAutoIncrement, $primaryKeyType);
        file_put_contents("{$dir}/Service/{$modelName}Service.php", $serviceCode);
        @chmod("{$dir}/Service/{$modelName}Service.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/Service/{$modelName}Service.php";

        // 6. Controller
        $controllerCode = $this->buildDddControllerCode($modelName, $lowerModel, $primaryKey, $primaryKeyType, $columns, $isAutoIncrement);
        file_put_contents("{$dir}/Controller/{$modelName}Controller.php", $controllerCode);
        @chmod("{$dir}/Controller/{$modelName}Controller.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/Controller/{$modelName}Controller.php";

        // 7. Views
        $indexCode = $this->buildDddIndexView($modelName, $lowerModel, $columns, $primaryKey);
        file_put_contents("{$dir}/View/index.php", $indexCode);
        @chmod("{$dir}/View/index.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/View/index.php";

        $createCode = $this->buildDddCreateView($modelName, $lowerModel, $columns, $primaryKey, $isAutoIncrement);
        file_put_contents("{$dir}/View/create.php", $createCode);
        @chmod("{$dir}/View/create.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/View/create.php";

        $formCode = $this->buildDddFormView($modelName, $lowerModel, $columns, $primaryKey, $isAutoIncrement);
        file_put_contents("{$dir}/View/_form.php", $formCode);
        @chmod("{$dir}/View/_form.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/View/_form.php";

        $updateCode = $this->buildDddUpdateView($modelName, $lowerModel, $columns, $primaryKey, $isAutoIncrement);
        file_put_contents("{$dir}/View/update.php", $updateCode);
        @chmod("{$dir}/View/update.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/View/update.php";

        return true;
    }

    private function toCamelCase(string $string): string
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        if ($str === '') return '';
        return lcfirst($str);
    }

    private function buildDddEntityCode(
        string $table,
        string $modelName,
        array $columns,
        string $primaryKey,
        string $primaryKeyType
    ): string {
        $props = [];
        $constructorArgs = [];
        $constructorAssignments = [];
        
        foreach ($columns as $col) {
            $field = $col['Field'];
            $fieldCamel = $this->toCamelCase($field);
            $type = $col['Type'];
            $isNull = $col['Null'] === 'YES';

            // Determine types
            if (str_contains($type, 'int')) {
                $phpType = 'int';
                $cycleType = 'integer';
            } elseif (str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
                $phpType = 'float';
                $cycleType = 'float';
            } else {
                $phpType = 'string';
                if (str_contains($type, 'varchar') || str_contains($type, 'char')) {
                    preg_match('/\((\d+)\)/', $type, $matches);
                    $length = isset($matches[1]) ? (int)$matches[1] : 255;
                    $cycleType = "string({$length})";
                } elseif (str_contains($type, 'text')) {
                    $cycleType = 'text';
                } else {
                    $cycleType = 'string';
                }
            }

            // Generate Attribute
            if ($field === $primaryKey) {
                $columnOptions = "type: 'primary'";
            } else {
                $columnOptions = "type: '{$cycleType}'";
                if ($field !== $fieldCamel) {
                    $columnOptions .= ", name: '{$field}'";
                }
                if ($isNull) {
                    $columnOptions .= ", nullable: true";
                }
            }
            
            if ($field === $primaryKey) {
                $props[] = "    #[Column({$columnOptions})]\n    public ?{$phpType} \${$fieldCamel} = null;";
                $constructorArgs[] = "?{$phpType} \${$fieldCamel} = null";
            } else {
                $prefix = $isNull ? '?' : '';
                $suffix = $isNull ? ' = null' : ($phpType === 'int' || $phpType === 'float' ? ' = 0' : " = ''");
                $defaultArg = $isNull ? ' = null' : ($phpType === 'int' || $phpType === 'float' ? ' = 0' : " = ''");
                
                $props[] = "    #[Column({$columnOptions})]\n    public {$prefix}{$phpType} \${$fieldCamel}{$suffix};";
                $constructorArgs[] = "{$prefix}{$phpType} \${$fieldCamel}{$defaultArg}";
            }
            $constructorAssignments[] = "        \$this->{$fieldCamel} = \${$fieldCamel};";
        }

        $propsStr = implode("\n\n", $props);
        $constructorArgsStr = implode(",\n        ", $constructorArgs);
        $constructorAssignmentsStr = implode("\n", $constructorAssignments);

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "namespace App\Web\\{$modelName}\\Model;\n\n";
        $code .= "use Cycle\Annotated\Annotation\Entity;\n";
        $code .= "use Cycle\Annotated\Annotation\Column;\n\n";
        $code .= "#[Entity(role: '{$table}', table: '{$table}', repository: {$modelName}Repository::class)]\n";
        $code .= "class {$modelName}\n";
        $code .= "{\n";
        $code .= $propsStr . "\n\n";
        $code .= "    public function __construct(\n";
        $code .= "        " . $constructorArgsStr . "\n";
        $code .= "    ) {\n";
        $code .= $constructorAssignmentsStr . "\n";
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
        $code .= "namespace App\Web\\{$modelName}\\Model;\n\n";
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
        $code .= "namespace App\Web\\{$modelName}\\Model;\n\n";
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
        $primaryKeyCamel = $this->toCamelCase($primaryKey);
        
        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "namespace App\Web\\{$modelName}\\Model;\n\n";
        $code .= "use Cycle\ORM\Select\Repository;\n\n";
        $code .= "class {$modelName}Repository extends Repository\n";
        $code .= "{\n";
        $code .= "    /**\n";
        $code .= "     * @return {$modelName}[]\n";
        $code .= "     */\n";
        $code .= "    public function getAll(): array\n";
        $code .= "    {\n";
        $code .= "        return \$this->select()->orderBy('{$table}.{$primaryKey}', 'DESC')->fetchAll();\n";
        $code .= "    }\n\n";
        
        $code .= "    /**\n";
        $code .= "     * @return {$modelName}[]\n";
        $code .= "     */\n";
        $code .= "    public function getAllFiltered(array \$filters = []): array\n";
        $code .= "    {\n";
        $code .= "        \$select = \$this->select();\n\n";

        foreach ($columns as $col) {
            $field = $col['Field'];
            $type = strtolower($col['Type']);
            
            if ($field === $primaryKey) {
                $cast = $primaryKeyType === 'int' ? '(int)' : '';
                $code .= "        if (!empty(\$filters['{$field}'])) {\n";
                $code .= "            \$select = \$select->where('{$field}', '=', {$cast}\$filters['{$field}']);\n";
                $code .= "        }\n";
            } else {
                if (str_contains($type, 'int') || str_contains($type, 'bool')) {
                    $code .= "        if (isset(\$filters['{$field}']) && \$filters['{$field}'] !== '') {\n";
                    $code .= "            \$select = \$select->where('{$field}', '=', (int)\$filters['{$field}']);\n";
                    $code .= "        }\n";
                } elseif (str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
                    $code .= "        if (isset(\$filters['{$field}']) && \$filters['{$field}'] !== '') {\n";
                    $code .= "            \$select = \$select->where('{$field}', '=', (float)\$filters['{$field}']);\n";
                    $code .= "        }\n";
                } else {
                    $code .= "        if (!empty(\$filters['{$field}'])) {\n";
                    $code .= "            \$select = \$select->where('{$field}', 'like', '%' . \$filters['{$field}'] . '%');\n";
                    $code .= "        }\n";
                }
            }
        }
        
        $code .= "\n        return \$select->orderBy('{$table}.{$primaryKey}', 'DESC')->fetchAll();\n";
        $code .= "    }\n\n";

        $code .= "    public function getById({$primaryKeyType} \${$primaryKeyCamel}): ?{$modelName}\n";
        $code .= "    {\n";
        $code .= "        return \$this->findByPK(\${$primaryKeyCamel});\n";
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

        $entityArgs = [];
        foreach ($columns as $col) {
            $field = $col['Field'];
            if ($field === $primaryKey) {
                $entityArgs[] = "null";
            } else {
                $fieldCamel = $this->toCamelCase($field);
                $entityArgs[] = "\$dto->{$fieldCamel}";
            }
        }
        $entityArgsStr = implode(', ', $entityArgs);

        $updateAssignments = [];
        foreach ($columns as $col) {
            $field = $col['Field'];
            if ($field === $primaryKey) continue;
            $fieldCamel = $this->toCamelCase($field);
            $updateAssignments[] = "            \$entity->{$fieldCamel} = \$dto->{$fieldCamel};";
        }
        $updateAssignmentsStr = implode("\n", $updateAssignments);

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "namespace App\Web\\{$modelName}\\Service;\n\n";
        $code .= "use App\Web\\{$modelName}\\Model\\{$modelName};\n";
        $code .= "use App\Web\\{$modelName}\\Model\\{$modelName}Dto;\n";
        $code .= "use App\Web\\{$modelName}\\Model\\{$modelName}Repository;\n";
        $code .= "use Cycle\ORM\EntityManagerInterface;\n\n";
        $code .= "final class {$modelName}Service\n";
        $code .= "{\n";
        $code .= "    public function __construct(\n";
        $code .= "        private {$modelName}Repository \${$lowerModel}Repository,\n";
        $code .= "        private EntityManagerInterface \$entityManager\n";
        $code .= "    ) {\n";
        $code .= "    }\n\n";
        $code .= "    /**\n";
        $code .= "     * @return {$modelName}[]\n";
        $code .= "     */\n";
        $code .= "    public function getAll{$modelName}(array \$filters = []): array\n";
        $code .= "    {\n";
        $code .= "        return \$this->{$lowerModel}Repository->getAllFiltered(\$filters);\n";
        $code .= "    }\n\n";

        $code .= "    public function get{$modelName}ById({$primaryKeyType} \$id): ?{$modelName}\n";
        $code .= "    {\n";
        $code .= "        return \$this->{$lowerModel}Repository->getById(\$id);\n";
        $code .= "    }\n\n";
        $code .= "    public function create{$modelName}(array \$rawData, array &\$errors): bool\n";
        $code .= "    {\n";
        $code .= "        \$dto = \$this->createDtoFromRaw(\$rawData);\n";
        $code .= "        \$errors = \$this->validateDto(\$dto);\n\n";
        $code .= "        if (!empty(\$errors)) {\n";
        $code .= "            return false;\n";
        $code .= "        }\n\n";
        $code .= "        try {\n";
        $code .= "            \$entity = new {$modelName}({$entityArgsStr});\n";
        $code .= "            \$this->entityManager->persist(\$entity)->run();\n";
        $code .= "            return true;\n";
        $code .= "        } catch (\\Throwable \$e) {\n";
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
        $code .= "        \$entity = \$this->{$lowerModel}Repository->getById(\$id);\n";
        $code .= "        if (\$entity === null) {\n";
        $code .= "            \$errors['general'] = 'Data tidak ditemukan.';\n";
        $code .= "            return false;\n";
        $code .= "        }\n\n";
        $code .= "        try {\n";
        $code .= "{$updateAssignmentsStr}\n";
        $code .= "            \$this->entityManager->persist(\$entity)->run();\n";
        $code .= "            return true;\n";
        $code .= "        } catch (\\Throwable \$e) {\n";
        $code .= "            \$errors['general'] = 'Gagal memperbarui data: ' . \$e->getMessage();\n";
        $code .= "            return false;\n";
        $code .= "        }\n";
        $code .= "    }\n\n";
        $code .= "    public function delete{$modelName}({$primaryKeyType} \$id): bool\n";
        $code .= "    {\n";
        $code .= "        \$entity = \$this->{$lowerModel}Repository->getById(\$id);\n";
        $code .= "        if (\$entity === null) {\n";
        $code .= "            return false;\n";
        $code .= "        }\n";
        $code .= "        try {\n";
        $code .= "            \$this->entityManager->delete(\$entity)->run();\n";
        $code .= "            return true;\n";
        $code .= "        } catch (\\Throwable \$e) {\n";
        $code .= "            return false;\n";
        $code .= "        }\n";
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
        $code .= "namespace App\Web\\{$modelName}\\Controller;\n\n";
        $code .= "use App\Web\\{$modelName}\\Model\\{$modelName};\n";
        $code .= "use App\Web\\{$modelName}\\Service\\{$modelName}Service;\n";
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
        $code .= "    public function index(ServerRequestInterface \$request): ResponseInterface\n";
        $code .= "    {\n";
        $code .= "        \$queryParams = \$request->getQueryParams();\n";
        $code .= "        \${$lowerModel}List = \$this->{$lowerModel}Service->getAll{$modelName}(\$queryParams);\n";
        $code .= "        return \$this->viewRenderer->render(__DIR__ . '/../View/index', [\n";
        $code .= "            '{$lowerModel}List' => \${$lowerModel}List,\n";
        $code .= "            'filters' => \$queryParams,\n";
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
        $code .= "        return \$this->viewRenderer->render(__DIR__ . '/../View/create', [\n";
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
        $code .= "        return \$this->viewRenderer->render(__DIR__ . '/../View/update', [\n";
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
                $headers[] = "                        <th style=\"width: 80px;\">{$label}</th>";
                $rowData[] = "                            <td><span class=\"badge badge-id\"><?= Html::encode((string)\${$lowerModel}->{$fieldCamel}) ?></span></td>";
            } else {
                $headers[] = "                        <th>{$label}</th>";
                $rowData[] = "                            <td><?= Html::encode((string)\${$lowerModel}->{$fieldCamel}) ?></td>";
            }
        }

        $headersStr = implode("\n", $headers);
        $rowDataStr = implode("\n", $rowData);

        $filterCells = '';
        foreach ($columns as $col) {
            $field = $col['Field'];
            $type = strtolower($col['Type']);
            
            $filterCells .= "                    <td>\n";
            if ($field === $primaryKey) {
                $filterCells .= "                        <input type=\"text\" name=\"{$field}\" form=\"filter-form\" value=\"<?= Html::encode(\$filters['{$field}'] ?? '') ?>\" class=\"form-control\" placeholder=\"Cari ID...\">\n";
            } else {
                if (str_contains($type, 'int') || str_contains($type, 'bool') || str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
                    $inputType = "number";
                } else {
                    $inputType = "text";
                }
                $label = ucfirst(str_replace('_', ' ', $field));
                $filterCells .= "                        <input type=\"{$inputType}\" name=\"{$field}\" form=\"filter-form\" value=\"<?= Html::encode(\$filters['{$field}'] ?? '') ?>\" class=\"form-control\" placeholder=\"Cari {$label}...\">\n";
            }
            $filterCells .= "                    </td>\n";
        }

        $actionsFilterCell = "                        <?php if (\$userSession->hasPermission('update_{$lowerModel}') || \$userSession->hasPermission('delete_{$lowerModel}')): " . '?' . ">\n";
        $actionsFilterCell .= "                            <td class=\"text-center\">\n";
        $actionsFilterCell .= "                                <div class=\"d-flex gap-1 justify-content-center\">\n";
        $actionsFilterCell .= "                                    <button type=\"submit\" form=\"filter-form\" class=\"btn btn-sm btn-primary\">Cari</button>\n";
        $actionsFilterCell .= "                                    <a href=\"<?= \$urlGenerator->generate('{$lowerModel}/index') ?>\" class=\"btn btn-sm btn-secondary\">Reset</a>\n";
        $actionsFilterCell .= "                                </div>\n";
        $actionsFilterCell .= "                            </td>\n";
        $actionsFilterCell .= "                        <?php endif; " . '?' . ">\n";

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "use Yiisoft\Html\Html;\n";
        $code .= "use Yiisoft\View\WebView;\n";
        $code .= "use Yiisoft\Router\UrlGeneratorInterface;\n\n";
        $code .= "/**\n";
        $code .= " * @var WebView \$this\n";
        $code .= " * @var App\Web\\{$modelName}\\Model\\{$modelName}[] \${$lowerModel}List\n";
        $code .= " * @var array \$filters\n";
        $code .= " * @var UrlGeneratorInterface \$urlGenerator\n";
        $code .= " * @var string|null \$csrf\n";
        $code .= " * @var string|null \$successMsg\n";
        $code .= " * @var array \$errorMsgs\n";
        $code .= " * @var \\App\Web\\Auth\\Model\\UserSession \$userSession\n";
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
        
        $code .= "    <form id=\"filter-form\" method=\"GET\" action=\"<?= \$urlGenerator->generate('{$lowerModel}/index') ?>\"></form>\n\n";

        $code .= "    <?php if (empty(\${$lowerModel}List) && empty(\$filters)): ?>\n";
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
        $code .= "                    <tr class=\"filter-row\">\n";
        $code .= $filterCells;
        $code .= $actionsFilterCell;
        $code .= "                    </tr>\n";
        $code .= "                </thead>\n";
        $code .= "                <tbody>\n";
        
        $code .= "                    <?php if (empty(\${$lowerModel}List)): ?>\n";
        $code .= "                        <?php \$colCount = " . count($columns) . " + (\$userSession->hasPermission('update_{$lowerModel}') || \$userSession->hasPermission('delete_{$lowerModel}') ? 1 : 0); ?>\n";
        $code .= "                        <tr>\n";
        $code .= "                            <td colspan=\"<?= \$colCount ?>\" class=\"text-center text-muted py-4\">\n";
        $code .= "                                Tidak ada data yang cocok dengan pencarian.\n";
        $code .= "                            </td>\n";
        $code .= "                        </tr>\n";
        $code .= "                    <?php else: ?>\n";
        
        $code .= "                        <?php foreach (\${$lowerModel}List as \${$lowerModel}): ?>\n";
        $code .= "                            <tr>\n";
        $code .= $rowDataStr . "\n";
        $code .= "                                <?php if (\$userSession->hasPermission('update_{$lowerModel}') || \$userSession->hasPermission('delete_{$lowerModel}')): ?>\n";
        $code .= "                                    <td>\n";
        $code .= "                                        <div class=\"action-buttons\">\n";
        $code .= "                                            <?php if (\$userSession->hasPermission('update_{$lowerModel}')): ?>\n";
        $code .= "                                                <a href=\"<?= \$urlGenerator->generate('{$lowerModel}/update', ['id' => \${$lowerModel}->{$primaryKeyCamel}]) ?>\" class=\"btn-action btn-edit\" title=\"Edit\">\n";
        $code .= "                                                    <svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\">\n";
        $code .= "                                                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10\" />\n";
        $code .= "                                                    </svg>\n";
        $code .= "                                                    Edit\n";
        $code .= "                                                </a>\n";
        $code .= "                                            <?php endif; ?>\n";
        $code .= "                                            <?php if (\$userSession->hasPermission('delete_{$lowerModel}')): ?>\n";
        $code .= "                                                <form action=\"<?= \$urlGenerator->generate('{$lowerModel}/delete', ['id' => \${$lowerModel}->{$primaryKeyCamel}]) ?>\" method=\"POST\" onsubmit=\"return confirm('Apakah Anda yakin ingin menghapus data ini?');\" style=\"display:inline;\">\n";
        $code .= "                                                    <input type=\"hidden\" name=\"_csrf\" value=\"<?= Html::encode(\$csrf) ?>\">\n";
        $code .= "                                                    <button type=\"submit\" class=\"btn-action btn-delete\" title=\"Hapus\">\n";
        $code .= "                                                        <svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\">\n";
        $code .= "                                                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0\" />\n";
        $code .= "                                                        </svg>\n";
        $code .= "                                                        Hapus\n";
        $code .= "                                                    </button>\n";
        $code .= "                                                </form>\n";
        $code .= "                                            <?php endif; ?>\n";
        $code .= "                                        </div>\n";
        $code .= "                                    </td>\n";
        $code .= "                                <?php endif; ?>\n";
        $code .= "                            </tr>\n";
        $code .= "                        <?php endforeach; ?>\n";
        $code .= "                    <?php endif; ?>\n";
        $code .= "                </tbody>\n";
        $code .= "            </table>\n";
        $code .= "        </div>\n";
        $code .= "    <?php endif; ?>\n";
        $code .= "</div>\n\n";

        $code .= "<script>\n";
        $code .= "document.querySelectorAll('[form=\"filter-form\"]').forEach(input => {\n";
        $code .= "    input.addEventListener('keypress', function(e) {\n";
        $code .= "        if (e.key === 'Enter') {\n";
        $code .= "            document.getElementById('filter-form').submit();\n";
        $code .= "        }\n";
        $code .= "    });\n";
        $code .= "    if (input.tagName === 'SELECT') {\n";
        $code .= "        input.addEventListener('change', function() {\n";
        $code .= "            document.getElementById('filter-form').submit();\n";
        $code .= "        });\n";
        $code .= "    }\n";
        $code .= "});\n";
        $code .= "</script>\n";

        return $code;
    }

    private function buildDddFormView(
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
        $code .= " * @var string \$formAction\n";
        $code .= " * @var string \$submitLabel\n";
        $code .= " * @var bool \$showReset\n";
        $code .= " * @var string|null \$cancelUrl\n";
        $code .= " */\n";
        $code .= "?>\n\n";
        $code .= "<div class=\"card\">\n";
        $code .= "    <form action=\"<?= \$formAction ?>\" method=\"POST\" class=\"form-grid\">\n";
        $code .= "        <input type=\"hidden\" name=\"_csrf\" value=\"<?= Html::encode((string)\$this->getParameter('csrf')) ?>\">\n\n";
        $code .= "        <?php if (isset(\$errors['general'])): ?>\n";
        $code .= "            <div class=\"alert alert-danger\" style=\"grid-column: span 2;\">\n";
        $code .= "                <div class=\"alert-content\"><?= Html::encode(\$errors['general']) ?></div>\n";
        $code .= "            </div>\n";
        $code .= "        <?php endif; ?>\n\n";
        $code .= $fieldsHtml;
        $code .= "        <div class=\"form-actions\">\n";
        $code .= "            <?php if (\$showReset): ?>\n";
        $code .= "                <button type=\"reset\" class=\"btn btn-secondary\">Reset</button>\n";
        $code .= "            <?php elseif (\$cancelUrl !== null): ?>\n";
        $code .= "                <a href=\"<?= \$cancelUrl ?>\" class=\"btn btn-secondary\">Batal</a>\n";
        $code .= "            <?php endif; ?>\n";
        $code .= "            <button type=\"submit\" class=\"btn btn-primary\">\n";
        $code .= "                <?= Html::encode(\$submitLabel) ?>\n";
        $code .= "            </button>\n";
        $code .= "        </div>\n";
        $code .= "    </form>\n";
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
        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
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
        $code .= "    <?= \$this->render('./_form', [\n";
        $code .= "        'errors' => \$errors,\n";
        $code .= "        'data' => \$data,\n";
        $code .= "        'formAction' => \$urlGenerator->generate('{$lowerModel}/create'),\n";
        $code .= "        'submitLabel' => 'Simpan Data',\n";
        $code .= "        'showReset' => true,\n";
        $code .= "        'cancelUrl' => null,\n";
        $code .= "    ]) ?>\n";
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
        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "use Yiisoft\View\WebView;\n";
        $code .= "use Yiisoft\Router\UrlGeneratorInterface;\n\n";
        $code .= "/**\n";
        $code .= " * @var WebView \$this\n";
        $code .= " * @var App\Web\\{$modelName}\\Model\\{$modelName} \${$lowerModel}\n";
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
        $code .= "    <?= \$this->render('./_form', [\n";
        $code .= "        'errors' => \$errors,\n";
        $code .= "        'data' => \$data,\n";
        $code .= "        'formAction' => \$urlGenerator->generate('{$lowerModel}/update', ['id' => \${$lowerModel}->{$primaryKeyCamel}]),\n";
        $code .= "        'submitLabel' => 'Simpan Perubahan',\n";
        $code .= "        'showReset' => false,\n";
        $code .= "        'cancelUrl' => \$urlGenerator->generate('{$lowerModel}/index'),\n";
        $code .= "    ]) ?>\n";
        $code .= "</div>\n";

        return $code;
    }
}
