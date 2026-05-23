<?php

declare(strict_types=1);

namespace App\Web\Guru;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class GuruController
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private GuruService $guruService,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute
    ) {
    }

    public function index(): ResponseInterface
    {
        $guruList = $this->guruService->getAllGuru();
        return $this->viewRenderer->render(__DIR__ . '/views/index', [
            'guruList' => $guruList
        ]);
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $errors = [];
        $data = [
            'stambuk' => '',
            'nama' => '',
            'daerah' => '',
            'konsulat' => '',
            'email' => '',
            'no_telp' => '',
        ];

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            if ($this->guruService->createGuru($data, $errors)) {
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('guru/index'));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/views/create', [
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $kdg = (int) $this->currentRoute->getArgument('kdg');
        $guru = $this->guruService->getGuruById($kdg);

        if ($guru === null) {
            return $this->responseFactory->createResponse(404);
        }

        $errors = [];
        $data = [
            'stambuk' => $guru->stambuk,
            'nama' => $guru->nama,
            'daerah' => $guru->daerah,
            'konsulat' => $guru->konsulat,
            'email' => $guru->email,
            'no_telp' => $guru->noTelp,
        ];

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            if ($this->guruService->updateGuru($kdg, $data, $errors)) {
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('guru/index'));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/views/update', [
            'guru' => $guru,
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    public function delete(): ResponseInterface
    {
        $kdg = (int) $this->currentRoute->getArgument('kdg');
        $this->guruService->deleteGuru($kdg);

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('guru/index'));
    }
}
