<?php

declare(strict_types=1);

namespace App\Shared\Service;

use Throwable;

use function bin2hex;
use function curl_close;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt_array;
use function error_log;
use function hash_hmac;
use function http_build_query;
use function implode;
use function is_array;
use function json_decode;
use function ltrim;
use function random_bytes;
use function rtrim;
use function time;

use const CURLINFO_HTTP_CODE;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_TIMEOUT;

/**
 * SidasApiClient
 *
 * HTTP Client untuk berkomunikasi dengan SIDAS API (sidas-yii3) menggunakan
 * autentikasi berbasis HMAC-SHA256 signature di request headers.
 */
final class SidasApiClient
{
    private string $baseUrl;

    public function __construct(
        string $baseUrl,
        private string $secret,
        private string $appId = 'kutubia-yii3',
        private int $timeout = 5
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Mencari data santri berdasarkan nomor stambuk lengkap.
     * Endpoint SIDAS: GET /api/santri/by-stambuk?stambuk={stambuk}
     *
     * @return array<string, mixed>|null Data santri atau null jika tidak ditemukan
     */
    public function findByStambuk(string $stambuk): ?array
    {
        $stambuk = trim($stambuk);
        if ($stambuk === '') {
            return null;
        }

        $r = $this->get('/api/santri/by-stambuk', ['stambuk' => $stambuk]);
        return ($r['success'] ?? false) && is_array($r['data'] ?? null) ? $r['data'] : null;
    }

    /**
     * Mencari santri dengan pencarian fuzzy / keyword nama atau potongan stambuk.
     * Endpoint SIDAS: GET /api/santri/search?q={keyword}&limit={limit}
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $q, int $limit = 10): array
    {
        $q = trim($q);
        if ($q === '') {
            return [];
        }

        $r = $this->get('/api/santri/search', ['q' => $q, 'limit' => $limit]);
        return is_array($r['data'] ?? null) ? $r['data'] : [];
    }

    /**
     * Mengambil daftar santri aktif berhalaman untuk sync batch / cache lokal.
     * Endpoint SIDAS: GET /api/santri/list-active?page={page}&per_page={perPage}
     *
     * @return array{
     *     success?: bool,
     *     data?: array<int, array<string, mixed>>,
     *     pagination?: array{
     *         total?: int,
     *         page?: int,
     *         per_page?: int,
     *         total_pages?: int
     *     }
     * }
     */
    public function listActive(int $page = 1, int $perPage = 100): array
    {
        return $this->get('/api/santri/list-active', [
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    /**
     * Mengirim signed HTTP GET request ke SIDAS API.
     *
     * Format HMAC Signature:
     *   hash_hmac('sha256', "GET|{endpoint_path_without_leading_slash}||{timestamp}|{nonce}", $secret)
     */
    private function get(string $endpoint, array $params = []): array
    {
        if ($this->secret === '') {
            error_log('[SidasApiClient] Error: SIDAS_SECRET belum dikonfigurasi.');
            return [];
        }

        try {
            $ts = time();
            $nonce = bin2hex(random_bytes(8));

            // Tanda tangani path endpoint saja (contoh: 'api/santri/by-stambuk', tanpa /sidas dan tanpa query string)
            $signPath = ltrim($endpoint, '/');
            $sigPayload = implode('|', ['GET', $signPath, '', $ts, $nonce]);
            $sig = hash_hmac('sha256', $sigPayload, $this->secret);

            $url = $this->baseUrl . $endpoint;
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }

            $ch = curl_init($url);
            if ($ch === false) {
                return [];
            }

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_CONNECTTIMEOUT => min(3, $this->timeout),
                CURLOPT_HTTPHEADER => [
                    "X-Signature: {$sig}",
                    "X-Timestamp: {$ts}",
                    "X-Nonce: {$nonce}",
                    "X-App-ID: {$this->appId}",
                    'Accept: application/json',
                ],
            ]);

            $raw = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($raw === false) {
                error_log("[SidasApiClient] cURL error pada {$endpoint}: {$err}");
                return [];
            }

            if ($code !== 200 && $code !== 404) {
                error_log("[SidasApiClient] HTTP {$code} diterima dari {$endpoint}");
                return [];
            }

            $decoded = json_decode((string) $raw, true);
            return is_array($decoded) ? $decoded : [];
        } catch (Throwable $e) {
            error_log("[SidasApiClient] Exception pada {$endpoint}: " . $e->getMessage());
            return [];
        }
    }
}
