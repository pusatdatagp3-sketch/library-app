<?php

declare(strict_types=1);

namespace App\Web\Perpustakaan\Controller;

use App\Web\Auth\Model\AuthRepository;
use App\Web\Auth\Model\UserSession;
use App\Web\Perpustakaan\Model\KunjunganRepository;
use App\Web\Perpustakaan\Model\SiswaRepository;
use App\Web\Staf\Model\StafRepository;
use HttpSoft\Message\Stream;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;
use Yiisoft\Router\CurrentRoute;
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
        private UrlGeneratorInterface $urlGenerator,
        private ?AuthRepository $authRepository = null,
        private ?StafRepository $stafRepository = null
    ) {
    }

    /**
     * Halaman Dashboard Perpustakaan:
     * Menyajikan data statistik agregat kunjungan hari ini, minggu ini,
     * serta 3 set data agregasi (Kelas, Rayon, Konsulat) untuk visualisasi Multi-Chart (Chart.js).
     */
    public function dashboard(): ResponseInterface
    {
        $kunjunganHariIni = $this->kunjunganRepository->getKunjunganHariIni();
        $kunjunganMingguIni = $this->kunjunganRepository->getKunjunganMingguIni();

        $totalHariIni = count($kunjunganHariIni);
        $totalMingguIni = count($kunjunganMingguIni);

        // 3 Query agregasi terpisah: Kelas, Rayon, dan Konsulat
        $rawKelas = $this->kunjunganRepository->getRekapAgregasi('kelas');
        $rawRayon = $this->kunjunganRepository->getRekapAgregasi('rayon');
        $rawKonsulat = $this->kunjunganRepository->getRekapAgregasi('konsulat');

        $chartKelas = [
            'labels' => array_column($rawKelas, 'nama'),
            'totals' => array_column($rawKelas, 'total'),
        ];
        $chartRayon = [
            'labels' => array_column($rawRayon, 'nama'),
            'totals' => array_column($rawRayon, 'total'),
        ];
        $chartKonsulat = [
            'labels' => array_column($rawKonsulat, 'nama'),
            'totals' => array_column($rawKonsulat, 'total'),
        ];

        // Santri terakhir berkunjung hari ini
        $kunjunganTerbaru = array_slice($kunjunganHariIni, 0, 10);

        return $this->viewRenderer->render(__DIR__ . '/../View/dashboard', [
            'totalHariIni' => $totalHariIni,
            'totalMingguIni' => $totalMingguIni,
            'chartKelas' => $chartKelas,
            'chartRayon' => $chartRayon,
            'chartKonsulat' => $chartKonsulat,
            'chartPerKelasMingguIni' => (!empty($chartKelas['labels']) && count($chartKelas['labels']) === count($chartKelas['totals']))
                ? array_combine($chartKelas['labels'], $chartKelas['totals'])
                : [],
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

        // Ambil daftar staf aktif dikelompokkan berdasarkan divisi ('Library' & 'Staff')
        if ($this->stafRepository !== null) {
            $groupedStaff = $this->stafRepository->findActiveGroupedByDivisi();
        } else {
            $db = $this->kunjunganRepository->getDatabase();
            $rows = $db->query("SELECT * FROM `list_staf` WHERE `is_active` = 1 ORDER BY `divisi` ASC, `nama_staf` ASC")->fetchAll();
            $groupedStaff = ['Library' => [], 'Staff' => []];
            foreach ($rows as $row) {
                $div = ($row['divisi'] ?? '') === 'Staff' ? 'Staff' : 'Library';
                $groupedStaff[$div][] = $row;
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/scan', [
            'kunjunganHariIni' => $kunjunganHariIni,
            'groupedStaff'     => $groupedStaff,
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

            // 2. Tentukan nama penginput: utamakan dari parameter POST petugas piket, fallback ke user session
            $petugasPiket = trim((string) ($body['petugas_piket'] ?? ''));
            $penginput = $petugasPiket !== '' ? $petugasPiket : ($this->userSession->getUsername() ?? 'Petugas Perpustakaan');

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
     * Halaman Rekap Kunjungan:
     * Mendukung 4 mode tampilan (Nav Pills):
     * - 'input'    : Riwayat data mentah kunjungan (urutkan waktu_kunjungan DESC).
     * - 'kelas'    : Agregasi per kelas (GROUP BY kelas ORDER BY kelas ASC).
     * - 'rayon'    : Agregasi per rayon (GROUP BY rayon ORDER BY rayon ASC).
     * - 'konsulat' : Agregasi per konsulat (GROUP BY konsulat ORDER BY konsulat ASC).
     */
    public function rekap(CurrentRoute $route, Request $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $mode = (string) ($queryParams['mode'] ?? 'input');
        if (!in_array($mode, ['input', 'kelas', 'rayon', 'konsulat'], true)) {
            $mode = 'input';
        }

        $limit = isset($queryParams['limit']) ? (int) $queryParams['limit'] : 100;
        if ($limit <= 0 || $limit > 500) {
            $limit = 100;
        }

        if ($mode === 'input') {
            $data = $this->kunjunganRepository->getRiwayatKunjungan($limit);
        } else {
            $data = $this->kunjunganRepository->getRekapAgregasi($mode);
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/rekap', [
            'mode' => $mode,
            'data' => $data,
            'kunjunganList' => $mode === 'input' ? $data : [],
            'limit' => $limit,
            'route' => $route,
        ]);
    }

    /**
     * Endpoint API AJAX: Detail kunjungan santri untuk fitur Drill-Down Chart.
     * Mengembalikan daftar santri yang berkunjung berdasarkan kategori ('kelas', 'rayon', 'konsulat') dan nilainya.
     */
    public function apiDetail(Request $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $type = strtolower(trim((string) ($queryParams['type'] ?? '')));
        $value = trim((string) ($queryParams['value'] ?? ''));

        if (!in_array($type, ['kelas', 'rayon', 'konsulat'], true) || $value === '') {
            return $this->json([
                'success' => false,
                'message' => 'Parameter "type" (kelas/rayon/konsulat) dan "value" wajib diisi.',
                'data' => [],
            ], 400);
        }

        $records = $this->kunjunganRepository->getDetailKunjunganBy($type, $value);

        $data = array_map(static function (array $row) {
            $waktu = $row['waktu_kunjungan'] ?? null;
            if ($waktu instanceof \DateTimeInterface) {
                $waktuFormatted = $waktu->format('d M Y, H:i');
            } elseif (is_string($waktu) && $waktu !== '') {
                $time = strtotime($waktu);
                $waktuFormatted = $time ? date('d M Y, H:i', $time) : $waktu;
            } else {
                $waktuFormatted = '-';
            }

            return [
                'id'              => (int) ($row['id'] ?? 0),
                'stambuk'         => (string) ($row['stambuk'] ?? '-'),
                'nama_santri'     => (string) ($row['nama_santri'] ?? '-'),
                'kelas'           => (string) ($row['kelas'] ?? '-'),
                'rayon'           => (string) ($row['rayon'] ?? '-'),
                'konsulat'        => (string) ($row['konsulat'] ?? '-'),
                'waktu_kunjungan' => $waktuFormatted,
                'penginput'       => (string) ($row['penginput'] ?? '-'),
                'petugas'         => (string) ($row['penginput'] ?? '-'),
            ];
        }, $records);

        return $this->json([
            'success' => true,
            'type'    => $type,
            'value'   => $value,
            'total'   => count($data),
            'data'    => $data,
        ]);
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
