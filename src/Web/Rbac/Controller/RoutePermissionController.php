<?php

declare(strict_types=1);

namespace App\Web\Rbac\Controller;

use App\Web\Auth\Model\UserSession;
use App\Web\Rbac\Model\RbacRepository;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Router\RouteCollectionInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class RoutePermissionController
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private RbacRepository $rbacRepository,
        private UserSession $userSession,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private RouteCollectionInterface $routeCollection,
        private FlashInterface $flash
    ) {
    }

    public function index(): ResponseInterface
    {
        $permissions = $this->rbacRepository->getAllPermissions();

        // Fetch active routes dynamically from RouteCollection
        $allRoutes = $this->routeCollection->getRoutes();
        $dynamicRouteNames = [];
        foreach ($allRoutes as $route) {
            $name = $route->getData('name');
            // Only capture defined route names (no spaces)
            if ($name && !str_contains($name, ' ')) {
                $dynamicRouteNames[] = $name;
            }
        }

        $dbRouteMap = $this->rbacRepository->getRoutePermissionMap();
        
        // Merge dynamic route names with database mapping
        $routePermissionMap = [];
        foreach ($dynamicRouteNames as $name) {
            $routePermissionMap[$name] = $dbRouteMap[$name] ?? null;
        }

        // Add old database mappings that might not match current route names
        foreach ($dbRouteMap as $name => $perm) {
            if (!isset($routePermissionMap[$name])) {
                $routePermissionMap[$name] = $perm;
            }
        }

        ksort($routePermissionMap);

        return $this->viewRenderer->render(__DIR__ . '/../View/routes/index', [
            'routePermissionMap' => $routePermissionMap,
            'permissions' => $permissions,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs' => $this->flash->get('errors') ?? [],
        ]);
    }

    public function saveRoutePermissions(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $body = (array) $request->getParsedBody();
        $routeMappings = $body['route_permissions'] ?? [];

        try {
            $this->rbacRepository->saveRoutePermissions($routeMappings);
            $this->flash->set('success', 'Konfigurasi proteksi rute berhasil diperbarui.');
        } catch (\Throwable $e) {
            $this->flash->set('errors', ['Gagal menyimpan konfigurasi proteksi rute: ' . $e->getMessage()]);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('routes/index'));
    }
}
