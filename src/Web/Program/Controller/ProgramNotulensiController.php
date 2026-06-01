<?php

declare(strict_types=1);

namespace App\Web\Program\Controller;

use App\Web\Program\Model\Notulensi;
use App\Web\Shared\Service\FileCompressionService;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;

final class ProgramNotulensiController
{
    private $notulensiRepository;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash,
        private ORMInterface $orm,
        private EntityManagerInterface $entityManager,
        private FileCompressionService $fileCompressionService
    ) {
        $this->notulensiRepository = $orm->getRepository(Notulensi::class);
    }

    public function addNotulensi(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $data = (array) $request->getParsedBody();
        $notulensi = new Notulensi();
        $notulensi->programId = $id;
        $notulensi->load($data);

        // Handle File Upload
        $uploadedFiles = $request->getUploadedFiles();
        $file = $uploadedFiles['file'] ?? null;
        if ($file !== null && $file->getClientFilename() !== '') {
            if ($file->getError() !== UPLOAD_ERR_OK) {
                $errorMsg = 'Gagal mengunggah file. ';
                switch ($file->getError()) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errorMsg .= 'Ukuran file melebihi batas maksimum yang diperbolehkan (maksimal 2MB).';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errorMsg .= 'File hanya terunggah sebagian.';
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $errorMsg .= 'Folder temporary di server hilang.';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $errorMsg .= 'Gagal menulis file ke disk server.';
                        break;
                    default:
                        $errorMsg .= 'Kode error: ' . $file->getError();
                }
                $this->flash->set('errors', [$errorMsg]);
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
            }

            // Validate file extension (PDF and Images only)
            $clientFilename = $file->getClientFilename();
            $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
            if (!in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif'], true)) {
                $this->flash->set('errors', ['Format file tidak diizinkan. Hanya menerima file PDF atau Gambar (JPG, JPEG, PNG, GIF).']);
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
            }

            $uploadDir = dirname(__DIR__, 4) . '/public/uploads/entitas_program_notulensi';
            try {
                $filePath = $this->fileCompressionService->compressAndSave(
                    $file,
                    $uploadDir,
                    'uploads/entitas_program_notulensi',
                    'notulen_'
                );
                $notulensi->filePath = $filePath;
            } catch (\Throwable $e) {
                $this->flash->set('errors', ['Gagal memproses file upload: ' . $e->getMessage()]);
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
            }
        }

        if ($notulensi->validate()) {
            $successMsg = "Notulensi berhasil ditambahkan.";
            $this->entityManager->persist($notulensi)->run();
            $this->flash->set('success', $successMsg);
        } else {
            $this->flash->set('errors', array_values($notulensi->errors));
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
    }

    public function deleteNotulensi(): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $notulensiId = (int) $this->currentRoute->getArgument('notulensiId');

        $notulensi = $this->notulensiRepository->findByPK($notulensiId);
        if ($notulensi !== null) {
            // Delete file if exists
            if ($notulensi->filePath !== null) {
                $fullPath = dirname(__DIR__, 4) . '/public/' . $notulensi->filePath;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            $successMsg = "Notulensi berhasil dihapus.";
            $this->entityManager->delete($notulensi)->run();
            $this->flash->set('success', $successMsg);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
    }
}
