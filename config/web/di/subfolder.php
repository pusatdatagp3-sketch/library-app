<?php

declare(strict_types=1);

use Yiisoft\Aliases\Aliases;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\Middleware\Subfolder;

return [
    Subfolder::class => static function (Aliases $aliases, UrlGeneratorInterface $urlGenerator) {
        return new Subfolder(
            $urlGenerator,
            $aliases,
            '/teqic-yii3' // Prefix subfolder Anda
        );
    },
];
