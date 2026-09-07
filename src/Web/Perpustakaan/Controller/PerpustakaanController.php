<?php

declare(strict_types=1);

namespace App\Web\Perpustakaan\Controller;

use App\Web\Auth\Model\UserSession;
use App\Web\Perpustakaan\Model\KunjunganRepository;
use App\Web\Perpustakaan\Model\SiswaRepository;
use HttpSoft\Message\Stream;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Controller untuk Modul Perpustakaan (Tahap 1: Dashboard & Barcode Scan).
 * Menggunakan Constructor Property Promotion dan Yii3 WebViewRenderer.
 */
final class PerpustakaanController
{
    public function __construct(
        private KunjunganRepository $kunjunganRepository,
        private WebViewRenderer $viewRenderer,
        private SiswaRepository $siswaRepository,
        private ResponseFactoryInterface $responseFactory,
        private UserSession $userSession,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * Halaman Dashboard Perpustakaan:
     * Menyajikan data statistik agregat kunjungan hari ini, minggu ini,
     * serta rekapitulasi kunjungan per kelas untuk divisualisasikan via Chart.js.
     */
    public function dashboard(): ResponseInterface
    {
        $kunjunganHariIni = $this->kunjunganRepository->getKunjunganHariIni();
        $kunjunganMingguIni = $this->kunjunganRepository->getKunjunganMingguIni();

        $totalHariIni = count($kunjunganHariIni);
        $totalMingguIni = count($kunjunganMingguIni);

        // Agregasi kunjungan per kelas untuk chart
        $chartPerKelasMingguIni = $this->kunjunganRepository->getRekapPerKelas('minggu_ini');
        $chartPerKelasHariIni = $this->kunjunganRepository->getRekapPerKelas('hari_ini');

        // Santri terakhir berkunjung hari ini
        $kunjunganTerbaru = array_slice($kunjunganHariIni, 0, 10);

        return $this->viewRenderer->render(__DIR__ . '/../View/dashboard', [
            'totalHariIni' => $totalHariIni,
            'totalMingguIni' => $totalMingguIni,
            'chartPerKelasMingguIni' => $chartPerKelasMingguIni,
            'chartPerKelasHariIni' => $chartPerKelasHariIni,
            'kunjunganTerbaru' => $kunjunganTerbaru,
        ]);
    }

    /**
     * Fitur Scan Barcode Kunjungan:
     * - GET: Menampilkan halaman scan dengan input autofocus untuk USB Barcode Scanner.
     * - POST: Menerima stambuk santri via AJAX, mencari data santri di SiswaRepository,
     *         mencatat kunjungan ke KunjunganRepository, dan mengembalikan respon JSON.
     */
    public function scan(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            return $this->handleScanPost($request);
        }

        // GET: Tampilkan halaman form scanner
        $kunjunganHariIni = $this->kunjunganRepository->getKunjunganHariIni();

        return $this->viewRenderer->render(__DIR__ . '/../View/scan', [
            'kunjunganHariIni' => $kunjunganHariIni,
        ]);
    }

    /**
     * Memproses request POST scan barcode secara asinkron (AJAX).
     */
    private function handleScanPost(ServerRequestInterface $request): ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $stambuk = trim((string) ($body['stambuk'] ?? ''));

        if ($stambuk === '') {
            return $this->json([
                'success' => false,
                'message' => 'Nomor stambuk barcode tidak boleh kosong.',
            ], 400);
        }

        try {
            // 1. Cari data santri secara Read-Only via SiswaRepository
            $santri = $this->siswaRepository->findByStambuk($stambuk);

            if ($santri === null) {
                return $this->json([
                    'success' => false,
                    'message' => 'Data santri dengan stambuk "' . htmlspecialchars($stambuk) . '" tidak ditemukan.',
                ], 404);
            }

            // 2. Tentukan nama penginput dari user yang sedang login
            $penginput = $this->userSession->getUsername() ?? 'Petugas Perpustakaan';

            // 3. Catat kunjungan ke tabel record_perpustakaan_kunjungan
            $santriId = (int) ($santri['santri_id'] ?? $santri['kds'] ?? 0);
            $catatSukses = $this->kunjunganRepository->catatKunjungan($santriId, [
                'stambuk' => (string) $santri['stambuk'],
                'nama_santri' => (string) ($santri['nama'] ?? $santri['nama_santri'] ?? ''),
                'kelas' => (string) ($santri['kelas'] ?? ''),
                'rayon' => (string) ($santri['rayon'] ?? ''),
                'konsulat' => (string) ($santri['konsulat'] ?? ''),
                'penginput' => $penginput,
            ]);

            if (!$catatSukses) {
                return $this->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan transaksi kunjungan ke database.',
                ], 500);
            }

            // 4. Return respon sukses JSON untuk AJAX frontend
            return $this->json([
                'success' => true,
                'message' => 'Kunjungan berhasil dicatat atas nama ' . $santri['nama'] . ' (Kelas ' . $santri['kelas'] . ').',
                'data' => [
                    'santri_id' => $santriId,
                    'stambuk' => $santri['stambuk'],
                    'nama' => $santri['nama'],
                    'kelas' => $santri['kelas'],
                    'rayon' => $santri['rayon'],
                    'konsulat' => $santri['konsulat'],
                    'waktu' => date('H:i:s'),
                    'penginput' => $penginput,
                ],
            ]);
        } catch (Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper untuk menghasilkan respon JSON yang bersih dan sesuai standar PSR-7.
     */
    private function json(array $payload, int $statusCode = 200): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($statusCode)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8');

        $stream = new Stream();
        $stream->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $response->withBody($stream);
    }
}
