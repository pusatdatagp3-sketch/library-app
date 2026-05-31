<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Support\FunctionalTester;
use HttpSoft\Message\ServerRequest;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;

final class HomePageCest
{
    public function base(FunctionalTester $tester): void
    {
        $savePath = dirname(__DIR__, 2) . '/runtime/sessions';
        if (!is_dir($savePath)) {
            mkdir($savePath, 0777, true);
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_secure' => 0,
                'save_path' => $savePath,
                'cookie_lifetime' => 3600,
                'gc_maxlifetime' => 3600,
            ]);
        }
        $_SESSION['user_auth_id'] = 1;
        $_SESSION['user_auth_username'] = 'admin';
        $_SESSION['user_auth_role'] = 'admin';
        $sessionId = session_id();
        $sessionName = session_name();
        session_write_close();
        
        $_COOKIE[$sessionName] = $sessionId;
        
        $request = new ServerRequest(
            cookieParams: [$sessionName => $sessionId],
            uri: '/'
        );
        $response = $tester->sendRequest($request);

        assertSame(200, $response->getStatusCode());
        assertStringContainsString(
            'Selamat Datang di TEQIC',
            $response->getBody()->getContents(),
        );
    }
}
