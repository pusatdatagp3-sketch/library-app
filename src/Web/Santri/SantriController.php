<?php

declare(strict_types=1);

namespace App\Web\Santri;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class SantriController
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash
    ) {
    }

    public function index(): ResponseInterface
    {
        $models = Santri::find();
        return $this->viewRenderer->render(__DIR__ . '/views/index', [
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
            if ($model->save()) {
                $this->flash->set('success', 'Data Santri berhasil ditambahkan.');
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('santri/index'));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/views/create', [
            'model' => $model,
        ]);
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $model = Santri::findOne($id);

        if ($model === null) {
            return $this->responseFactory->createResponse(404);
        }

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            $model->load($data);
            if ($model->save()) {
                $this->flash->set('success', 'Data Santri berhasil diperbarui.');
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('santri/index'));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/views/update', [
            'model' => $model,
        ]);
    }

    public function delete(): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $model = Santri::findOne($id);

        if ($model !== null) {
            $model->delete();
            $this->flash->set('success', 'Data Santri berhasil dihapus.');
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('santri/index'));
    }
}
