<?php

declare(strict_types=1);

namespace App\Web\Santri\Controller;

use App\Web\Santri\Model\Santri;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class SantriController
{
    private $repository;

    public function __construct(
        private WebViewRenderer $viewRenderer,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash,
        private ORMInterface $orm,
        private EntityManagerInterface $entityManager
    ) {
        $this->repository = $orm->getRepository(Santri::class);
    }

    public function index(): ResponseInterface
    {
        $models = $this->repository->select()->orderBy('kds', 'DESC')->fetchAll();
        return $this->viewRenderer->render(__DIR__ . '/../View/index', [
            'models' => $models,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs' => $this->flash->get('errors') ?? [],
        ]);
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $model = new Santri();

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            $model->load($data);
            if ($model->validate()) {
                $this->entityManager->persist($model)->run();
                $this->flash->set('success', 'Data Santri berhasil ditambahkan.');
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('santri/index'));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/create', [
            'model' => $model,
        ]);
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $model = $this->repository->findByPK($id);

        if ($model === null) {
            return $this->responseFactory->createResponse(404);
        }

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            $model->load($data);
            if ($model->validate()) {
                $this->entityManager->persist($model)->run();
                $this->flash->set('success', 'Data Santri berhasil diperbarui.');
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('santri/index'));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/update', [
            'model' => $model,
        ]);
    }

    public function delete(): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $model = $this->repository->findByPK($id);

        if ($model !== null) {
            $this->entityManager->delete($model)->run();
            $this->flash->set('success', 'Data Santri berhasil dihapus.');
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('santri/index'));
    }
}
