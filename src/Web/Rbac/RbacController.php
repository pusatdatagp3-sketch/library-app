<?php

declare(strict_types=1);

namespace App\Web\Rbac;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use App\Web\Auth\AuthRepository;
use App\Web\Auth\UserSession;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

use Yiisoft\Router\RouteCollectionInterface;

final class RbacController
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private RbacRepository $rbacRepository,
        private AuthRepository $authRepository,
        private UserSession $userSession,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private FlashInterface $flash,
        private RouteCollectionInterface $routeCollection
    ) {
    }

    public function index(): ResponseInterface
    {
        $roles = $this->rbacRepository->getAllRoles();
        $permissions = $this->rbacRepository->getAllPermissions();
        $users = $this->authRepository->getAllUsers();
        
        // Ambil rute aktif secara dinamis dari RouteCollection
        $allRoutes = $this->routeCollection->getRoutes();
        $dynamicRouteNames = [];
        foreach ($allRoutes as $route) {
            $name = $route->getData('name');
            // Hanya ambil rute yang memiliki nama terdefinisi (tanpa spasi)
            if ($name && !str_contains($name, ' ')) {
                $dynamicRouteNames[] = $name;
            }
        }

        $dbRouteMap = $this->rbacRepository->getRoutePermissionMap();
        
        // Gabungkan rute dinamis dengan pemetaan di database
        $routePermissionMap = [];
        foreach ($dynamicRouteNames as $name) {
            $routePermissionMap[$name] = $dbRouteMap[$name] ?? null;
        }

        // Pastikan pemetaan lama di database tetap muncul jika ada
        foreach ($dbRouteMap as $name => $perm) {
            if (!isset($routePermissionMap[$name])) {
                $routePermissionMap[$name] = $perm;
            }
        }

        ksort($routePermissionMap);

        // Bangun matriks peranan-izin saat ini
        $matrix = [];
        foreach ($roles as $role) {
            $rolePerms = $this->rbacRepository->getRolePermissions($role['name']);
            foreach ($permissions as $perm) {
                $matrix[$role['name']][$perm['name']] = in_array($perm['name'], $rolePerms, true);
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/views/index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'matrix' => $matrix,
            'users' => $users,
            'routePermissionMap' => $routePermissionMap,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs' => $this->flash->get('errors') ?? [],
        ]);
    }

    public function saveMatrix(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $body = (array) $request->getParsedBody();
        $inputMatrix = $body['matrix'] ?? []; // format: [role_name => [perm_name => "1", ...]]

        $formattedMatrix = [];
        $roles = $this->rbacRepository->getAllRoles();
        $permissions = $this->rbacRepository->getAllPermissions();

        // Inisialisasi setiap role di matriks agar role tanpa izin pun terdaftar kosong
        foreach ($roles as $role) {
            $formattedMatrix[$role['name']] = [];
        }

        foreach ($inputMatrix as $roleName => $perms) {
            if (!isset($formattedMatrix[$roleName])) {
                continue;
            }
            foreach ($perms as $permName => $value) {
                // Pastikan izin tersebut valid
                $isValidPerm = false;
                foreach ($permissions as $p) {
                    if ($p['name'] === $permName) {
                        $isValidPerm = true;
                        break;
                    }
                }
                if ($isValidPerm && $value === '1') {
                    $formattedMatrix[$roleName][] = $permName;
                }
            }
        }

        try {
            $this->rbacRepository->saveRolePermissionsMatrix($formattedMatrix);
            $this->flash->set('success', 'Matriks hak akses (RBAC) berhasil diperbarui.');
        } catch (\Throwable $e) {
            $this->flash->set('errors', ['Gagal menyimpan matriks: ' . $e->getMessage()]);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('rbac/index'));
    }

    public function updateUserRole(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $body = (array) $request->getParsedBody();
        $userId = (int) ($body['user_id'] ?? 0);
        $role = trim((string) ($body['role'] ?? ''));

        if ($userId <= 0 || $role === '') {
            $this->flash->set('errors', ['Data tidak lengkap untuk mengubah peran.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('rbac/index'));
        }

        // Cari tahu apakah user tersebut valid
        $user = $this->authRepository->findById($userId);
        if ($user === null) {
            $this->flash->set('errors', ['Pengguna tidak ditemukan.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('rbac/index'));
        }

        // Proteksi: jangan sampai admin mengubah perannya sendiri secara tidak sengaja sehingga kehilangan akses rbac
        if ($userId === $this->userSession->getUserId() && $role !== 'Admin') {
            $this->flash->set('errors', ['Anda tidak diperbolehkan mengubah peran Admin Anda sendiri demi keamanan akses.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('rbac/index'));
        }

        if ($this->authRepository->updateUserRole($userId, $role)) {
            $this->flash->set('success', 'Peran untuk user "' . htmlspecialchars($user['username']) . '" berhasil diubah menjadi ' . htmlspecialchars($role) . '.');
        } else {
            $this->flash->set('errors', ['Gagal memperbarui peran pengguna.']);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('rbac/index'));
    }

    public function createUser(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $body = (array) $request->getParsedBody();
        $username = trim((string) ($body['username'] ?? ''));
        $email = trim((string) ($body['email'] ?? ''));
        $password = (string) ($body['password'] ?? '');
        $role = trim((string) ($body['role'] ?? ''));

        $errors = [];
        if ($username === '') $errors[] = 'Username wajib diisi.';
        if ($email === '') $errors[] = 'Email wajib diisi.';
        if ($password === '') $errors[] = 'Password wajib diisi.';
        if ($role === '') $errors[] = 'Peran wajib dipilih.';

        if (empty($errors)) {
            // Cek jika username sudah terpakai
            if ($this->authRepository->findByUsername($username) !== null) {
                $errors[] = 'Username sudah digunakan oleh akun lain.';
            } else {
                try {
                    if ($this->authRepository->createUser($username, $email, $password, $role)) {
                        $this->flash->set('success', 'Pengguna baru "' . htmlspecialchars($username) . '" berhasil didaftarkan.');
                    } else {
                        $errors[] = 'Gagal mendaftarkan pengguna baru.';
                    }
                } catch (\Throwable $e) {
                    $errors[] = 'Terjadi kesalahan database: ' . $e->getMessage();
                }
            }
        }

        if (!empty($errors)) {
            $this->flash->set('errors', $errors);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('rbac/index'));
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
            ->withHeader('Location', $this->urlGenerator->generate('rbac/index'));
    }
}
