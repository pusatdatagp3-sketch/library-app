<?php

declare(strict_types=1);

namespace App\Web\Konsulat\Controller;

use App\Web\Konsulat\Model\Konsulat;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class KonsulatController
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
        $this->repository = $orm->getRepository(Konsulat::class);
    }

    public function index(): ResponseInterface
    {
        $models = $this->repository->select()->orderBy('id', 'DESC')->fetchAll();
        return $this->viewRenderer->render(__DIR__ . '/../View/index', [
            'models' => $models,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs' => $this->flash->get('errors') ?? [],
        ]);
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $model = new Konsulat();

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            $model->load($data);
            if ($model->validate($this->orm)) {
                $this->entityManager->persist($model)->run();
                $this->flash->set('success', 'Konsulat berhasil ditambahkan.');
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('konsulat/index'));
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
            if ($model->validate($this->orm)) {
                $this->entityManager->persist($model)->run();
                $this->flash->set('success', 'Konsulat berhasil diperbarui.');
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('konsulat/index'));
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
            $this->flash->set('success', 'Konsulat berhasil dihapus.');
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('konsulat/index'));
    }
}
