<?php

declare(strict_types=1);

namespace App\Web\Middleware;

use App\Shared\TenantContext;
use App\Web\Auth\Model\UserSession;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class TenantContextMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UserSession $userSession,
        private TenantContext $tenantContext
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->userSession->isLoggedIn()) {
            $allowed = $this->userSession->getAllowedCampuses();
            $active = $this->userSession->getActiveCampus();

            $this->tenantContext->setAllowedCampusCodes($allowed);
            $this->tenantContext->setActiveCampusCode($active);
        }

        return $handler->handle($request);
    }
}
