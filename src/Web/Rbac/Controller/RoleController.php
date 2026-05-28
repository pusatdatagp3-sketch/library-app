<?php

declare(strict_types=1);

namespace App\Web\Rbac\Controller;

use App\Web\Auth\Model\UserSession;
use App\Web\Rbac\Model\RbacRepository;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class RoleController
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private RbacRepository $rbacRepository,
        private UserSession $userSession,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash
    ) {
    }

    public function index(): ResponseInterface
    {
        $roles = $this->rbacRepository->getAllRoles();
        $permissions = $this->rbacRepository->getAllPermissions();

        // Build matrix
        $matrix = [];
        foreach ($roles as $role) {
            $rolePerms = $this->rbacRepository->getRolePermissions($role['name']);
            foreach ($permissions as $perm) {
                $matrix[$role['name']][$perm['name']] = in_array($perm['name'], $rolePerms, true);
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/roles/index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'matrix' => $matrix,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs' => $this->flash->get('errors') ?? [],
        ]);
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $errors = [];
        $data = [
            'name' => '',
            'description' => '',
        ];

        if ($request->getMethod() === 'POST') {
            $body = (array) $request->getParsedBody();
            $data['name'] = trim((string) ($body['name'] ?? ''));
            $data['description'] = trim((string) ($body['description'] ?? ''));

            if ($data['name'] === '') $errors[] = 'Nama peran wajib diisi.';
            // Hanya izinkan alfanumerik dan garis bawah untuk nama peran demi keselamatan routing
            if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $data['name'])) {
                $errors[] = 'Nama peran hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).';
            }

            if (empty($errors)) {
                if ($this->rbacRepository->getRoleByName($data['name']) !== null) {
                    $errors[] = 'Peran dengan nama tersebut sudah ada.';
                } else {
                    try {
                        if ($this->rbacRepository->createRole($data['name'], $data['description'])) {
                            $this->flash->set('success', 'Peran "' . htmlspecialchars($data['name']) . '" berhasil ditambahkan.');
                            return $this->responseFactory->createResponse(302)
                                ->withHeader('Location', $this->urlGenerator->generate('roles/index'));
                        } else {
                            $errors[] = 'Gagal menambahkan peran.';
                        }
                    } catch (\Throwable $e) {
                        $errors[] = 'Terjadi kesalahan: ' . $e->getMessage();
                    }
                }
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/roles/create', [
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $name = (string) $this->currentRoute->getArgument('name');
        $role = $this->rbacRepository->getRoleByName($name);

        if ($role === null) {
            return $this->responseFactory->createResponse(404);
        }

        $errors = [];
        $data = [
            'name' => $role['name'],
            'description' => $role['description'] ?? '',
        ];

        if ($request->getMethod() === 'POST') {
            $body = (array) $request->getParsedBody();
            $data['description'] = trim((string) ($body['description'] ?? ''));

            try {
                if ($this->rbacRepository->updateRole($name, $data['description'])) {
                    $this->flash->set('success', 'Peran "' . htmlspecialchars($name) . '" berhasil diperbarui.');
                    return $this->responseFactory->createResponse(302)
                        ->withHeader('Location', $this->urlGenerator->generate('roles/index'));
                } else {
                    $errors[] = 'Gagal memperbarui peran.';
                }
            } catch (\Throwable $e) {
                $errors[] = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/roles/update', [
            'role' => $role,
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    public function delete(): ResponseInterface
    {
        $name = (string) $this->currentRoute->getArgument('name');

        if ($name === 'Admin') {
            $this->flash->set('errors', ['Peran "Admin" bersifat bawaan sistem dan tidak boleh dihapus.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('roles/index'));
        }

        $role = $this->rbacRepository->getRoleByName($name);
        if ($role === null) {
            $this->flash->set('errors', ['Peran tidak ditemukan.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('roles/index'));
        }

        try {
            $this->rbacRepository->deleteRole($name);
            $this->flash->set('success', 'Peran "' . htmlspecialchars($name) . '" berhasil dihapus.');
        } catch (\Throwable $e) {
            $this->flash->set('errors', ['Gagal menghapus peran: ' . $e->getMessage()]);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('roles/index'));
    }

    public function saveMatrix(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $body = (array) $request->getParsedBody();
        $inputMatrix = $body['matrix'] ?? [];

        $formattedMatrix = [];
        $roles = $this->rbacRepository->getAllRoles();
        $permissions = $this->rbacRepository->getAllPermissions();

        foreach ($roles as $role) {
            $formattedMatrix[$role['name']] = [];
        }

        foreach ($inputMatrix as $roleName => $perms) {
            if (!isset($formattedMatrix[$roleName])) {
                continue;
            }
            foreach ($perms as $permName => $value) {
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
            ->withHeader('Location', $this->urlGenerator->generate('roles/index'));
    }
}
