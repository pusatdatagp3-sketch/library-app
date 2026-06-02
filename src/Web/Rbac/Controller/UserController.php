<?php

declare(strict_types=1);

namespace App\Web\Rbac\Controller;

use App\Web\Auth\Model\AuthRepository;
use App\Web\Auth\Model\UserSession;
use App\Web\Rbac\Model\RbacRepository;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class UserController
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private RbacRepository $rbacRepository,
        private AuthRepository $authRepository,
        private UserSession $userSession,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash
    ) {
    }

    public function index(): ResponseInterface
    {
        $users = $this->authRepository->getAllUsers();
        $roles = $this->rbacRepository->getAllRoles();
        $campusList = $this->authRepository->getAllCampuses();

        return $this->viewRenderer->render(__DIR__ . '/../View/users/index', [
            'users'      => $users,
            'roles'      => $roles,
            'campusList' => $campusList,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs'  => $this->flash->get('errors') ?? [],
        ]);
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $roles      = $this->rbacRepository->getAllRoles();
        $campusList = $this->authRepository->getAllCampuses();
        $errors     = [];
        $data = [
            'username'         => '',
            'email'            => '',
            'role'             => '',
            'allowed_campuses' => [],
        ];

        if ($request->getMethod() === 'POST') {
            $body = (array) $request->getParsedBody();
            $data['username']         = trim((string) ($body['username'] ?? ''));
            $data['email']            = trim((string) ($body['email'] ?? ''));
            $data['role']             = trim((string) ($body['role'] ?? ''));
            $data['allowed_campuses'] = (array) ($body['allowed_campuses'] ?? []);
            $password = (string) ($body['password'] ?? '');

            if ($data['username'] === '')         $errors[] = 'Username wajib diisi.';
            if ($data['email'] === '')             $errors[] = 'Email wajib diisi.';
            if ($password === '')                  $errors[] = 'Password wajib diisi.';
            if ($data['role'] === '')              $errors[] = 'Peran wajib dipilih.';
            if (empty($data['allowed_campuses'])) $errors[] = 'Minimal satu kampus harus dipilih.';

            if (empty($errors)) {
                if ($this->authRepository->findByUsername($data['username']) !== null) {
                    $errors[] = 'Username sudah digunakan oleh akun lain.';
                } else {
                    try {
                        if ($this->authRepository->createUser($data['username'], $data['email'], $password, $data['role'])) {
                            // Save campus assignments
                            $newUser = $this->authRepository->findByUsername($data['username']);
                            if ($newUser !== null) {
                                $this->authRepository->updateUserCampuses((int)$newUser['id'], $data['allowed_campuses']);
                            }
                            $this->flash->set('success', 'Pengguna baru "' . htmlspecialchars($data['username']) . '" berhasil didaftarkan.');
                            return $this->responseFactory->createResponse(302)
                                ->withHeader('Location', $this->urlGenerator->generate('users/index'));
                        } else {
                            $errors[] = 'Gagal mendaftarkan pengguna baru.';
                        }
                    } catch (\Throwable $e) {
                        $errors[] = 'Terjadi kesalahan: ' . $e->getMessage();
                    }
                }
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/users/create', [
            'roles'      => $roles,
            'campusList' => $campusList,
            'errors'     => $errors,
            'data'       => $data,
        ]);
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $id   = (int) $this->currentRoute->getArgument('id');
        $user = $this->authRepository->findById($id);

        if ($user === null) {
            return $this->responseFactory->createResponse(404);
        }

        $roles      = $this->rbacRepository->getAllRoles();
        $campusList = $this->authRepository->getAllCampuses();
        $errors     = [];
        $data = [
            'username'         => $user['username'],
            'email'            => $user['email'],
            'role'             => $user['role'],
            'allowed_campuses' => $user['allowed_campuses'],
        ];

        if ($request->getMethod() === 'POST') {
            $body = (array) $request->getParsedBody();
            $data['username']         = trim((string) ($body['username'] ?? ''));
            $data['email']            = trim((string) ($body['email'] ?? ''));
            $data['role']             = trim((string) ($body['role'] ?? ''));
            $data['allowed_campuses'] = (array) ($body['allowed_campuses'] ?? []);
            $password = (string) ($body['password'] ?? '');

            if ($data['username'] === '') $errors[] = 'Username wajib diisi.';
            if ($data['email'] === '')    $errors[] = 'Email wajib diisi.';
            if ($data['role'] === '')     $errors[] = 'Peran wajib dipilih.';
            if (empty($data['allowed_campuses'])) $errors[] = 'Minimal satu kampus harus dipilih.';

            // Proteksi: jangan sampai admin mengubah perannya sendiri
            if ($id === $this->userSession->getUserId() && $data['role'] !== 'Admin') {
                $errors[] = 'Anda tidak diperbolehkan mengubah peran Admin Anda sendiri demi keamanan akses.';
            }

            if (empty($errors)) {
                $existingUser = $this->authRepository->findByUsername($data['username']);
                if ($existingUser !== null && (int)$existingUser['id'] !== $id) {
                    $errors[] = 'Username sudah digunakan oleh akun lain.';
                } else {
                    try {
                        if ($this->authRepository->updateUser($id, $data['username'], $data['email'], $password !== '' ? $password : null, $data['role'])) {
                            $this->authRepository->updateUserCampuses($id, $data['allowed_campuses']);
                            $this->flash->set('success', 'Pengguna "' . htmlspecialchars($data['username']) . '" berhasil diperbarui.');
                            return $this->responseFactory->createResponse(302)
                                ->withHeader('Location', $this->urlGenerator->generate('users/index'));
                        } else {
                            $errors[] = 'Gagal memperbarui pengguna.';
                        }
                    } catch (\Throwable $e) {
                        $errors[] = 'Terjadi kesalahan: ' . $e->getMessage();
                    }
                }
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/users/update', [
            'user'       => $user,
            'roles'      => $roles,
            'campusList' => $campusList,
            'errors'     => $errors,
            'data'       => $data,
        ]);
    }

    public function delete(): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        
        if ($id === $this->userSession->getUserId()) {
            $this->flash->set('errors', ['Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('users/index'));
        }

        $user = $this->authRepository->findById($id);
        if ($user === null) {
            $this->flash->set('errors', ['Pengguna tidak ditemukan.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('users/index'));
        }

        // Proteksi: jangan menghapus default admin
        if ($user['username'] === 'admin') {
            $this->flash->set('errors', ['Akun default "admin" tidak diperbolehkan untuk dihapus demi alasan keamanan.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('users/index'));
        }

        try {
            $this->authRepository->deleteUser($id);
            $this->flash->set('success', 'Pengguna "' . htmlspecialchars($user['username']) . '" berhasil dihapus.');
        } catch (\Throwable $e) {
            $this->flash->set('errors', ['Gagal menghapus pengguna: ' . $e->getMessage()]);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('users/index'));
    }
}
