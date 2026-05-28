<?php

declare(strict_types=1);

namespace App\Web\Auth\Controller;

use App\Web\Auth\Model\AuthRepository;
use App\Web\Auth\Model\UserSession;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use Yiisoft\Session\Flash\FlashInterface;

final class AuthController
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private AuthRepository $authRepository,
        private UserSession $userSession,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private FlashInterface $flash
    ) {
    }

    public function login(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->userSession->isLoggedIn()) {
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('guru/index'));
        }

        $error = null;
        $username = '';

        // Ambil dan hapus flash errors dari session agar tidak bocor ke halaman berikutnya
        $flashErrors = $this->flash->get('errors');
        if (!empty($flashErrors) && $request->getMethod() !== 'POST') {
            $error = is_array($flashErrors) ? implode(', ', $flashErrors) : (string) $flashErrors;
        }

        if ($request->getMethod() === 'POST') {
            $body = (array) $request->getParsedBody();
            $username = trim((string) ($body['username'] ?? ''));
            $password = (string) ($body['password'] ?? '');

            if ($username === '' || $password === '') {
                $error = 'Username dan password wajib diisi.';
            } else {
                $user = $this->authRepository->findByUsername($username);
                if ($user !== null && password_verify($password, $user['password_hash'])) {
                    $this->userSession->login($user);
                    $this->flash->remove('errors'); // Pastikan flash errors benar-benar bersih
                    $this->flash->set('success', 'Selamat datang kembali, ' . HtmlHelper::encode($user['username']) . '!');
                    return $this->responseFactory->createResponse(302)
                        ->withHeader('Location', $this->urlGenerator->generate('guru/index'));
                } else {
                    $error = 'Username atau password salah.';
                }
            }
        }

        return $this->viewRenderer
           ->withLayout('@src/Web/Shared/Layout/Login/login-layout.php')
           ->render(__DIR__ . '/../View/login', [
            'error' => $error,
            'username' => $username,
        ]);
    }

    public function logout(): ResponseInterface
    {
        $this->userSession->logout();
        $this->flash->set('success', 'Anda telah berhasil keluar dari sistem.');
        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('login'));
    }
}

// Inline helper to prevent importing too many libraries in controller
class HtmlHelper {
    public static function encode(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
