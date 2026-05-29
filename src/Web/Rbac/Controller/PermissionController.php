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

final class PermissionController
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
        $permissions = $this->rbacRepository->getAllPermissions();

        return $this->viewRenderer->render(__DIR__ . '/../View/permissions/index', [
            'permissions' => $permissions,
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

            if ($data['name'] === '') $errors[] = 'Nama izin wajib diisi.';
            if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $data['name'])) {
                $errors[] = 'Nama izin hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).';
            }

            if (empty($errors)) {
                if ($this->rbacRepository->getPermissionByName($data['name']) !== null) {
                    $errors[] = 'Izin dengan nama tersebut sudah ada.';
                } else {
                    try {
                        if ($this->rbacRepository->createPermission($data['name'], $data['description'])) {
                            $this->flash->set('success', 'Izin "' . htmlspecialchars($data['name']) . '" berhasil ditambahkan.');
                            return $this->responseFactory->createResponse(302)
                                ->withHeader('Location', $this->urlGenerator->generate('permissions/index'));
                        } else {
                            $errors[] = 'Gagal menambahkan izin.';
                        }
                    } catch (\Throwable $e) {
                        $errors[] = 'Terjadi kesalahan: ' . $e->getMessage();
                    }
                }
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/permissions/create', [
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $name = (string) $this->currentRoute->getArgument('name');
        $permission = $this->rbacRepository->getPermissionByName($name);

        if ($permission === null) {
            return $this->responseFactory->createResponse(404);
        }

        $errors = [];
        $data = [
            'name' => $permission['name'],
            'description' => $permission['description'] ?? '',
        ];

        if ($request->getMethod() === 'POST') {
            $body = (array) $request->getParsedBody();
            $data['description'] = trim((string) ($body['description'] ?? ''));

            try {
                if ($this->rbacRepository->updatePermission($name, $data['description'])) {
                    $this->flash->set('success', 'Izin "' . htmlspecialchars($name) . '" berhasil diperbarui.');
                    return $this->responseFactory->createResponse(302)
                        ->withHeader('Location', $this->urlGenerator->generate('permissions/index'));
                } else {
                    $errors[] = 'Gagal memperbarui izin.';
                }
            } catch (\Throwable $e) {
                $errors[] = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/permissions/update', [
            'permission' => $permission,
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    public function delete(): ResponseInterface
    {
        $name = (string) $this->currentRoute->getArgument('name');

        if ($name === 'manage_rbac') {
            $this->flash->set('errors', ['Izin "manage_rbac" bersifat bawaan sistem dan tidak boleh dihapus demi alasan keamanan.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('permissions/index'));
        }

        $permission = $this->rbacRepository->getPermissionByName($name);
        if ($permission === null) {
            $this->flash->set('errors', ['Izin tidak ditemukan.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('permissions/index'));
        }

        try {
            $this->rbacRepository->deletePermission($name);
            $this->flash->set('success', 'Izin "' . htmlspecialchars($name) . '" berhasil dihapus.');
        } catch (\Throwable $e) {
            $this->flash->set('errors', ['Gagal menghapus izin: ' . $e->getMessage()]);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('permissions/index'));
    }
}
