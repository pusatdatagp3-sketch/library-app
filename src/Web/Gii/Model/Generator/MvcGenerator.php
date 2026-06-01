<?php

declare(strict_types=1);

namespace App\Web\Gii\Model\Generator;

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
        file_put_contents("{$dir}/Model/{$modelName}.php", $modelCode);
        @chmod("{$dir}/Model/{$modelName}.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/Model/{$modelName}.php";

        // 2. Generate Controller
        $controllerCode = $this->buildControllerCode($modelName, $lowerModel, $primaryKey, $primaryKeyType, $columns);
        file_put_contents("{$dir}/Controller/{$modelName}Controller.php", $controllerCode);
        @chmod("{$dir}/Controller/{$modelName}Controller.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/Controller/{$modelName}Controller.php";

        // 3. Generate Views
        $indexCode = $this->buildIndexView($modelName, $lowerModel, $columns, $primaryKey);
        file_put_contents("{$dir}/View/index.php", $indexCode);
        @chmod("{$dir}/View/index.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/View/index.php";

        $createCode = $this->buildCreateView($modelName, $lowerModel, $columns, $primaryKey, $isAutoIncrement);
        file_put_contents("{$dir}/View/create.php", $createCode);
        @chmod("{$dir}/View/create.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/View/create.php";

        $formCode = $this->buildFormView($modelName, $lowerModel, $columns, $primaryKey, $isAutoIncrement);
        file_put_contents("{$dir}/View/_form.php", $formCode);
        @chmod("{$dir}/View/_form.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/View/_form.php";

        $updateCode = $this->buildUpdateView($modelName, $lowerModel, $columns, $primaryKey, $isAutoIncrement);
        file_put_contents("{$dir}/View/update.php", $updateCode);
        @chmod("{$dir}/View/update.php", 0666);
        $logs[] = "File dibuat: src/Web/{$modelName}/View/update.php";

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

        foreach ($columns as $col) {
            $field = $col['Field'];
            $type = $col['Type'];
            $isNull = $col['Null'] === 'YES';
            $extra = $col['Extra'];

            // Property data type mapping
            $phpType = 'string';
            $cycleType = 'string';
            if (str_contains($type, 'int')) {
                $phpType = 'int';
                $cycleType = 'integer';
            } elseif (str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
                $phpType = 'float';
                $cycleType = 'float';
            } else {
                if (str_contains($type, 'varchar') || str_contains($type, 'char')) {
                    preg_match('/\((\d+)\)/', $type, $matches);
                    $length = isset($matches[1]) ? (int)$matches[1] : 255;
                    $cycleType = "string({$length})";
                } elseif (str_contains($type, 'text')) {
                    $cycleType = 'text';
                }
            }

            if ($field === $primaryKey) {
                $properties .= "    #[Column(type: 'primary')]\n";
                $properties .= "    public ?{$primaryKeyType} \${$primaryKey} = null;\n\n";

                // Load primary key if not auto-increment
                if (!$isAutoIncrement) {
                    if ($primaryKeyType === 'int') {
                        $loadFields .= "        \$this->{$primaryKey} = isset(\$data['{$primaryKey}']) && \$data['{$primaryKey}'] !== '' ? (int)\$data['{$primaryKey}'] : null;\n";
                    } else {
                        $loadFields .= "        \$this->{$primaryKey} = isset(\$data['{$primaryKey}']) ? trim((string)\$data['{$primaryKey}']) : null;\n";
                    }
                    $validationRules .= "        if (\$this->{$primaryKey} === '' || \$this->{$primaryKey} === null) {\n";
                    $validationRules .= "            \$this->errors['{$primaryKey}'] = 'Kolom " . ucfirst($primaryKey) . " tidak boleh kosong.';\n";
                    $validationRules .= "        }\n";
                }
            } else {
                $defaultValue = $isNull ? 'null' : ($phpType === 'int' || $phpType === 'float' ? '0' : "''");
                $nullablePrefix = $isNull ? '?' : '';
                
                $columnAttr = "type: '{$cycleType}'";
                if ($isNull) {
                    $columnAttr .= ", nullable: true";
                }
                
                $properties .= "    #[Column({$columnAttr})]\n";
                $properties .= "    public {$nullablePrefix}{$phpType} \${$field} = {$defaultValue};\n\n";

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
            }
        }

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "namespace App\Web\\{$modelName}\\Model;\n\n";
        $code .= "use Cycle\Annotated\Annotation\Entity;\n";
        $code .= "use Cycle\Annotated\Annotation\Column;\n\n";
        $code .= "#[Entity(role: '{$table}', table: '{$table}')]\n";
        $code .= "class {$modelName}\n";
        $code .= "{\n";
        $code .= $properties;
        $code .= "    public array \$errors = [];\n\n";
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
        $code .= "    }\n";
        $code .= "}\n";

        return $code;
    }

    private function buildControllerCode(
        string $modelName,
        string $lowerModel,
        string $primaryKey,
        string $primaryKeyType,
        array $columns
    ): string {
        $pkCast = $primaryKeyType === 'int' ? '(int)' : '(string)';

        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "namespace App\Web\\{$modelName}\\Controller;\n\n";
        $code .= "use App\Web\\{$modelName}\\Model\\{$modelName};\n";
        $code .= "use Cycle\ORM\ORMInterface;\n";
        $code .= "use Cycle\ORM\EntityManagerInterface;\n";
        $code .= "use Psr\Http\Message\ResponseInterface;\n";
        $code .= "use Psr\Http\Message\ServerRequestInterface;\n";
        $code .= "use Psr\Http\Message\ResponseFactoryInterface;\n";
        $code .= "use Yiisoft\Router\CurrentRoute;\n";
        $code .= "use Yiisoft\Router\UrlGeneratorInterface;\n";
        $code .= "use Yiisoft\Yii\View\Renderer\WebViewRenderer;\n";
        $code .= "use Yiisoft\Session\Flash\FlashInterface;\n\n";
        $code .= "final class {$modelName}Controller\n";
        $code .= "{\n";
        $code .= "    private \$repository;\n\n";
        $code .= "    public function __construct(\n";
        $code .= "        private WebViewRenderer \$viewRenderer,\n";
        $code .= "        private UrlGeneratorInterface \$urlGenerator,\n";
        $code .= "        private ResponseFactoryInterface \$responseFactory,\n";
        $code .= "        private CurrentRoute \$currentRoute,\n";
        $code .= "        private FlashInterface \$flash,\n";
        $code .= "        private ORMInterface \$orm,\n";
        $code .= "        private EntityManagerInterface \$entityManager\n";
        $code .= "    ) {\n";
        $code .= "        \$this->repository = \$orm->getRepository({$modelName}::class);\n";
        $code .= "    }\n\n";
        $code .= "    public function index(ServerRequestInterface \$request): ResponseInterface\n";
        $code .= "    {\n";
        $code .= "        \$queryParams = \$request->getQueryParams();\n";
        $code .= "        \$select = \$this->repository->select();\n\n";

        foreach ($columns as $col) {
            $field = $col['Field'];
            $type = strtolower($col['Type']);
            
            if ($field === $primaryKey) {
                $cast = $primaryKeyType === 'int' ? '(int)' : '';
                $code .= "        if (!empty(\$queryParams['{$field}'])) {\n";
                $code .= "            \$select = \$select->where('{$field}', '=', {$cast}\$queryParams['{$field}']);\n";
                $code .= "        }\n";
            } else {
                if (str_contains($type, 'int') || str_contains($type, 'bool')) {
                    $code .= "        if (isset(\$queryParams['{$field}']) && \$queryParams['{$field}'] !== '') {\n";
                    $code .= "            \$select = \$select->where('{$field}', '=', (int)\$queryParams['{$field}']);\n";
                    $code .= "        }\n";
                } elseif (str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
                    $code .= "        if (isset(\$queryParams['{$field}']) && \$queryParams['{$field}'] !== '') {\n";
                    $code .= "            \$select = \$select->where('{$field}', '=', (float)\$queryParams['{$field}']);\n";
                    $code .= "        }\n";
                } else {
                    $code .= "        if (!empty(\$queryParams['{$field}'])) {\n";
                    $code .= "            \$select = \$select->where('{$field}', 'like', '%' . \$queryParams['{$field}'] . '%');\n";
                    $code .= "        }\n";
                }
            }
        }

        $code .= "\n        \$models = \$select->orderBy('{$primaryKey}', 'DESC')->fetchAll();\n";
        $code .= "        return \$this->viewRenderer->render(__DIR__ . '/../View/index', [\n";
        $code .= "            'models' => \$models,\n";
        $code .= "            'filters' => \$queryParams,\n";
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
        $code .= "            if (\$model->validate()) {\n";
        $code .= "                \$successMsg = 'Data {$modelName} berhasil ditambahkan.';\n";
        $code .= "                \$this->entityManager->persist(\$model)->run();\n";
        $code .= "                \$this->flash->set('success', \$successMsg);\n";
        $code .= "                return \$this->responseFactory->createResponse(302)\n";
        $code .= "                    ->withHeader('Location', \$this->urlGenerator->generate('{$lowerModel}/index'));\n";
        $code .= "            }\n";
        $code .= "        }\n\n";
        $code .= "        return \$this->viewRenderer->render(__DIR__ . '/../View/create', [\n";
        $code .= "            'model' => \$model,\n";
        $code .= "        ]);\n";
        $code .= "    }\n\n";
        $code .= "    public function update(ServerRequestInterface \$request): ResponseInterface\n";
        $code .= "    {\n";
        $code .= "        \$id = {$pkCast} \$this->currentRoute->getArgument('id');\n";
        $code .= "        \$model = \$this->repository->findByPK(\$id);\n\n";
        $code .= "        if (\$model === null) {\n";
        $code .= "            return \$this->responseFactory->createResponse(404);\n";
        $code .= "        }\n\n";
        $code .= "        if (\$request->getMethod() === 'POST') {\n";
        $code .= "            \$data = (array) \$request->getParsedBody();\n";
        $code .= "            \$model->load(\$data);\n";
        $code .= "            if (\$model->validate()) {\n";
        $code .= "                \$successMsg = 'Data {$modelName} berhasil diperbarui.';\n";
        $code .= "                \$this->entityManager->persist(\$model)->run();\n";
        $code .= "                \$this->flash->set('success', \$successMsg);\n";
        $code .= "                return \$this->responseFactory->createResponse(302)\n";
        $code .= "                    ->withHeader('Location', \$this->urlGenerator->generate('{$lowerModel}/index'));\n";
        $code .= "            }\n";
        $code .= "        }\n\n";
        $code .= "        return \$this->viewRenderer->render(__DIR__ . '/../View/update', [\n";
        $code .= "            'model' => \$model,\n";
        $code .= "        ]);\n";
        $code .= "    }\n\n";
        $code .= "    public function delete(): ResponseInterface\n";
        $code .= "    {\n";
        $code .= "        \$id = {$pkCast} \$this->currentRoute->getArgument('id');\n";
        $code .= "        \$model = \$this->repository->findByPK(\$id);\n\n";
        $code .= "        if (\$model !== null) {\n";
        $code .= "            \$successMsg = 'Data {$modelName} berhasil dihapus.';\n";
        $code .= "            \$this->entityManager->delete(\$model)->run();\n";
        $code .= "            \$this->flash->set('success', \$successMsg);\n";
        $code .= "        }\n\n";
        $code .= "        return \$this->responseFactory->createResponse(302)\n";
        $code .= "            ->withHeader('Location', \$this->urlGenerator->generate('{$lowerModel}/index'));\n";
        $code .= "    }\n";
        $code .= "}\n";

        return $code;
    }    private function buildIndexView(
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
        $code .= " * @var App\Web\\{$modelName}\\Model\\{$modelName}[] \$models\n";
        $code .= " * @var array \$filters\n";
        $code .= " * @var UrlGeneratorInterface \$urlGenerator\n";
        $code .= " * @var \App\Web\Auth\Model\UserSession \$userSession\n";
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
        
        $code .= "    <form id=\"filter-form\" method=\"GET\" action=\"<?= \$urlGenerator->generate('{$lowerModel}/index') ?>\"></form>\n\n";

        $code .= "    <?php if (empty(\$models) && empty(\$filters)): " . '?' . ">\n";
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
        $code .= "                    <tr class=\"filter-row\">\n";
        $code .= $filterCells;
        $code .= $actionsFilterCell;
        $code .= "                    </tr>\n";
        $code .= "                </thead>\n";
        $code .= "                <tbody>\n";
        
        $code .= "                    <?php if (empty(\$models)): " . '?' . ">\n";
        $code .= "                        <?php \$colCount = " . count($columns) . " + (\$userSession->hasPermission('update_{$lowerModel}') || \$userSession->hasPermission('delete_{$lowerModel}') ? 1 : 0); " . '?' . ">\n";
        $code .= "                        <tr>\n";
        $code .= "                            <td colspan=\"<?= \$colCount ?>\" class=\"text-center text-muted py-4\">\n";
        $code .= "                                Tidak ada data yang cocok dengan pencarian.\n";
        $code .= "                            </td>\n";
        $code .= "                        </tr>\n";
        $code .= "                    <?php else: " . '?' . ">\n";
        
        $code .= "                        <?php foreach (\$models as \$model): " . '?' . ">\n";
        $code .= "                            <tr>\n";
        $code .= $values;
        $code .= "                                <?php if (\$userSession->hasPermission('update_{$lowerModel}') || \$userSession->hasPermission('delete_{$lowerModel}')): " . '?' . ">\n";
        $code .= "                                    <td>\n";
        $code .= "                                        <div class=\"action-buttons\">\n";
        $code .= "                                            <?php if (\$userSession->hasPermission('update_{$lowerModel}')): " . '?' . ">\n";
        $code .= "                                                <a href=\"<?= \$urlGenerator->generate('{$lowerModel}/update', ['id' => \$model->{$primaryKey}]) ?>\" class=\"btn-action btn-edit\">\n";
        $code .= "                                                    Edit\n";
        $code .= "                                                </a>\n";
        $code .= "                                            <?php endif; " . '?' . ">\n\n";
        $code .= "                                            <?php if (\$userSession->hasPermission('delete_{$lowerModel}')): " . '?' . ">\n";
        $code .= "                                                <form action=\"<?= \$urlGenerator->generate('{$lowerModel}/delete', ['id' => \$model->{$primaryKey}]) ?>\" method=\"POST\" onsubmit=\"return confirm('Apakah Anda yakin ingin menghapus data ini?');\" style=\"display:inline;\">\n";
        $code .= "                                                    <input type=\"hidden\" name=\"_csrf\" value=\"<?= Html::encode(\$this->getParameter('csrf')) ?>\">\n";
        $code .= "                                                    <button type=\"submit\" class=\"btn-action btn-delete\">\n";
        $code .= "                                                        Hapus\n";
        $code .= "                                                    </button>\n";
        $code .= "                                                </form>\n";
        $code .= "                                            <?php endif; " . '?' . ">\n";
        $code .= "                                        </div>\n";
        $code .= "                                    </td>\n";
        $code .= "                                <?php endif; " . '?' . ">\n";
        $code .= "                            </tr>\n";
        $code .= "                        <?php endforeach; " . '?' . ">\n";
        $code .= "                    <?php endif; " . '?' . ">\n";
        $code .= "                </tbody>\n";
        $code .= "            </table>\n";
        $code .= "        </div>\n";
        $code .= "    <?php endif; " . '?' . ">\n";
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


    private function buildFormView(
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
            $fieldsHtml .= "                <?php if (isset(\$model->errors['{$field}'])): ?>\n";
            $fieldsHtml .= "                    <div class=\"invalid-feedback\"><?= Html::encode(\$model->errors['{$field}']) ?></div>\n";
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
        $code .= " * @var App\Web\\{$modelName}\\Model\\{$modelName} \$model\n";
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

    private function buildCreateView(
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
        $code .= " * @var App\Web\\{$modelName}\\Model\\{$modelName} \$model\n";
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
        $code .= "        'model' => \$model,\n";
        $code .= "        'formAction' => \$urlGenerator->generate('{$lowerModel}/create'),\n";
        $code .= "        'submitLabel' => 'Simpan Data',\n";
        $code .= "        'showReset' => true,\n";
        $code .= "        'cancelUrl' => null,\n";
        $code .= "    ]) ?>\n";
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
        $code = "<?php\n\n";
        $code .= "declare(strict_types=1);\n\n";
        $code .= "use Yiisoft\View\WebView;\n";
        $code .= "use Yiisoft\Router\UrlGeneratorInterface;\n\n";
        $code .= "/**\n";
        $code .= " * @var WebView \$this\n";
        $code .= " * @var App\Web\\{$modelName}\\Model\\{$modelName} \$model\n";
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
        $code .= "        'model' => \$model,\n";
        $code .= "        'formAction' => \$urlGenerator->generate('{$lowerModel}/update', ['id' => \$model->{$primaryKey}]),\n";
        $code .= "        'submitLabel' => 'Simpan Perubahan',\n";
        $code .= "        'showReset' => false,\n";
        $code .= "        'cancelUrl' => \$urlGenerator->generate('{$lowerModel}/index'),\n";
        $code .= "    ]) ?>\n";
        $code .= "</div>\n";

        return $code;
    }
}
