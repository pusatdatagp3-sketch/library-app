<?php

declare(strict_types=1);

use App\Shared\ApplicationParams;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Definitions\Reference;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\CsrfViewInjection;

return [
    'application' => require __DIR__ . '/application.php',

    'yiisoft/aliases' => [
        'aliases' => require __DIR__ . '/aliases.php',
    ],

    'yiisoft/view' => [
        'basePath' => null,
        'parameters' => [
            'assetManager' => Reference::to(AssetManager::class),
            'applicationParams' => Reference::to(ApplicationParams::class),
            'aliases' => Reference::to(Aliases::class),
            'urlGenerator' => Reference::to(UrlGeneratorInterface::class),
            'currentRoute' => Reference::to(CurrentRoute::class),
            'userSession' => Reference::to(App\Web\Auth\Model\UserSession::class),
        ],
    ],

    'yiisoft/yii-view-renderer' => [
        'viewPath' => null,
        'layout' => '@src/Web/Shared/Layout/Main/layout.php',
        'injections' => [
            Reference::to(CsrfViewInjection::class),
        ],
    ],

    'yiisoft/yii-cycle' => [
        'migrations' => [
            'table' => 'cycle_migration',
        ],
        'dbal' => [
            'default' => 'default',
            'databases' => [
                'default' => ['connection' => 'mysql'],
            ],
            'connections' => [
                'mysql' => new \Cycle\Database\Config\MySQLDriverConfig(
                    connection: new \Cycle\Database\Config\MySQL\DsnConnectionConfig(
                        dsn: 'mysql:host=' . \App\Environment::dbHost() . ';port=' . \App\Environment::dbPort() . ';dbname=' . \App\Environment::dbName() . ';charset=utf8mb4',
                        user: \App\Environment::dbUser(),
                        password: \App\Environment::dbPassword()
                    ),
                    queryCache: true
                ),
            ],
        ],
        'schema-providers' => [
            \Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider::class => [
                'generators' => [
                    \Cycle\Schema\Generator\ResetTables::class,
                    \Cycle\Schema\Generator\GenerateRelations::class,
                    \Cycle\Schema\Generator\ValidateEntities::class,
                    \Cycle\Schema\Generator\RenderTables::class,
                    \Cycle\Schema\Generator\RenderRelations::class,
                    \Cycle\Schema\Generator\SyncTables::class,
                    \Cycle\Schema\Generator\GenerateTypecast::class,
                ],
            ],
        ],
        'entity-paths' => [
            '@src/Web/Auth/Model',
        ],
    ],
];
