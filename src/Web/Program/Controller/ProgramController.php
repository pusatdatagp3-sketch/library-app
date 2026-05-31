<?php

declare(strict_types=1);

namespace App\Web\Program\Controller;

use App\Web\Program\Model\Program;
use App\Web\Program\Model\KendalaProgram;
use App\Web\Program\Model\Notulensi;
use App\Web\Program\Model\Dokumentasi;
use App\Web\Entitas\Model\Entitas;
use App\Web\Entitas\Model\AnggotaEntitas;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class ProgramController
{
    private $programRepository;
    private $kendalaRepository;
    private $notulensiRepository;
    private $dokumentasiRepository;
    private $entitasRepository;
    private $anggotaRepository;

    public function __construct(
        private WebViewRenderer $viewRenderer,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash,
        private ORMInterface $orm,
        private EntityManagerInterface $entityManager
    ) {
        $this->programRepository = $orm->getRepository(Program::class);
        $this->kendalaRepository = $orm->getRepository(KendalaProgram::class);
        $this->notulensiRepository = $orm->getRepository(Notulensi::class);
        $this->dokumentasiRepository = $orm->getRepository(Dokumentasi::class);
        $this->entitasRepository = $orm->getRepository(Entitas::class);
        $this->anggotaRepository = $orm->getRepository(AnggotaEntitas::class);
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $entitasId = isset($queryParams['entitas_id']) ? (int)$queryParams['entitas_id'] : null;

        $model = new Program();
        if ($entitasId !== null) {
            $model->entitasId = $entitasId;
        }

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            $model->load($data);
            if ($model->validate()) {
                $this->entityManager->persist($model)->run();
                $this->flash->set('success', "Program \"{$model->namaProgram}\" berhasil dibuat.");
                
                // Get modul prefix for redirection
                $entitas = $this->entitasRepository->findByPK($model->entitasId);
                $redirectUrl = $this->getRedirectUrlForEntitas($entitas);
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $redirectUrl);
            }
        }

        $entitasList = $this->entitasRepository->select()->orderBy('nama', 'ASC')->fetchAll();
        
        // Members list for Penanggung Jawab dropdown
        $members = [];
        if ($model->entitasId !== null) {
            $members = $this->anggotaRepository->select()
                ->where(['entitas_id' => $model->entitasId])
                ->orderBy('nama_anggota', 'ASC')
                ->fetchAll();
        } else {
            $members = $this->anggotaRepository->select()->orderBy('nama_anggota', 'ASC')->fetchAll();
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/create', [
            'model' => $model,
            'entitasList' => $entitasList,
            'members' => $members,
            'backUrl' => $entitasId !== null ? $this->getRedirectUrlForEntitas($this->entitasRepository->findByPK($entitasId)) : $this->urlGenerator->generate('home'),
        ]);
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $model = $this->programRepository->findByPK($id);

        if ($model === null) {
            return $this->responseFactory->createResponse(404);
        }

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            $model->load($data);
            if ($model->validate()) {
                $this->entityManager->persist($model)->run();
                $this->flash->set('success', "Program \"{$model->namaProgram}\" berhasil diperbarui.");
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $model->id]));
            }
        }

        $entitasList = $this->entitasRepository->select()->orderBy('nama', 'ASC')->fetchAll();
        
        $members = $this->anggotaRepository->select()
            ->where(['entitas_id' => $model->entitasId])
            ->orderBy('nama_anggota', 'ASC')
            ->fetchAll();

        return $this->viewRenderer->render(__DIR__ . '/../View/update', [
            'model' => $model,
            'entitasList' => $entitasList,
            'members' => $members,
        ]);
    }

    public function delete(): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $model = $this->programRepository->findByPK($id);

        if ($model !== null) {
            $entitas = $this->entitasRepository->findByPK($model->entitasId);
            $redirectUrl = $this->getRedirectUrlForEntitas($entitas);
            
            $this->entityManager->delete($model)->run();
            $this->flash->set('success', "Program berhasil dihapus.");
            return $this->responseFactory->createResponse(302)->withHeader('Location', $redirectUrl);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('home'));
    }

    public function view(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $model = $this->programRepository->findByPK($id);

        if ($model === null) {
            return $this->responseFactory->createResponse(404);
        }

        $kendalaList = $this->kendalaRepository->select()
            ->where(['program_id' => $id])
            ->orderBy('id', 'DESC')
            ->fetchAll();

        $notulensiList = $this->notulensiRepository->select()
            ->where(['program_id' => $id])
            ->orderBy('id', 'DESC')
            ->fetchAll();

        $dokumentasiList = $this->dokumentasiRepository->select()
            ->where(['program_id' => $id])
            ->orderBy('id', 'DESC')
            ->fetchAll();

        $entitas = $this->entitasRepository->findByPK($model->entitasId);
        $backUrl = $this->getRedirectUrlForEntitas($entitas);

        return $this->viewRenderer->render(__DIR__ . '/../View/view', [
            'model' => $model,
            'kendalaList' => $kendalaList,
            'notulensiList' => $notulensiList,
            'dokumentasiList' => $dokumentasiList,
            'backUrl' => $backUrl,
            'entitas' => $entitas,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs' => $this->flash->get('errors') ?? [],
        ]);
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

        if ($kendala->validate()) {
            $this->entityManager->persist($kendala)->run();
            $this->flash->set('success', "Kendala berhasil dicatat.");
        } else {
            $this->flash->set('errors', array_values($kendala->errors));
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
            $this->entityManager->persist($kendala)->run();
            $this->flash->set('success', "Kendala telah ditandai selesai/tertutup.");
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
            $this->entityManager->delete($kendala)->run();
            $this->flash->set('success', "Kendala berhasil dihapus.");
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
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
        if ($file !== null && $file->getError() === UPLOAD_ERR_OK) {
            $clientFilename = $file->getClientFilename();
            $extension = pathinfo($clientFilename, PATHINFO_EXTENSION);
            $filename = uniqid('notulen_') . '.' . $extension;

            $uploadDir = dirname(__DIR__, 4) . '/public/uploads/notulensi';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            try {
                $file->moveTo($uploadDir . '/' . $filename);
                $notulensi->filePath = 'uploads/notulensi/' . $filename;
            } catch (\Throwable $e) {
                $this->flash->set('errors', ['Gagal memproses file upload: ' . $e->getMessage()]);
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
            }
        }

        if ($notulensi->validate()) {
            $this->entityManager->persist($notulensi)->run();
            $this->flash->set('success', "Notulensi berhasil ditambahkan.");
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
            $this->entityManager->delete($notulensi)->run();
            $this->flash->set('success', "Notulensi berhasil dihapus.");
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
    }

    public function addDokumentasi(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $data = (array) $request->getParsedBody();
        $dokumentasi = new Dokumentasi();
        $dokumentasi->programId = $id;
        $dokumentasi->load($data);

        // Handle File Upload
        $uploadedFiles = $request->getUploadedFiles();
        $file = $uploadedFiles['file'] ?? null;
        if ($file !== null && $file->getError() === UPLOAD_ERR_OK) {
            $clientFilename = $file->getClientFilename();
            $extension = pathinfo($clientFilename, PATHINFO_EXTENSION);
            $filename = uniqid('dokumentasi_') . '.' . $extension;

            $uploadDir = dirname(__DIR__, 4) . '/public/uploads/dokumentasi';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            try {
                $file->moveTo($uploadDir . '/' . $filename);
                $dokumentasi->filePath = 'uploads/dokumentasi/' . $filename;
            } catch (\Throwable $e) {
                $this->flash->set('errors', ['Gagal memproses file upload: ' . $e->getMessage()]);
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
            }
        }

        if ($dokumentasi->validate()) {
            $this->entityManager->persist($dokumentasi)->run();
            $this->flash->set('success', "Dokumentasi berhasil diunggah.");
        } else {
            $this->flash->set('errors', array_values($dokumentasi->errors));
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
            // Delete file if exists
            if ($dokumentasi->filePath !== null) {
                $fullPath = dirname(__DIR__, 4) . '/public/' . $dokumentasi->filePath;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            $this->entityManager->delete($dokumentasi)->run();
            $this->flash->set('success', "Dokumentasi berhasil dihapus.");
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('program/view', ['id' => $id]));
    }

    private function getRedirectUrlForEntitas(?Entitas $entitas): string
    {
        if ($entitas === null) {
            return $this->urlGenerator->generate('home');
        }

        $viewRoute = 'fungsionaris/view';
        if ($entitas->modulId === 2) {
            $viewRoute = 'kepanitiaan/view';
        } elseif ($entitas->modulId === 3) {
            $viewRoute = 'empowering/view';
        }

        return $this->urlGenerator->generate($viewRoute, ['id' => $entitas->id]);
    }
}
