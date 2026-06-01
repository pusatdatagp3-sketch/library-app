<?php

declare(strict_types=1);

namespace App\Web\Program\Controller;

use App\Web\Program\Model\KendalaProgram;
use App\Web\Program\Model\KendalaProgramFoto;
use App\Web\Shared\Service\FileCompressionService;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;

final class ProgramKendalaController
{
    private $kendalaRepository;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash,
        private ORMInterface $orm,
        private EntityManagerInterface $entityManager,
        private FileCompressionService $fileCompressionService
    ) {
        $this->kendalaRepository = $orm->getRepository(KendalaProgram::class);
    }

    public function addKendala(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $data = (array) $request->getParsedBody();
        $kendala = new KendalaProgram();
        $kendala->programId = $id;
        $kendala->load($data);

        // Handle Optional File Uploads
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

        // Validate files if any are uploaded
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

        // Validate kendala
        if (!$kendala->validate()) {
            $this->flash->set('errors', array_values($kendala->errors));
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
        }

        // Persist Kendala Program first
        try {
            $this->entityManager->persist($kendala)->run();
        } catch (\Throwable $e) {
            $this->flash->set('errors', ['Gagal menyimpan kendala: ' . $e->getMessage()]);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
        }

        // Save uploaded files if any
        $uploadDir = dirname(__DIR__, 4) . '/public/uploads/entitas_program_kendala';
        $successCount = 0;
        foreach ($validFiles as $file) {
            try {
                $filePath = $this->fileCompressionService->compressAndSave(
                    $file,
                    $uploadDir,
                    'uploads/entitas_program_kendala',
                    'kendala_'
                );

                $foto = new KendalaProgramFoto();
                $foto->kendalaId = $kendala->id;
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
            $this->flash->set('success', "Kendala berhasil dicatat dengan $successCount foto.");
        } else {
            $this->flash->set('success', "Kendala berhasil dicatat.");
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
    }

    public function resolveKendala(): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $kendalaId = (int) $this->currentRoute->getArgument('kendalaId');

        $kendala = $this->kendalaRepository->findByPK($kendalaId);
        if ($kendala !== null) {
            $kendala->jenis = 'tertutup';
            $successMsg = "Kendala telah ditandai selesai/tertutup.";
            $this->entityManager->persist($kendala)->run();
            $this->flash->set('success', $successMsg);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
    }

    public function deleteKendala(): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $kendalaId = (int) $this->currentRoute->getArgument('kendalaId');

        $kendala = $this->kendalaRepository->findByPK($kendalaId);
        if ($kendala !== null) {
            // Delete photos from disk
            foreach ($kendala->fotos as $foto) {
                if ($foto->filePath !== '') {
                    $fullPath = dirname(__DIR__, 4) . '/public/' . $foto->filePath;
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }
            }

            $successMsg = "Kendala berhasil dihapus.";
            $this->entityManager->delete($kendala)->run();
            $this->flash->set('success', $successMsg);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
    }
}
