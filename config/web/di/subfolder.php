<?php

declare(strict_types=1);

use Yiisoft\Aliases\Aliases;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\Middleware\Subfolder;

return [
    Subfolder::class => static function (Aliases $aliases, UrlGeneratorInterface $urlGenerator) {
        $prefix = (PHP_SAPI === 'cli' || PHP_SAPI === 'cli-server') ? null : '/teqic-yii3/public';
        return new Subfolder(
            $urlGenerator,
            $aliases,
            $prefix
        );
    },
];
