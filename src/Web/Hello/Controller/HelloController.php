<?php

declare(strict_types=1);

namespace App\Web\Hello\Controller;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class HelloController
{
    public function __construct(
        private WebViewRenderer $viewRenderer
    ) {
    }

    public function index(): ResponseInterface
    {
        return $this->viewRenderer->render(__DIR__ . '/../View/index', [
            'message' => 'Halo dari Yii3'
        ]);
    }
}