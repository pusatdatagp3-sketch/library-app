<?php

declare(strict_types=1);

/**
 * Script untuk menguji koneksi langsung dari Kutubia ke SIDAS API.
 *
 * Cara menjalankan:
 *   php bin/test-sidas-connection.php [stambuk]
 * Contoh:
 *   php bin/test-sidas-connection.php 2.46.51125
 */

require_once __DIR__ . '/../src/bootstrap.php';

use App\Environment;
use App\Shared\Service\SidasApiClient;

$testStambuk = $argv[1] ?? '2.46.51125';

$baseUrl = Environment::sidasBaseUrl();
$appId = Environment::sidasAppId();
$secret = Environment::sidasSecret();
$timeout = Environment::sidasTimeout();

echo "=========================================================\n";
echo " KUTUBIA -> SIDAS API CONNECTION TEST\n";
echo "=========================================================\n";
echo "Base URL : {$baseUrl}\n";
echo "App ID   : {$appId}\n";
echo "Secret   : " . substr($secret, 0, 8) . "..." . substr($secret, -6) . "\n";
echo "Timeout  : {$timeout} detik\n";
echo "---------------------------------------------------------\n";

$client = new SidasApiClient($baseUrl, $secret, $appId, $timeout);

// Test 1: findByStambuk
echo "1. Menguji GET /api/santri/by-stambuk?stambuk={$testStambuk} ...\n";
$santri = $client->findByStambuk($testStambuk);
if ($santri !== null) {
    echo "   [OK] Berhasil! Data santri ditemukan:\n";
    echo "   - Nama   : " . ($santri['nama'] ?? '-') . "\n";
    echo "   - Stambuk: " . ($santri['stambuk'] ?? '-') . "\n";
    echo "   - Kelas  : " . ($santri['kelas'] ?? '-') . "\n";
    echo "   - Rayon  : " . ($santri['rayon'] ?? '-') . "\n";
    echo "   - Konsulat: " . ($santri['konsulat'] ?? '-') . "\n";
    echo "   - Aktif  : " . (!empty($santri['aktif']) ? 'Ya' : 'Tidak') . "\n";
} else {
    echo "   [FAIL/404] Santri tidak ditemukan atau koneksi gagal.\n";
}
echo "---------------------------------------------------------\n";

// Test 2: search
echo "2. Menguji GET /api/santri/search?q=ahmad&limit=2 ...\n";
$searchResults = $client->search('ahmad', 2);
echo "   [OK] Hasil pencarian: " . count($searchResults) . " santri.\n";
foreach ($searchResults as $i => $item) {
    echo "   " . ($i + 1) . ". " . ($item['nama'] ?? '-') . " (" . ($item['stambuk'] ?? '-') . " - " . ($item['kelas'] ?? '-') . ")\n";
}
echo "---------------------------------------------------------\n";

// Test 3: listActive
echo "3. Menguji GET /api/santri/list-active?page=1&per_page=2 ...\n";
$listActive = $client->listActive(1, 2);
if ($listActive['success'] ?? false) {
    $total = $listActive['pagination']['total'] ?? 0;
    $totalPages = $listActive['pagination']['total_pages'] ?? 0;
    echo "   [OK] Berhasil! Total santri aktif di SIDAS: {$total} ({$totalPages} halaman)\n";
} else {
    echo "   [FAIL] Gagal memanggil list-active.\n";
}
echo "=========================================================\n";
