<?php

declare(strict_types=1);

namespace App\Web\Auth\Controller;

use App\Environment;
use App\Web\Auth\Model\AuthRepository;
use App\Web\Auth\Model\UserSession;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\Session\SessionInterface;

final class SsoController
{
    public function __construct(
        private AuthRepository $authRepository,
        private UserSession $userSession,
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private FlashInterface $flash
    ) {
    }

    public function redirect(SessionInterface $session): ResponseInterface
    {
        $state = bin2hex(random_bytes(16));
        $session->set('sso_state', $state);

        // Generate PKCE code verifier and challenge
        $codeVerifier = bin2hex(random_bytes(32));
        $session->set('sso_code_verifier', $codeVerifier);
        
        $hashed = hash('sha256', $codeVerifier, true);
        $codeChallenge = rtrim(strtr(base64_encode($hashed), '+/', '-_'), '=');

        $authorizeUrl = Environment::hawiSsoUrl() . '/oauth/authorize' . '?' . http_build_query([
            'response_type' => 'code',
            'client_id' => Environment::hawiSsoClientId(),
            'redirect_uri' => Environment::hawiSsoRedirectUri(),
            'scope' => 'openid profile email',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $authorizeUrl);
    }

    public function callback(ServerRequestInterface $request, SessionInterface $session): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $code = $queryParams['code'] ?? null;
        $state = $queryParams['state'] ?? null;

        if (empty($code)) {
            $this->flash->set('errors', ['Authorization code tidak ditemukan.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('login'));
        }

        // Validate CSRF state
        $savedState = $session->get('sso_state');
        if (empty($state) || $state !== $savedState) {
            $this->flash->set('errors', ['State parameter tidak valid (CSRF detected).']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('login'));
        }

        $codeVerifier = $session->get('sso_code_verifier');

        // Exchange Authorization Code for Token (Backend-to-Backend)
        $tokenUrl = Environment::hawiSsoUrl() . '/oauth/token';
        $postData = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => Environment::hawiSsoClientId(),
            'client_secret' => Environment::hawiSsoClientSecret(),
            'redirect_uri' => Environment::hawiSsoRedirectUri(),
        ];
        if (!empty($codeVerifier)) {
            $postData['code_verifier'] = $codeVerifier;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $responseStr = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $errData = json_decode((string)$responseStr, true);
            $errMsg = $errData['error_description'] ?? $errData['error'] ?? 'Terjadi kesalahan saat menghubungi server SSO.';
            $this->flash->set('errors', ['SSO Token Error: ' . $errMsg]);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('login'));
        }

        $tokenData = json_decode((string)$responseStr, true);
        $accessToken = $tokenData['access_token'] ?? null;

        if (empty($accessToken)) {
            $this->flash->set('errors', ['Access token tidak ditemukan dalam respon SSO.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('login'));
        }

        // Fetch user profile info using the access token
        $userInfoUrl = Environment::hawiSsoUrl() . '/oauth/userinfo';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $userInfoStr = curl_exec($ch);
        $userInfoHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($userInfoHttpCode !== 200) {
            $this->flash->set('errors', ['Gagal mengambil profil user dari SSO.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('login'));
        }

        $userInfo = json_decode((string)$userInfoStr, true);
        $email = $userInfo['email'] ?? null;
        $username = $userInfo['preferred_username'] ?? null;
        $bagian = $userInfo['bagian'] ?? null;

        if (empty($email)) {
            $this->flash->set('errors', ['Email pengguna tidak ditemukan dalam klaim SSO.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('login'));
        }

        // Map SSO user role to TEQIC user role
        $mappedRole = 'Guru';
        $normalizedBagian = strtolower((string)$bagian);
        if (str_contains($normalizedBagian, 'admin')) {
            $mappedRole = 'Admin';
        } elseif (str_contains($normalizedBagian, 'operator')) {
            $mappedRole = 'Operator';
        }

        // Check if user exists in TEQIC database
        $user = $this->authRepository->findByEmail($email);
        if ($user === null && !empty($username)) {
            $user = $this->authRepository->findByUsername($username);
        }

        if ($user === null) {
            $this->flash->set('errors', ['Akun Anda (' . htmlspecialchars($email) . ') belum terdaftar di sistem TEQIC. Silakan hubungi Administrator.']);
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->urlGenerator->generate('login'));
        }

        // Authenticate the user session locally
        $this->userSession->login($user);
        $this->flash->set('success', 'Selamat datang! Anda masuk menggunakan SSO Hawi.');

        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('home'));
    }

    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        $this->userSession->logout();

        $params = $request->getQueryParams();
        $redirectUrl = $params['post_logout_redirect_uri'] ?? null;

        if ($redirectUrl !== null) {
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', (string)$redirectUrl);
        }

        $response = $this->responseFactory->createResponse(200);
        $response->getBody()->write('OK');
        return $response;
    }
}
