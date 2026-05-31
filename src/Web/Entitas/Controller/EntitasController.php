<?php

declare(strict_types=1);

namespace App\Web\Entitas\Controller;

use App\Web\Entitas\Model\Entitas;
use App\Web\Entitas\Model\AnggotaEntitas;
use App\Web\Modul\Model\Modul;
use App\Web\Program\Model\Program;
use App\Web\Auth\Model\User;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class EntitasController
{
    private $entitasRepository;
    private $anggotaRepository;
    private $modulRepository;
    private $programRepository;
    private $userRepository;

    public function __construct(
        private WebViewRenderer $viewRenderer,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash,
        private ORMInterface $orm,
        private EntityManagerInterface $entityManager
    ) {
        $this->entitasRepository = $orm->getRepository(Entitas::class);
        $this->anggotaRepository = $orm->getRepository(AnggotaEntitas::class);
        $this->modulRepository = $orm->getRepository(Modul::class);
        $this->programRepository = $orm->getRepository(Program::class);
        $this->userRepository = $orm->getRepository(User::class);
    }

    private function getModulContext(): array
    {
        $routeName = $this->currentRoute->getName() ?? '';
        if (str_contains($routeName, 'fungsionaris')) {
            return [1, 'fungsionaris', 'Fungsionaris KMI', 'fungsionaris/index'];
        } elseif (str_contains($routeName, 'kepanitiaan')) {
            return [2, 'kepanitiaan', 'Kepanitiaan KMI', 'kepanitiaan/index'];
        } else {
            return [3, 'empowering', 'Empowering KMI', 'empowering/index'];
        }
    }

    public function fungsionarisIndex(): ResponseInterface
    {
        return $this->index(1, 'Fungsionaris KMI', 'fungsionaris');
    }

    public function kepanitiaanIndex(): ResponseInterface
    {
        return $this->index(2, 'Kepanitiaan KMI', 'kepanitiaan');
    }

    public function empoweringIndex(): ResponseInterface
    {
        return $this->index(3, 'Empowering KMI', 'empowering');
    }

    private function index(int $modulId, string $title, string $prefix): ResponseInterface
    {
        $models = $this->entitasRepository->select()
            ->where(['modul_id' => $modulId])
            ->orderBy('id', 'DESC')
            ->fetchAll();

        return $this->viewRenderer->render(__DIR__ . '/../View/index', [
            'models' => $models,
            'title' => $title,
            'prefix' => $prefix,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs' => $this->flash->get('errors') ?? [],
        ]);
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        [$modulId, $prefix, $modulTitle, $indexRoute] = $this->getModulContext();
        $model = new Entitas();
        $model->modulId = $modulId;

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            $model->load($data);
            if ($model->validate()) {
                $this->entityManager->persist($model)->run();
                $this->flash->set('success', "Entitas \"{$model->nama}\" berhasil ditambahkan.");
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate($indexRoute));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/create', [
            'model' => $model,
            'prefix' => $prefix,
            'modulTitle' => $modulTitle,
            'indexRoute' => $indexRoute,
        ]);
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        [$modulId, $prefix, $modulTitle, $indexRoute] = $this->getModulContext();
        $id = (int) $this->currentRoute->getArgument('id');
        $model = $this->entitasRepository->findByPK($id);

        if ($model === null) {
            return $this->responseFactory->createResponse(404);
        }

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            $model->load($data);
            if ($model->validate()) {
                $this->entityManager->persist($model)->run();
                $this->flash->set('success', "Entitas \"{$model->nama}\" berhasil diperbarui.");
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate($indexRoute));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/update', [
            'model' => $model,
            'prefix' => $prefix,
            'modulTitle' => $modulTitle,
            'indexRoute' => $indexRoute,
        ]);
    }

    public function delete(): ResponseInterface
    {
        [$modulId, $prefix, $modulTitle, $indexRoute] = $this->getModulContext();
        $id = (int) $this->currentRoute->getArgument('id');
        $model = $this->entitasRepository->findByPK($id);

        if ($model !== null) {
            $this->entityManager->delete($model)->run();
            $this->flash->set('success', "Entitas \"{$model->nama}\" berhasil dihapus.");
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate($indexRoute));
    }

    public function view(ServerRequestInterface $request): ResponseInterface
    {
        [$modulId, $prefix, $modulTitle, $indexRoute] = $this->getModulContext();
        $id = (int) $this->currentRoute->getArgument('id');
        $model = $this->entitasRepository->findByPK($id);

        if ($model === null) {
            return $this->responseFactory->createResponse(404);
        }

        // Get members and programs
        $members = $this->anggotaRepository->select()
            ->where(['entitas_id' => $id])
            ->orderBy('nama_anggota', 'ASC')
            ->fetchAll();

        $programs = $this->programRepository->select()
            ->where(['entitas_id' => $id])
            ->orderBy('id', 'DESC')
            ->fetchAll();

        // Get system users for member assignment dropdown
        $users = $this->userRepository->select()->orderBy('username', 'ASC')->fetchAll();

        return $this->viewRenderer->render(__DIR__ . '/../View/view', [
            'model' => $model,
            'prefix' => $prefix,
            'modulTitle' => $modulTitle,
            'indexRoute' => $indexRoute,
            'members' => $members,
            'programs' => $programs,
            'users' => $users,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs' => $this->flash->get('errors') ?? [],
        ]);
    }

    public function addMember(ServerRequestInterface $request): ResponseInterface
    {
        [$modulId, $prefix, $modulTitle, $indexRoute] = $this->getModulContext();
        $id = (int) $this->currentRoute->getArgument('id');
        
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $data = (array) $request->getParsedBody();
        $member = new AnggotaEntitas();
        $member->entitasId = $id;
        $member->load($data);

        if ($member->validate()) {
            $this->entityManager->persist($member)->run();
            $this->flash->set('success', "Anggota \"{$member->namaAnggota}\" berhasil ditambahkan.");
        } else {
            $this->flash->set('errors', array_values($member->errors));
        }

        $viewRoute = str_replace('/index', '/view', $indexRoute);
        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate($viewRoute, ['id' => $id]));
    }

    public function updateMember(ServerRequestInterface $request): ResponseInterface
    {
        [$modulId, $prefix, $modulTitle, $indexRoute] = $this->getModulContext();
        $entitasId = (int) $this->currentRoute->getArgument('id');
        $memberId = (int) $this->currentRoute->getArgument('memberId');

        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $member = $this->anggotaRepository->findByPK($memberId);
        if ($member === null) {
            return $this->responseFactory->createResponse(404);
        }

        $data = (array) $request->getParsedBody();
        $member->load($data);

        if ($member->validate()) {
            $this->entityManager->persist($member)->run();
            $this->flash->set('success', "Anggota \"{$member->namaAnggota}\" berhasil diperbarui.");
        } else {
            $this->flash->set('errors', array_values($member->errors));
        }

        $viewRoute = str_replace('/index', '/view', $indexRoute);
        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate($viewRoute, ['id' => $entitasId]));
    }

    public function deleteMember(): ResponseInterface
    {
        [$modulId, $prefix, $modulTitle, $indexRoute] = $this->getModulContext();
        $entitasId = (int) $this->currentRoute->getArgument('id');
        $memberId = (int) $this->currentRoute->getArgument('memberId');

        $member = $this->anggotaRepository->findByPK($memberId);
        if ($member !== null) {
            $this->entityManager->delete($member)->run();
            $this->flash->set('success', "Anggota berhasil dihapus.");
        }

        $viewRoute = str_replace('/index', '/view', $indexRoute);
        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate($viewRoute, ['id' => $entitasId]));
    }
}
