<?php

declare(strict_types=1);

use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;

/**
 * Bind Cycle DatabaseInterface to the default database from DatabaseManager.
 * This lets repositories accept DatabaseInterface in their constructor.
 */
return [
    DatabaseInterface::class => static function (DatabaseProviderInterface $dbManager): DatabaseInterface {
        return $dbManager->database('default');
    },
];
