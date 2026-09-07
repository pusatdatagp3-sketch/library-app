<?php

declare(strict_types=1);

namespace App\Web\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use App\Web\Auth\Model\UserSession;
use App\Web\Rbac\Model\RbacRepository;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class RbacAccessControlMiddleware implements MiddlewareInterface
{
    /** Role yang mendapat bypass penuh tanpa pengecekan tabel RBAC */
    private const SUPER_ADMIN_ROLE = 'super-admin';

    public function __construct(
        private UserSession $userSession,
        private RbacRepository $rbacRepository,
        private CurrentRoute $currentRoute,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
        private WebViewRenderer $viewRenderer,
        private FlashInterface $flash
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 1. Cek status login terlebih dahulu
        if (!$this->userSession->isLoggedIn()) {
            $this->flash->set('errors', ['Silakan masuk terlebih dahulu untuk mengakses halaman ini.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('login'));
        }

        // 2. BYPASS: Super Admin memiliki akses penuh ke seluruh sistem tanpa
        //    perlu mengecek tabel rbac_route_permissions maupun rbac_role_permissions.
        if ($this->userSession->getUserRole() === self::SUPER_ADMIN_ROLE) {
            return $handler->handle($request);
        }

        // 3. Dapatkan nama rute saat ini dan peta izin rute dari DB
        $routeName          = $this->currentRoute->getName();
        $routePermissionMap = $this->rbacRepository->getRoutePermissionMap();

        if ($routeName !== null && isset($routePermissionMap[$routeName])) {
            $requiredPermission = $routePermissionMap[$routeName];

            // Jika rute memiliki permission yang dipersyaratkan (bukan null/kosong)
            if ($requiredPermission !== null && $requiredPermission !== '') {
                // 4. Cek apakah user memiliki permission yang dibutuhkan
                if (!$this->userSession->hasPermission($requiredPermission)) {
                    if ($request->getMethod() === 'POST') {
                        // Jika POST (aksi), redirect kembali dengan pesan flash
                        $this->flash->set('errors', ['Anda tidak memiliki hak akses untuk melakukan aksi ini.']);
                        $referrer    = $request->getHeaderLine('Referer');
                        $redirectUrl = $referrer !== '' ? $referrer : $this->urlGenerator->generate('home');
                        return $this->responseFactory->createResponse(302)
                            ->withHeader('Location', $redirectUrl);
                    }

                    // Jika GET (navigasi), tampilkan halaman 403
                    return $this->viewRenderer->render(__DIR__ . '/../Shared/View/error403');
                }
            }
        }

        return $handler->handle($request);
    }
}
