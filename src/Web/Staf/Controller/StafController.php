<?php

declare(strict_types=1);

namespace App\Web\Staf\Controller;

use App\Web\Auth\Model\UserSession;
use App\Web\Staf\Model\StafEntity;
use App\Web\Staf\Model\StafRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Controller untuk Modul Manajemen Staf Perpustakaan.
 * Dilindungi akses eksklusif hanya untuk peran 'super-admin'.
 */
final class StafController
{
    private const ALLOWED_ROLE = 'super-admin';
    private const ALLOWED_DIVISI = ['Library', 'Staff'];

    public function __construct(
        private StafRepository $stafRepository,
        private WebViewRenderer $viewRenderer,
        private UserSession $userSession,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash
    ) {
    }

    /**
     * Memastikan hanya pengguna dengan role super-admin yang diizinkan mengakses modul ini.
     */
    private function checkSuperAdmin(): ?ResponseInterface
    {
        if (!$this->userSession->isLoggedIn()) {
            $this->flash->set('errors', ['Silakan masuk terlebih dahulu untuk mengakses modul ini.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('login'));
        }

        if ($this->userSession->getUserRole() !== self::ALLOWED_ROLE) {
            $this->flash->set('errors', ['Akses ditolak. Modul Manajemen Staf hanya dapat diakses oleh Super Admin.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('perpustakaan/scan'));
        }

        return null;
    }

    /**
     * Halaman Utama: Daftar Seluruh Staf.
     */
    public function index(): ResponseInterface
    {
        if ($accessResponse = $this->checkSuperAdmin()) {
            return $accessResponse;
        }

        $rawStaff = $this->stafRepository->findAll();
        $staffList = is_array($rawStaff) ? $rawStaff : iterator_to_array($rawStaff);

        return $this->viewRenderer->render(__DIR__ . '/../View/index', [
            'staffList'  => $staffList,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs'  => $this->flash->get('errors') ?? [],
        ]);
    }

    /**
     * Tambah Staf Baru (Hanya menangani request POST dari modal index, lalu redirect).
     */
    public function create(Request $request): ResponseInterface
    {
        if ($accessResponse = $this->checkSuperAdmin()) {
            return $accessResponse;
        }

        if ($request->getMethod() === 'POST') {
            $body = (array) $request->getParsedBody();
            $namaStaf = trim((string) ($body['nama_staf'] ?? ''));
            $divisi   = trim((string) ($body['divisi'] ?? 'Library'));
            $isActive = isset($body['is_active']) ? (bool) $body['is_active'] : false;

            $errors = [];
            if ($namaStaf === '') {
                $errors[] = 'Nama staf wajib diisi.';
            }
            if (!in_array($divisi, self::ALLOWED_DIVISI, true)) {
                $errors[] = 'Divisi harus bernilai "Library" atau "Staff".';
            }

            if (!empty($errors)) {
                $this->flash->set('errors', $errors);
            } else {
                try {
                    $staf = new StafEntity($namaStaf, $divisi, $isActive);
                    $this->stafRepository->save($staf);
                    $this->flash->set(
                        'success',
                        'Staf baru "' . htmlspecialchars($namaStaf, ENT_QUOTES, 'UTF-8') . '" berhasil ditambahkan.'
                    );
                } catch (Throwable $e) {
                    $this->flash->set('errors', ['Gagal menambahkan staf: ' . $e->getMessage()]);
                }
            }
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('staf/index'));
    }

    /**
     * Edit Data Staf (GET form / POST update).
     */
    public function update(Request $request): ResponseInterface
    {
        if ($accessResponse = $this->checkSuperAdmin()) {
            return $accessResponse;
        }

        $id = (int) $this->currentRoute->getArgument('id');
        $staf = $this->stafRepository->findById($id);

        if ($staf === null) {
            $this->flash->set('errors', ['Data staf tidak ditemukan.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('staf/index'));
        }

        $errors = [];
        $data = [
            'id'        => $staf->id,
            'nama_staf' => $staf->nama_staf,
            'divisi'    => $staf->divisi,
            'is_active' => $staf->is_active,
        ];

        if ($request->getMethod() === 'POST') {
            $body = (array) $request->getParsedBody();
            $data['nama_staf'] = trim((string) ($body['nama_staf'] ?? ''));
            $data['divisi']    = trim((string) ($body['divisi'] ?? 'Library'));
            $data['is_active'] = isset($body['is_active']) ? (bool) $body['is_active'] : false;

            if ($data['nama_staf'] === '') {
                $errors[] = 'Nama staf tidak boleh kosong.';
            }
            if (!in_array($data['divisi'], self::ALLOWED_DIVISI, true)) {
                $errors[] = 'Divisi harus bernilai "Library" atau "Staff".';
            }

            if (empty($errors)) {
                try {
                    $staf->nama_staf = $data['nama_staf'];
                    $staf->divisi    = $data['divisi'];
                    $staf->is_active = $data['is_active'];

                    $this->stafRepository->save($staf);

                    $this->flash->set(
                        'success',
                        'Data staf "' . htmlspecialchars($data['nama_staf'], ENT_QUOTES, 'UTF-8') . '" berhasil diperbarui.'
                    );
                    return $this->responseFactory->createResponse(302)
                        ->withHeader('Location', $this->urlGenerator->generate('staf/index'));
                } catch (Throwable $e) {
                    $errors[] = 'Gagal memperbarui data staf: ' . $e->getMessage();
                }
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/update', [
            'staf'   => $staf,
            'data'   => $data,
            'errors' => $errors,
        ]);
    }

    /**
     * Mengubah Status Aktif / Non-Aktif Staf (Toggle).
     */
    public function toggle(Request $request): ResponseInterface
    {
        if ($accessResponse = $this->checkSuperAdmin()) {
            return $accessResponse;
        }

        $id = (int) $this->currentRoute->getArgument('id');
        $staf = $this->stafRepository->findById($id);

        if ($staf === null) {
            $this->flash->set('errors', ['Data staf tidak ditemukan.']);
        } else {
            $this->stafRepository->toggleStatus($id);
            $newStatus = !$staf->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $this->flash->set('success', 'Status staf "' . htmlspecialchars($staf->nama_staf, ENT_QUOTES, 'UTF-8') . '" berhasil ' . $newStatus . '.');
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('staf/index'));
    }

    /**
     * Menghapus Data Staf.
     */
    public function delete(Request $request): ResponseInterface
    {
        if ($accessResponse = $this->checkSuperAdmin()) {
            return $accessResponse;
        }

        $id = (int) $this->currentRoute->getArgument('id');
        $staf = $this->stafRepository->findById($id);

        if ($staf === null) {
            $this->flash->set('errors', ['Data staf tidak ditemukan.']);
        } else {
            $nama = $staf->nama_staf;
            $this->stafRepository->deleteById($id);
            $this->flash->set('success', 'Staf "' . htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') . '" berhasil dihapus.');
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('staf/index'));
    }
}
