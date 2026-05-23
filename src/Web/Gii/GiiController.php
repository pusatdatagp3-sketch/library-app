<?php

declare(strict_types=1);

namespace App\Web\Gii;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class GiiController
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private FlashInterface $flash,
        private GiiService $giiService
    ) {
    }

    public function index(): ResponseInterface
    {
        $tables = $this->giiService->getTables();
        return $this->viewRenderer->render(__DIR__ . '/views/index', [
            'tables' => $tables,
            'successMsg' => $this->flash->get('success'),
            'logs' => $this->flash->get('logs') ?? [],
            'errorMsg' => $this->flash->get('error'),
        ]);
    }

    public function generate(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return $this->responseFactory->createResponse(405);
        }

        $data = (array) $request->getParsedBody();
        $table = trim((string)($data['table'] ?? ''));
        $modelName = trim((string)($data['modelName'] ?? ''));

        if ($table === '' || $modelName === '') {
            $this->flash->set('error', 'Semua kolom input wajib diisi.');
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('gii/index'));
        }

        $logs = [];
        $success = $this->giiService->generate($table, $modelName, $logs);

        if ($success) {
            $this->flash->set('success', 'Modul ' . $modelName . ' berhasil di-generate secara otomatis!');
            $this->flash->set('logs', $logs);
        } else {
            $this->flash->set('error', 'Gagal men-generate modul.');
            $this->flash->set('logs', $logs);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('gii/index'));
    }
}
