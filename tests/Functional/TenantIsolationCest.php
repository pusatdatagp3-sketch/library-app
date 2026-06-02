<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Support\FunctionalTester;
use HttpSoft\Message\ServerRequest;
use App\Web\Entitas\Model\Entitas;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\EntityManagerInterface;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertStringNotContainsString;

final class TenantIsolationCest
{
    public function testTenantIsolation(FunctionalTester $tester): void
    {
        $savePath = dirname(__DIR__, 2) . '/runtime/sessions';
        if (!is_dir($savePath)) {
            mkdir($savePath, 0777, true);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        session_name('TEQIC_SESSID');
        session_start([
            'cookie_secure' => 0,
            'save_path' => $savePath,
            'cookie_lifetime' => 3600,
            'gc_maxlifetime' => 3600,
        ]);

        // Set session variables for tenant G1
        $_SESSION['user_auth_id'] = 1;
        $_SESSION['user_auth_username'] = 'admin';
        $_SESSION['user_auth_role'] = 'Admin';
        $_SESSION['user_auth_allowed_campuses'] = ['G1', 'G2'];
        $_SESSION['user_auth_active_campus_code'] = 'G1';
        $sessionId = session_id();
        $sessionName = session_name();
        session_write_close();
        
        $_COOKIE[$sessionName] = $sessionId;

        // Get container to seed test data
        $runner = new \Yiisoft\Yii\Runner\Http\HttpApplicationRunner(
            rootPath: dirname(__DIR__, 2),
            environment: \App\Environment::appEnv(),
        );
        $container = $runner->getContainer();
        $orm = $container->get(ORMInterface::class);
        $em = $container->get(EntityManagerInterface::class);

        // Create one test Entitas for G1 and one for G2
        $entitasG1 = new Entitas();
        $entitasG1->nama = 'Entitas G1 Test';
        $entitasG1->modulId = 1;
        $entitasG1->kodeKampus = 'G1';
        $em->persist($entitasG1);

        $entitasG2 = new Entitas();
        $entitasG2->nama = 'Entitas G2 Test';
        $entitasG2->modulId = 1;
        $entitasG2->kodeKampus = 'G2';
        $em->persist($entitasG2);
        
        $em->run();

        // 1. Request with G1 active
        $sessionFile = $savePath . '/sess_' . $sessionId;
        file_put_contents(
            dirname(__DIR__, 2) . '/runtime/test_debug.log',
            sprintf(
                "Session ID: %s\nSession Name: %s\nSession File: %s\nFile Exists: %s\nFile Content: %s\n\$_COOKIE: %s\nRequest Cookies: %s\n",
                $sessionId,
                $sessionName,
                $sessionFile,
                file_exists($sessionFile) ? 'YES' : 'NO',
                file_exists($sessionFile) ? file_get_contents($sessionFile) : '',
                json_encode($_COOKIE),
                json_encode([$sessionName => $sessionId])
            )
        );

        $requestG1 = new ServerRequest(
            method: 'GET',
            cookieParams: [$sessionName => $sessionId],
            uri: '/fungsionaris'
        );
        $responseG1 = $tester->sendRequest($requestG1);
        assertSame(200, $responseG1->getStatusCode(), 'Redirected to: ' . ($responseG1->getHeaderLine('Location') ?: 'none'));
        $bodyG1 = $responseG1->getBody()->getContents();
        assertStringContainsString('Entitas G1 Test', $bodyG1);
        assertStringNotContainsString('Entitas G2 Test', $bodyG1);

        // 2. Select G2 campus
        $requestSelect = new ServerRequest(
            method: 'POST',
            cookieParams: [$sessionName => $sessionId],
            queryParams: [],
            parsedBody: ['campus_code' => 'G2'],
            uri: '/select-campus'
        );
        $responseSelect = $tester->sendRequest($requestSelect);
        assertSame(302, $responseSelect->getStatusCode());

        // 3. Request with G2 active (which is updated in session)
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        session_name('TEQIC_SESSID');
        session_start([
            'cookie_secure' => 0,
            'save_path' => $savePath,
            'cookie_lifetime' => 3600,
            'gc_maxlifetime' => 3600,
        ]);
        $_SESSION['user_auth_active_campus_code'] = 'G2';
        session_write_close();

        $requestG2 = new ServerRequest(
            method: 'GET',
            cookieParams: [$sessionName => $sessionId],
            uri: '/fungsionaris'
        );
        $responseG2 = $tester->sendRequest($requestG2);
        assertSame(200, $responseG2->getStatusCode());
        $bodyG2 = $responseG2->getBody()->getContents();
        assertStringContainsString('Entitas G2 Test', $bodyG2);
        assertStringNotContainsString('Entitas G1 Test', $bodyG2);

        // Clean up test data
        $em->delete($entitasG1);
        $em->delete($entitasG2);
        $em->run();
    }
}
