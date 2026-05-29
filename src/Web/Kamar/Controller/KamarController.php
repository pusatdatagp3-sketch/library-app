<?php

declare(strict_types=1);

namespace App\Web\Kamar\Controller;

use App\Web\Kamar\Model\Kamar;
use App\Web\Kamar\Model\KamarDto;
use App\Web\Kamar\Service\KamarService;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class KamarController
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private KamarService $kamarService,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute,
        private FlashInterface $flash
    ) {
    }

    public function index(): ResponseInterface
    {
        $kamarList = $this->kamarService->getAllKamar();
        return $this->viewRenderer->render(__DIR__ . '/../View/index', [
            'kamarList' => $kamarList,
            'successMsg' => $this->flash->get('success'),
            'errorMsgs' => $this->flash->get('errors') ?? [],
        ]);
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $errors = [];
        $data = [
            'nama_kamar' => '',
            'kapasitas' => '',
        ];

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            if ($this->kamarService->createKamar($data, $errors)) {
                $this->flash->set('success', 'Kamar berhasil ditambahkan.');
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('kamar/index'));
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
        $kamar = $this->kamarService->getKamarById($id);

        if ($kamar === null) {
            return $this->responseFactory->createResponse(404);
        }

        $errors = [];
        $data = [
            'nama_kamar' => $kamar->namaKamar,
            'kapasitas' => (string) $kamar->kapasitas,
        ];

        if ($request->getMethod() === 'POST') {
            $data = (array) $request->getParsedBody();
            if ($this->kamarService->updateKamar($id, $data, $errors)) {
                $this->flash->set('success', 'Kamar berhasil diperbarui.');
                return $this->responseFactory->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('kamar/index'));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/update', [
            'kamar' => $kamar,
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    public function delete(): ResponseInterface
    {
        $id = (int) $this->currentRoute->getArgument('id');
        try {
            $this->kamarService->deleteKamar($id);
            $this->flash->set('success', 'Kamar berhasil dihapus.');
        } catch (\Throwable $e) {
            $this->flash->set('errors', ['Gagal menghapus kamar. Kamar mungkin sedang digunakan oleh salah satu guru.']);
        }

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('kamar/index'));
    }
}
