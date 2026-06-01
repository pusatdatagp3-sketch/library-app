<?php

declare(strict_types=1);

namespace App\Web\Program\Controller;

use App\Web\Program\Model\Dokumentasi;
use App\Web\Program\Model\DokumentasiFoto;
use App\Web\Shared\Service\FileCompressionService;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;

final class ProgramDokumentasiController
{
    private $dokumentasiRepository;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash,
        private ORMInterface $orm,
        private EntityManagerInterface $entityManager,
        private FileCompressionService $fileCompressionService
    ) {
        $this->dokumentasiRepository = $orm->getRepository(Dokumentasi::class);
    }

    public function addDokumentasi(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $data = (array) $request->getParsedBody();

        // Handle File Upload
        $uploadedFiles = $request->getUploadedFiles();
        $files = $uploadedFiles['files'] ?? [];
        if (!is_array($files)) {
            $files = [$files];
        }

        // Filter out empty files
        $validFiles = [];
        foreach ($files as $file) {
            if ($file !== null && $file->getClientFilename() !== '') {
                $validFiles[] = $file;
            }
        }

        if (empty($validFiles)) {
            $this->flash->set('errors', ['File dokumentasi wajib diunggah.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
        }

        // Validate all files first (all-or-nothing approach)
        $errors = [];
        foreach ($validFiles as $index => $file) {
            if ($file->getError() !== UPLOAD_ERR_OK) {
                $errorMsg = 'File #' . ($index + 1) . ' gagal diunggah: ';
                switch ($file->getError()) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errorMsg .= 'Ukuran file melebihi batas maksimum 2MB.';
                        break;
                    default:
                        $errorMsg .= 'Kode error: ' . $file->getError();
                }
                $errors[] = $errorMsg;
                continue;
            }

            $clientFilename = $file->getClientFilename();
            $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $errors[] = 'File #' . ($index + 1) . ' memiliki format tidak diizinkan. Hanya menerima Gambar (JPG, JPEG, PNG, GIF, WEBP).';
            }
        }

        if (!empty($errors)) {
            $this->flash->set('errors', $errors);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
        }

        // Validate title (judul) first
        $dokumentasi = new Dokumentasi();
        $dokumentasi->programId = $id;
        $dokumentasi->load($data);
        if (!$dokumentasi->validate()) {
            $this->flash->set('errors', array_values($dokumentasi->errors));
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
        }

        try {
            $this->entityManager->persist($dokumentasi)->run();
        } catch (\Throwable $e) {
            $this->flash->set('errors', ['Gagal menyimpan dokumentasi: ' . $e->getMessage()]);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
        }

        $uploadDir = dirname(__DIR__, 4) . '/public/uploads/entitas_program_dokumentasi';
        $successCount = 0;
        foreach ($validFiles as $file) {
            try {
                $filePath = $this->fileCompressionService->compressAndSave(
                    $file,
                    $uploadDir,
                    'uploads/entitas_program_dokumentasi',
                    'dokumentasi_'
                );

                $foto = new DokumentasiFoto();
                $foto->dokumentasiId = $dokumentasi->id;
                $foto->filePath = $filePath;
                
                $this->entityManager->persist($foto);
                $successCount++;
            } catch (\Throwable $e) {
                $this->flash->set('errors', ['Gagal memproses file upload: ' . $e->getMessage()]);
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
            }
        }

        if ($successCount > 0) {
            $this->entityManager->run();
            $this->flash->set('success', "Dokumentasi berhasil diunggah dengan $successCount foto.");
        } else {
            $this->flash->set('success', "Dokumentasi berhasil dibuat.");
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
    }

    public function deleteDokumentasi(): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $dokumentasiId = (int) $this->currentRoute->getArgument('dokumentasiId');

        $dokumentasi = $this->dokumentasiRepository->findByPK($dokumentasiId);
        if ($dokumentasi !== null) {
            // Delete all associated files from disk
            foreach ($dokumentasi->fotos as $foto) {
                if ($foto->filePath !== '') {
                    $fullPath = dirname(__DIR__, 4) . '/public/' . $foto->filePath;
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }
            }
            
            $successMsg = "Dokumentasi berhasil dihapus.";
            $this->entityManager->delete($dokumentasi)->run();
            $this->flash->set('success', $successMsg);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
    }
}
