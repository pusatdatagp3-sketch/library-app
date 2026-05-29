<?php

declare(strict_types=1);

namespace App\Web\Perizinan\Controller;

use App\Web\Perizinan\Model\Perizinan;
use App\Web\Perizinan\Service\PerizinanService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class PerizinanController
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private PerizinanService $perizinanService,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash
    ) {
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $perizinanList = $this->perizinanService->getAllPerizinan($queryParams);
        return $this->viewRenderer->render(__DIR__ . '/../View/index', [
            'perizinanList' => $perizinanList,
            'filters' => $queryParams,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs' => $this->flash->get('errors') ?? [],
        ]);
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $errors = [];
        $data = [
            'kemana' => '',
            'sama_siapa' => '',
            'berapa_orang' => '',
        ];

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            if ($this->perizinanService->createPerizinan($data, $errors)) {
                $this->flash->set('success', 'Data Perizinan berhasil ditambahkan.');
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('perizinan/index'));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/create', [
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        $perizinan = $this->perizinanService->getPerizinanById($id);

        if ($perizinan === null) {
            return $this->responseFactory->createResponse(404);
        }

        $errors = [];
        $data = [
            'kemana' => (string) $perizinan->kemana,
            'sama_siapa' => (string) $perizinan->samaSiapa,
            'berapa_orang' => (string) $perizinan->berapaOrang,
        ];

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            if ($this->perizinanService->updatePerizinan($id, $data, $errors)) {
                $this->flash->set('success', 'Data Perizinan berhasil diperbarui.');
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('perizinan/index'));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/update', [
            'perizinan' => $perizinan,
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    public function delete(): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        try {
            $this->perizinanService->deletePerizinan($id);
            $this->flash->set('success', 'Data Perizinan berhasil dihapus.');
        } catch (\Throwable $e) {
            $this->flash->set('errors', ['Gagal menghapus data: ' . $e->getMessage()]);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('perizinan/index'));
    }
}
