<?php

declare(strict_types=1);

namespace App\Web\HomePage;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use App\Web\Auth\UserSession;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private UserSession $userSession,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
    ) {}

    public function __invoke(): ResponseInterface
    {
        // Jika belum login, redirect ke halaman login
        if (!$this->userSession->isLoggedIn()) {
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('login'));
        }
        
        return $this->viewRenderer->render(__DIR__ . '/template');
    }
}
