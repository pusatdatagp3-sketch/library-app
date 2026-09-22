<?php

declare(strict_types=1);

namespace App\Web\Perpustakaan\Controller;

use App\Web\Auth\Model\AuthRepository;
use App\Web\Auth\Model\UserSession;
use App\Web\Perpustakaan\Model\KunjunganRepository;
use App\Web\Perpustakaan\Model\SiswaRepository;
use App\Web\Staf\Model\StafRepository;
use HttpSoft\Message\Stream;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta'));

        // 6 adalah index hari Sabtu, 5 adalah Jumat
        $startOfWeek = ($now->format('w') == 6) 
            ? $now->setTime(0, 0, 0) 
            : $now->modify('last saturday')->setTime(0, 0, 0);

        $endOfWeek = ($now->format('w') == 5) 
            ? $now->setTime(23, 59, 59) 
            : $now->modify('next friday')->setTime(23, 59, 59);

        $startStr = $startOfWeek->format('Y-m-d H:i:s');
        $endStr = $endOfWeek->format('Y-m-d H:i:s');

        $kunjunganHariIni = $this->kunjunganRepository->getKunjunganHariIni();
        $kunjunganMingguIni = $this->kunjunganRepository->getKunjunganMingguIni($startStr, $endStr);

        $totalHariIni = count($kunjunganHariIni);
        $totalMingguIni = count($kunjunganMingguIni);

        // 3 Query agregasi terpisah: Kelas, Rayon, dan Konsulat (Minggu Ini: Sabtu - Jumat)
        $rawKelas = $this->kunjunganRepository->getRekapAgregasi('kelas', $startStr, $endStr);
        $rawRayon = $this->kunjunganRepository->getRekapAgregasi('rayon', $startStr, $endStr);
        $rawKonsulat = $this->kunjunganRepository->getRekapAgregasi('konsulat', $startStr, $endStr);

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

        // Query Leaderboard: Top 3 Pengunjung Aktif Minggu Ini (Sabtu - Jumat)
        try {
            $db = $this->kunjunganRepository->getDatabase();
            $sql = "SELECT `stambuk`, `nama_santri`, `kelas`, COUNT(`id`) AS `total_kunjungan`
                    FROM `record_perpustakaan_kunjungan`
                    WHERE `waktu_kunjungan` BETWEEN :start_str AND :end_str
                    GROUP BY `stambuk`, `nama_santri`, `kelas`
                    ORDER BY `total_kunjungan` DESC
                    LIMIT 3";
            $topVisitors = $db->query($sql, [
                ':start_str' => $startStr,
                ':end_str'   => $endStr,
            ])->fetchAll();
        } catch (\Throwable) {
            $topVisitors = [];
        }

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
            'topVisitors' => $topVisitors,
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

        $currentPetugas = $this->userSession->getPetugasPiket();
        $hasStaffActive = !empty($currentPetugas);

        return $this->viewRenderer->render(__DIR__ . '/../View/scan', [
            'kunjunganHariIni' => $kunjunganHariIni,
            'groupedStaff'     => $groupedStaff,
            'currentPetugas'   => $currentPetugas,
            'hasStaffActive'   => $hasStaffActive,
        ]);
    }

    /**
     * Menetapkan petugas piket aktif ke dalam sesi user (AJAX).
     */
    public function setStaff(ServerRequestInterface $request): ResponseInterface
    {
        $body = (array) ($request->getParsedBody() ?? []);
        if (empty($body)) {
            $raw = (string) $request->getBody();
            if ($raw !== '') {
                $jsonData = json_decode($raw, true);
                if (is_array($jsonData)) {
                    $body = $jsonData;
                }
            }
        }

        $namaPetugas = trim((string) ($body['nama_petugas'] ?? ''));

        if (mb_strlen($namaPetugas) < 2) {
            return $this->json([
                'success' => false,
                'message' => 'Silakan pilih nama petugas yang valid dari daftar staf.',
            ], 400);
        }

        $this->userSession->setPetugasPiket($namaPetugas);

        return $this->json([
            'success'    => true,
            'staff_name' => $namaPetugas,
            'message'    => "Petugas piket aktif berhasil disetel: {$namaPetugas}",
        ]);
    }

    /**
     * Mereset sesi petugas piket aktif (AJAX).
     */
    public function switchStaff(ServerRequestInterface $request): ResponseInterface
    {
        $this->userSession->clearPetugasPiket();

        return $this->json([
            'success' => true,
            'message' => 'Sesi petugas piket berhasil di-reset. Silakan tentukan petugas baru.',
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

            // 2. Tentukan nama penginput: utamakan dari sesi petugas piket
            $sessionPetugas = $this->userSession->getPetugasPiket();
            $postPetugas    = trim((string) ($body['petugas_piket'] ?? ''));

            if ($sessionPetugas !== null && $sessionPetugas !== '') {
                $penginput = $sessionPetugas;
            } elseif ($postPetugas !== '') {
                $this->userSession->setPetugasPiket($postPetugas);
                $penginput = $postPetugas;
            } else {
                return $this->json([
                    'success' => false,
                    'require_staff' => true,
                    'message' => 'Petugas piket belum ditentukan. Silakan tentukan petugas yang bertugas terlebih dahulu.',
                ], 400);
            }

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
     *
     * Dilengkapi Filter Rentang Waktu (Semua Waktu, Minggu Ini Sabtu-Jumat, Bulan Ini)
     * serta Fitur Export Data (Excel .CSV & PDF via Clean Print Layout).
     */
    public function rekap(CurrentRoute $route, Request $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $mode = (string) ($queryParams['mode'] ?? 'input');
        if (!in_array($mode, ['input', 'kelas', 'rayon', 'konsulat'], true)) {
            $mode = 'input';
        }

        $rentangWaktu = (string) ($queryParams['rentang_waktu'] ?? 'semua');
        if (!in_array($rentangWaktu, ['semua', 'minggu_ini', 'bulan_ini'], true)) {
            $rentangWaktu = 'semua';
        }

        $searchQuery = trim((string) ($queryParams['q'] ?? ''));
        $action = strtolower(trim((string) ($queryParams['action'] ?? '')));
        $format = strtolower(trim((string) ($queryParams['format'] ?? '')));

        // 1. Tentukan Filter Rentang Waktu (Sabtu - Jumat untuk kalender pesantren)
        $startStr = null;
        $endStr = null;
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta'));

        if ($rentangWaktu === 'minggu_ini') {
            // 6 adalah index hari Sabtu, 5 adalah hari Jumat
            $startOfWeek = ($now->format('w') == 6) 
                ? $now->setTime(0, 0, 0) 
                : $now->modify('last saturday')->setTime(0, 0, 0);

            $endOfWeek = ($now->format('w') == 5) 
                ? $now->setTime(23, 59, 59) 
                : $now->modify('next friday')->setTime(23, 59, 59);

            $startStr = $startOfWeek->format('Y-m-d H:i:s');
            $endStr = $endOfWeek->format('Y-m-d H:i:s');
        } elseif ($rentangWaktu === 'bulan_ini') {
            $startOfMonth = $now->modify('first day of this month')->setTime(0, 0, 0);
            $endOfMonth = $now->modify('last day of this month')->setTime(23, 59, 59);

            $startStr = $startOfMonth->format('Y-m-d H:i:s');
            $endStr = $endOfMonth->format('Y-m-d H:i:s');
        }

        $limit = isset($queryParams['limit']) ? (int) $queryParams['limit'] : 100;
        if ($limit <= 0 || $limit > 500) {
            $limit = 100;
        }

        // Jika melakukan export, ambil seluruh data (maksimal 10.000) agar file tidak terpotong pagination
        $effectiveLimit = ($action === 'export') ? 10000 : $limit;

        // 2. Eksekusi query data ke repository dengan parameter waktu & pencarian
        if ($mode === 'input') {
            $data = $this->kunjunganRepository->getRiwayatKunjungan(
                $effectiveLimit,
                $startStr,
                $endStr,
                $searchQuery !== '' ? $searchQuery : null
            );
        } else {
            $data = $this->kunjunganRepository->getRekapAgregasi(
                $mode,
                $startStr,
                $endStr,
                $searchQuery !== '' ? $searchQuery : null
            );
        }

        // Tentukan label deskriptif rentang waktu
        $bulanIndo = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $namaBulan = $bulanIndo[(int)$now->format('n')];
        $tahun = $now->format('Y');

        $labelRentang = match ($rentangWaktu) {
            'minggu_ini' => 'Minggu Ini (Sabtu - Jumat)',
            'bulan_ini'  => "Bulan Ini ({$namaBulan} {$tahun})",
            default      => 'Semua Waktu',
        };

        $periodeDetail = ($startStr !== null && $endStr !== null)
            ? (new \DateTimeImmutable($startStr))->format('d M Y') . ' s.d. ' . (new \DateTimeImmutable($endStr))->format('d M Y')
            : 'Seluruh Riwayat';

        // 3. Logika Ekspor Berkas (Excel .XLSX / .CSV & PDF Standalone View)
        if ($action === 'export') {
            if ($format === 'excel' || $format === 'xlsx') {
                if (class_exists(Spreadsheet::class) && class_exists(\XMLWriter::class)) {
                    return $this->handleExportXlsx($data, $mode, $rentangWaktu, $labelRentang, $periodeDetail);
                }
                return $this->handleExportCsv($data, $mode, $rentangWaktu, $labelRentang, $periodeDetail);
            }

            if ($format === 'csv') {
                return $this->handleExportCsv($data, $mode, $rentangWaktu, $labelRentang, $periodeDetail);
            }

            if ($format === 'pdf') {
                return $this->handleExportPdf($data, $mode, $rentangWaktu, $labelRentang, $periodeDetail);
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/../View/rekap', [
            'mode'          => $mode,
            'data'          => $data,
            'kunjunganList' => $mode === 'input' ? $data : [],
            'limit'         => $limit,
            'rentangWaktu'  => $rentangWaktu,
            'searchQuery'   => $searchQuery,
            'labelRentang'  => $labelRentang,
            'periodeDetail' => $periodeDetail,
            'route'         => $route,
        ]);
    }

    /**
     * Menghasilkan file download Excel .XLSX (Microsoft Excel murni) dengan styling tabel rapi.
     */
    private function handleExportXlsx(
        array $data,
        string $mode,
        string $rentangWaktu,
        string $labelRentang,
        string $periodeDetail
    ): ResponseInterface {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Kunjungan');

        // 1. Header Judul Laporan
        $sheet->setCellValue('A1', 'KUTUBIA - SISTEM INFORMASI PERPUSTAKAAN');
        $sheet->setCellValue('A2', 'LAPORAN REKAPITULASI KUNJUNGAN SANTRI');
        $sheet->setCellValue('A3', 'Mode Rekap: ' . strtoupper($mode) . ' | Periode: ' . $labelRentang . ' (' . $periodeDetail . ')');
        $sheet->setCellValue('A4', 'Waktu Ekspor: ' . date('d-m-Y H:i:s') . ' WIB');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('4338CA');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('0F172A');
        $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true)->getColor()->setRGB('475569');
        $sheet->getStyle('A4')->getFont()->setSize(9)->getColor()->setRGB('64748B');

        $startRow = 6;

        if ($mode === 'input') {
            // Mode Detail Per-Input
            $headers = ['No', 'Waktu Kunjungan', 'No. Stambuk', 'Nama Santri', 'Kelas', 'Rayon', 'Konsulat', 'Petugas Piket'];
            $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

            foreach ($headers as $idx => $h) {
                $sheet->setCellValue($cols[$idx] . $startRow, $h);
            }

            $currentRow = $startRow + 1;
            $no = 1;
            foreach ($data as $item) {
                $waktu = $item->waktu_kunjungan ? $item->waktu_kunjungan->format('Y-m-d H:i') : '-';
                $sheet->setCellValue('A' . $currentRow, $no++);
                $sheet->setCellValue('B' . $currentRow, $waktu);
                $sheet->setCellValueExplicit('C' . $currentRow, (string)$item->stambuk, DataType::TYPE_STRING);
                $sheet->setCellValue('D' . $currentRow, (string)($item->nama_santri ?? '-'));
                $sheet->setCellValue('E' . $currentRow, (string)($item->kelas ?? '-'));
                $sheet->setCellValue('F' . $currentRow, (string)($item->rayon ?? '-'));
                $sheet->setCellValue('G' . $currentRow, (string)($item->konsulat ?? '-'));
                $sheet->setCellValue('H' . $currentRow, (string)($item->penginput ?? '-'));
                $currentRow++;
            }

            $endRow = max($startRow + 1, $currentRow - 1);
            $lastCol = 'H';

            // Alignment
            $sheet->getStyle('A' . ($startRow + 1) . ':A' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . ($startRow + 1) . ':B' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . ($startRow + 1) . ':C' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . ($startRow + 1) . ':E' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        } else {
            // Mode Agregasi (Kelas / Rayon / Konsulat)
            $colTitle = match ($mode) {
                'kelas'    => 'Nama Kelas',
                'rayon'    => 'Nama Rayon',
                'konsulat' => 'Nama Konsulat',
                default    => 'Kategori',
            };

            $headers = ['No', $colTitle, 'Jumlah Kunjungan'];
            $cols = ['A', 'B', 'C'];

            foreach ($headers as $idx => $h) {
                $sheet->setCellValue($cols[$idx] . $startRow, $h);
            }

            $currentRow = $startRow + 1;
            $no = 1;
            $grandTotal = 0;
            foreach ($data as $row) {
                $nama = (string)($row['nama'] ?? $row[$mode] ?? 'Tidak Terdata');
                $total = (int)($row['total'] ?? 0);
                $grandTotal += $total;

                $sheet->setCellValue('A' . $currentRow, $no++);
                $sheet->setCellValue('B' . $currentRow, $nama);
                $sheet->setCellValue('C' . $currentRow, $total);
                $currentRow++;
            }

            // Total baris
            $sheet->setCellValue('A' . $currentRow, '');
            $sheet->setCellValue('B' . $currentRow, 'TOTAL KESELURUHAN');
            $sheet->setCellValue('C' . $currentRow, $grandTotal);

            $sheet->getStyle('A' . $currentRow . ':C' . $currentRow)->getFont()->setBold(true);
            $sheet->getStyle('A' . $currentRow . ':C' . $currentRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('EDE9FE');

            $endRow = $currentRow;
            $lastCol = 'C';

            // Alignment
            $sheet->getStyle('A' . ($startRow + 1) . ':A' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . ($startRow + 1) . ':C' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // Style Table Header (Baris $startRow)
        $headerRange = 'A' . $startRow . ':' . $lastCol . $startRow;
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('7C3AED'); // Brand Purple KUTUBIA
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Border Table
        $tableRange = 'A' . $startRow . ':' . $lastCol . $endRow;
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('CBD5E1');

        // Auto size columns
        foreach (range('A', $lastCol) as $colID) {
            $sheet->getColumnDimension($colID)->setAutoSize(true);
        }

        // Simpan ke temp stream & kembalikan response PSR-7
        $tempStream = fopen('php://temp', 'r+');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempStream);
        rewind($tempStream);

        $bodyStream = new Stream($tempStream);

        return $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader('Content-Disposition', 'attachment; filename="Rekap_Kunjungan_KUTUBIA.xlsx"')
            ->withHeader('Cache-Control', 'max-age=0')
            ->withHeader('Pragma', 'public')
            ->withBody($bodyStream);
    }

    /**
     * Menghasilkan file download CSV Rekap Kunjungan untuk dibuka di Microsoft Excel / Google Sheets (PSR-7 Stream).
     */
    private function handleExportCsv(
        array $data,
        string $mode,
        string $rentangWaktu,
        string $labelRentang,
        string $periodeDetail
    ): ResponseInterface {
        $tempStream = fopen('php://temp', 'r+');
        // UTF-8 Byte Order Mark (BOM) agar huruf Latin / Arab terbaca akurat di Microsoft Excel
        fputs($tempStream, "\xEF\xBB\xBF");

        // Header Informasi Dokumen
        fputcsv($tempStream, ['KUTUBIA - SISTEM INFORMASI PERPUSTAKAAN']);
        fputcsv($tempStream, ['LAPORAN REKAPITULASI KUNJUNGAN SANTRI']);
        fputcsv($tempStream, ['Mode Rekap', strtoupper($mode)]);
        fputcsv($tempStream, ['Periode', $labelRentang . ' (' . $periodeDetail . ')']);
        fputcsv($tempStream, ['Waktu Ekspor', date('d-m-Y H:i:s') . ' WIB']);
        fputcsv($tempStream, []); // Baris kosong pemisah

        if ($mode === 'input') {
            // Mode Detail Per-Input
            fputcsv($tempStream, ['No', 'Waktu Kunjungan', 'No. Stambuk', 'Nama Santri', 'Kelas', 'Rayon', 'Konsulat', 'Petugas Piket']);
            $no = 1;
            foreach ($data as $item) {
                $waktu = $item->waktu_kunjungan ? $item->waktu_kunjungan->format('Y-m-d H:i:s') : '-';
                fputcsv($tempStream, [
                    $no++,
                    $waktu,
                    $item->stambuk,
                    $item->nama_santri ?? '-',
                    $item->kelas ?? '-',
                    $item->rayon ?? '-',
                    $item->konsulat ?? '-',
                    $item->penginput ?? '-',
                ]);
            }
        } else {
            // Mode Agregasi (Kelas / Rayon / Konsulat)
            $colTitle = match ($mode) {
                'kelas'    => 'Nama Kelas',
                'rayon'    => 'Nama Rayon',
                'konsulat' => 'Nama Konsulat',
                default    => 'Kategori',
            };

            fputcsv($tempStream, ['No', $colTitle, 'Jumlah Kunjungan']);
            $no = 1;
            $grandTotal = 0;
            foreach ($data as $row) {
                $nama = (string)($row['nama'] ?? $row[$mode] ?? 'Tidak Terdata');
                $total = (int)($row['total'] ?? 0);
                $grandTotal += $total;
                fputcsv($tempStream, [$no++, $nama, $total]);
            }
            fputcsv($tempStream, []);
            fputcsv($tempStream, ['', 'TOTAL KESELURUHAN', $grandTotal]);
        }

        rewind($tempStream);
        $bodyStream = new Stream($tempStream);

        return $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->withHeader('Content-Disposition', 'attachment; filename="Rekap_Kunjungan_KUTUBIA.csv"')
            ->withHeader('Cache-Control', 'max-age=0')
            ->withHeader('Pragma', 'no-cache')
            ->withBody($bodyStream);
    }

    /**
     * Merender view khusus print/PDF tanpa navbar dan tanpa sidebar (layout kosong).
     */
    private function handleExportPdf(
        array $data,
        string $mode,
        string $rentangWaktu,
        string $labelRentang,
        string $periodeDetail
    ): ResponseInterface {
        $grandTotalAgregasi = 0;
        if ($mode !== 'input') {
            foreach ($data as $row) {
                $grandTotalAgregasi += (int)($row['total'] ?? 0);
            }
        }

        return $this->viewRenderer
            ->withLayout(null)
            ->render(__DIR__ . '/../View/rekap_print', [
                'mode'               => $mode,
                'data'               => $data,
                'rentangWaktu'       => $rentangWaktu,
                'labelRentang'       => $labelRentang,
                'periodeDetail'      => $periodeDetail,
                'grandTotalAgregasi' => $grandTotalAgregasi,
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
