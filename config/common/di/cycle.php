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

    \App\Web\Perpustakaan\Model\KunjunganRepository::class => static function (
        ORMInterface $orm,
        EntityManagerInterface $em
    ): \App\Web\Perpustakaan\Model\KunjunganRepository {
        /** @var \App\Web\Perpustakaan\Model\KunjunganRepository $repo */
        $repo = $orm->getRepository(\App\Web\Perpustakaan\Model\KunjunganEntity::class);
        $repo->setEntityManager($em);
        return $repo;
    },

    \App\Web\Perpustakaan\Model\SiswaRepository::class => static function (
        DatabaseInterface $db
    ): \App\Web\Perpustakaan\Model\SiswaRepository {
        return new \App\Web\Perpustakaan\Model\SiswaRepository($db);
    },
];
