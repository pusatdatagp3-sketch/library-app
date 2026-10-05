<?php

declare(strict_types=1);

use App\Environment;
use App\Shared\Service\SidasApiClient;

return [
    SidasApiClient::class => static function (): SidasApiClient {
        return new SidasApiClient(
            baseUrl: Environment::sidasBaseUrl(),
            secret: Environment::sidasSecret(),
            appId: Environment::sidasAppId(),
            timeout: Environment::sidasTimeout(),
        );
    },
];
