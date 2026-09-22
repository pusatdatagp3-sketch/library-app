<?php

declare(strict_types=1);

use App\Web\Perpustakaan\Model\KunjunganEntity;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var int $totalHariIni
 * @var int $totalMingguIni
 * @var array{labels: string[], totals: int[]} $chartKelas
 * @var array{labels: string[], totals: int[]} $chartRayon
 * @var array{labels: string[], totals: int[]} $chartKonsulat
 * @var KunjunganEntity[] $kunjunganTerbaru
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Dashboard Perpustakaan');

// Data untuk chart
$chartKelas = $chartKelas ?? ['labels' => [], 'totals' => []];
$chartRayon = $chartRayon ?? ['labels' => [], 'totals' => []];
$chartKonsulat = $chartKonsulat ?? ['labels' => [], 'totals' => []];
$topVisitors = $topVisitors ?? [];

// Total kelas aktif
$totalKelasAktif = count($chartKelas['labels']);

// Format tanggal lokal Indonesia
$hariList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$bulanList = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
$hariIni = $hariList[(int)date('w')];
$tgl = (int)date('j');
$bln = $bulanList[(int)date('n')];
$thn = date('Y');
$tanggalLengkap = "{$hariIni}, {$tgl} {$bln} {$thn}";
?>

<!-- Bootstrap 5 CSS & JS CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
/* KUTUBIA Custom Pastel Color Scheme (Light & Dark Mode) */
:root {
    --k-purple: #8b5cf6;
    --k-purple-bg: rgba(139, 92, 246, 0.12);
    --k-purple-border: rgba(139, 92, 246, 0.25);

    --k-pink: #ec4899;
    --k-pink-bg: rgba(236, 72, 153, 0.12);
    --k-pink-border: rgba(236, 72, 153, 0.25);

    --k-blue: #3b82f6;
    --k-blue-bg: rgba(59, 130, 246, 0.12);
    --k-blue-border: rgba(59, 130, 246, 0.25);

    /* Override Bootstrap primary */
    --bs-primary: #8b5cf6;
    --bs-primary-rgb: 139, 92, 246;
}

[data-bs-theme="dark"] {
    --k-purple: #a78bfa;
    --k-purple-bg: rgba(167, 139, 250, 0.18);
    --k-purple-border: rgba(167, 139, 250, 0.35);

    --k-pink: #f472b6;
    --k-pink-bg: rgba(244, 114, 182, 0.18);
    --k-pink-border: rgba(244, 114, 182, 0.35);

    --k-blue: #60a5fa;
    --k-blue-bg: rgba(96, 165, 250, 0.18);
    --k-blue-border: rgba(96, 165, 250, 0.35);

    /* Override Bootstrap primary in dark mode */
    --bs-primary: #a78bfa;
    --bs-primary-rgb: 167, 139, 250;
}

/* Brand Utama (KUTUBIA Purple) */
.bg-kutubia {
    background-color: var(--k-purple) !important;
    color: #ffffff !important;
}
.text-kutubia {
    color: var(--k-purple) !important;
}
.border-kutubia {
    border-color: var(--k-purple) !important;
}

/* Soft Purple Classes */
.text-k-purple {
    color: var(--k-purple) !important;
}
.border-k-purple {
    border-color: var(--k-purple) !important;
}
.bg-k-purple-subtle {
    background-color: var(--k-purple-bg) !important;
}

/* Soft Pink Classes */
.text-k-pink {
    color: var(--k-pink) !important;
}
.border-k-pink {
    border-color: var(--k-pink) !important;
}
.bg-k-pink-subtle {
    background-color: var(--k-pink-bg) !important;
}

/* Soft Blue Classes */
.text-k-blue {
    color: var(--k-blue) !important;
}
.border-k-blue {
    border-color: var(--k-blue) !important;
}
.bg-k-blue-subtle {
    background-color: var(--k-blue-bg) !important;
}

/* Button Kutubia */
.btn-kutubia {
    background-color: var(--k-purple) !important;
    border-color: var(--k-purple) !important;
    color: #ffffff !important;
}
.btn-kutubia:hover, .btn-kutubia:focus {
    opacity: 0.92;
    color: #ffffff !important;
}
.btn-outline-kutubia {
    color: var(--k-purple) !important;
    border-color: var(--k-purple) !important;
    background-color: transparent !important;
}
.btn-outline-kutubia:hover, .btn-outline-kutubia:focus {
    background-color: var(--k-purple) !important;
    border-color: var(--k-purple) !important;
    color: #ffffff !important;
}

/* Penyesuaian Elemen Dark Mode */
[data-bs-theme="dark"] .card {
    background-color: #111827 !important;
    border-color: #1f2937 !important;
}
[data-bs-theme="dark"] .list-group-item {
    border-color: #1f2937 !important;
}

/* Custom Nav Pills untuk Switcher Multi-Chart */
.nav-pills-chart .nav-link {
    color: var(--bs-body-color, #64748b);
    background: transparent;
    border: 1px solid var(--k-purple-border);
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.35rem 0.85rem;
    border-radius: 50rem;
    transition: all 0.2s ease-in-out;
}
.nav-pills-chart .nav-link:hover {
    background: var(--k-purple-bg);
    color: var(--k-purple);
}
.nav-pills-chart .nav-link.active {
    background-color: var(--k-purple) !important;
    color: #ffffff !important;
    border-color: var(--k-purple) !important;
    box-shadow: 0 2px 8px rgba(139, 92, 246, 0.35);
}
</style>

<div class="container-fluid py-3">

    <!-- 1. HERO BANNER -->
    <div class="p-4 p-md-5 mb-4 text-white rounded-4 shadow-sm bg-kutubia">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="h2 fw-bold text-white mb-2">Selamat Datang di KUTUBIA</h1>
                <p class="text-white-50 fs-6 mb-0">Sistem Informasi &amp; Manajemen Perpustakaan Terpadu</p>
            </div>
            <div class="text-md-end">
                <span class="badge bg-white bg-opacity-25 text-white px-3 py-2 rounded-pill fs-6 fw-normal">
                    <i class="ri-calendar-line me-1"></i><?= Html::encode($tanggalLengkap) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- 2. KARTU METRIK BERJEJER (BARIS PERTAMA) -->
    <div class="row g-4 mb-4">
        <!-- Kartu 1: Kunjungan Hari Ini (Soft Blue) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 border-start border-4 border-k-blue h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-k-blue"><i class="ri-user-line fs-4"></i></div>
                        <span class="badge bg-k-blue-subtle text-k-blue rounded-pill">Hari ini</span>
                    </div>
                    <h2 class="fw-bold text-body-emphasis mb-1"><?= number_format($totalHariIni) ?></h2>
                    <p class="text-body-secondary small mb-0">Kunjungan Hari Ini</p>
                </div>
            </div>
        </div>

        <!-- Kartu 2: Kunjungan Minggu Ini (Soft Pink) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 border-start border-4 border-k-pink h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-k-pink"><i class="ri-calendar-event-line fs-4"></i></div>
                        <span class="badge bg-k-pink-subtle text-k-pink rounded-pill">Minggu ini</span>
                    </div>
                    <h2 class="fw-bold text-body-emphasis mb-1"><?= number_format($totalMingguIni) ?></h2>
                    <p class="text-body-secondary small mb-0">Kunjungan Minggu Ini</p>
                </div>
            </div>
        </div>

        <!-- Kartu 3: Total Kelas Aktif (Soft Purple) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 border-start border-4 border-k-purple h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-k-purple"><i class="ri-book-mark-line fs-4"></i></div>
                        <span class="badge bg-k-purple-subtle text-k-purple rounded-pill">Kelas Aktif</span>
                    </div>
                    <h2 class="fw-bold text-body-emphasis mb-1"><?= number_format($totalKelasAktif) ?></h2>
                    <p class="text-body-secondary small mb-0">Total Kelas Terdata</p>
                </div>
            </div>
        </div>

        <!-- Kartu 4: Mode Input (Soft Blue) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 border-start border-4 border-k-blue h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="text-k-blue"><i class="ri-barcode-box-line fs-4"></i></div>
                        <span class="badge bg-k-blue-subtle text-k-blue rounded-pill">Online</span>
                    </div>
                    <h2 class="fw-bold text-body-emphasis mb-1 fs-3">Barcode USB</h2>
                    <p class="text-body-secondary small mb-0">Mode Input Aktif</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. GRAFIK BATANG MULTI-CHART & AKSI/PENGUNJUNG TERBARU (BARIS KEDUA) -->
    <div class="row g-4">
        <!-- Kolom Kiri: Multi-Chart (75% lebar di desktop) -->
        <div class="col-lg-8">
            <!-- Kartu Leaderboard: Top 3 Pengunjung Minggu Ini (Horizontal Podium) -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 text-body-emphasis">
                        <i class="ri-trophy-line text-warning me-1"></i> Top 3 Pengunjung Minggu Ini
                    </h6>

                    <?php if (empty($topVisitors)): ?>
                        <p class="text-body-secondary small mb-0">Belum ada data kunjungan minggu ini.</p>
                    <?php else: ?>
                        <?php $medals = ['🥇', '🥈', '🥉']; ?>
                        <div class="row g-3">
                            <?php foreach ($topVisitors as $idx => $visitor): ?>
                                <?php
                                    $medal = $medals[$idx] ?? '🎖️';
                                    $namaSantri = is_array($visitor) ? ($visitor['nama_santri'] ?? '-') : ($visitor->nama_santri ?? '-');
                                    $kelas = is_array($visitor) ? ($visitor['kelas'] ?? '-') : ($visitor->kelas ?? '-');
                                    $total = is_array($visitor) ? ($visitor['total_kunjungan'] ?? 0) : ($visitor->total_kunjungan ?? 0);
                                ?>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded text-center h-100 d-flex flex-column justify-content-center align-items-center bg-body-tertiary bg-opacity-50">
                                        <div class="fs-2 mb-1"><?= $medal ?></div>
                                        <div class="fw-bold text-truncate w-100 text-body-emphasis" title="<?= Html::encode((string)$namaSantri) ?>">
                                            <?= Html::encode((string)$namaSantri) ?>
                                        </div>
                                        <div class="text-muted small mb-2">
                                            Kelas: <?= Html::encode((string)$kelas) ?>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">
                                            <?= (int)$total ?> Kali Kunjungan
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card Grafik Statistik Kunjungan -->
            <div class="card border-0 shadow-sm rounded-4">
                <!-- Header Kartu dengan Nav Pills Switcher -->
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                    <div>
                        <h5 class="card-title fw-bold mb-1 text-body-emphasis d-flex align-items-center flex-wrap gap-2">
                            <span><i class="ri-bar-chart-box-line text-k-purple me-2"></i>Statistik Kunjungan Santri</span>
                            <span class="badge bg-k-purple-subtle text-k-purple rounded-pill small fw-normal font-monospace" style="font-size: 0.72rem;">
                                <i class="ri-cursor-line me-1"></i>Klik batang untuk drill-down
                            </span>
                        </h5>
                        <p class="text-body-secondary small mb-0">Visualisasi volume presensi santri perpustakaan minggu ini (Sabtu - Jumat).</p>
                    </div>

                    <!-- Bootstrap Nav Pills Switcher (Kelas, Rayon, Konsulat) -->
                    <ul class="nav nav-pills nav-pills-chart gap-1" id="chartSwitcherTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-chart-kelas" data-bs-toggle="pill" data-bs-target="#pane-chart-kelas" type="button" role="tab" aria-controls="pane-chart-kelas" aria-selected="true">
                                <i class="ri-building-line me-1"></i>Kelas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-chart-rayon" data-bs-toggle="pill" data-bs-target="#pane-chart-rayon" type="button" role="tab" aria-controls="pane-chart-rayon" aria-selected="false">
                                <i class="ri-community-line me-1"></i>Rayon
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-chart-konsulat" data-bs-toggle="pill" data-bs-target="#pane-chart-konsulat" type="button" role="tab" aria-controls="pane-chart-konsulat" aria-selected="false">
                                <i class="ri-global-line me-1"></i>Konsulat
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Body Kartu: Tab Content untuk 3 Canvas Chart.js -->
                <div class="card-body p-4 pt-2">
                    <div class="tab-content" id="chartSwitcherContent">

                        <!-- Tab Pane 1: Chart Kelas -->
                        <div class="tab-pane fade show active" id="pane-chart-kelas" role="tabpanel" aria-labelledby="tab-chart-kelas">
                            <?php if (empty($chartKelas['labels'])): ?>
                                <div class="text-center py-5 text-body-secondary">
                                    <i class="ri-bar-chart-line fs-1 mb-2 d-block opacity-50"></i>
                                    <p class="mb-2">Belum ada data rekaman kunjungan per kelas.</p>
                                    <a href="<?= $urlGenerator->generate('perpustakaan/scan') ?>" class="btn btn-sm bg-kutubia text-white">
                                        <i class="ri-barcode-box-line me-1"></i>Mulai Scan Barcode
                                    </a>
                                </div>
                            <?php else: ?>
                                <div style="position: relative; height: 350px; width: 100%;">
                                    <canvas id="chartKelas"></canvas>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Pane 2: Chart Rayon -->
                        <div class="tab-pane fade" id="pane-chart-rayon" role="tabpanel" aria-labelledby="tab-chart-rayon">
                            <?php if (empty($chartRayon['labels'])): ?>
                                <div class="text-center py-5 text-body-secondary">
                                    <i class="ri-bar-chart-line fs-1 mb-2 d-block opacity-50"></i>
                                    <p class="mb-2">Belum ada data rekaman kunjungan per rayon.</p>
                                    <a href="<?= $urlGenerator->generate('perpustakaan/scan') ?>" class="btn btn-sm bg-kutubia text-white">
                                        <i class="ri-barcode-box-line me-1"></i>Mulai Scan Barcode
                                    </a>
                                </div>
                            <?php else: ?>
                                <div style="position: relative; height: 350px; width: 100%;">
                                    <canvas id="chartRayon"></canvas>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Pane 3: Chart Konsulat -->
                        <div class="tab-pane fade" id="pane-chart-konsulat" role="tabpanel" aria-labelledby="tab-chart-konsulat">
                            <?php if (empty($chartKonsulat['labels'])): ?>
                                <div class="text-center py-5 text-body-secondary">
                                    <i class="ri-bar-chart-line fs-1 mb-2 d-block opacity-50"></i>
                                    <p class="mb-2">Belum ada data rekaman kunjungan per konsulat.</p>
                                    <a href="<?= $urlGenerator->generate('perpustakaan/scan') ?>" class="btn btn-sm bg-kutubia text-white">
                                        <i class="ri-barcode-box-line me-1"></i>Mulai Scan Barcode
                                    </a>
                                </div>
                            <?php else: ?>
                                <div style="position: relative; height: 350px; width: 100%;">
                                    <canvas id="chartKonsulat"></canvas>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Aksi & Pengunjung Terbaru (25% lebar di desktop) -->
        <div class="col-lg-4 d-flex flex-column gap-3">
            <!-- Kartu Aksi Cepat -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                    <h6 class="card-title fw-bold mb-0 text-body-emphasis">
                        <i class="ri-flashlight-line text-k-purple me-2"></i>Aksi Cepat
                    </h6>
                </div>
                <div class="card-body px-4 pb-4 pt-2">
                    <p class="text-body-secondary small mb-3">Mulai proses pencatatan presensi atau buka riwayat rekap.</p>
                    <a href="<?= $urlGenerator->generate('perpustakaan/scan') ?>" class="btn bg-kutubia text-white w-100 py-2 d-flex align-items-center justify-content-center gap-2 mb-2 rounded-3 shadow-sm">
                        <i class="ri-barcode-box-line fs-5"></i>
                        <span class="fw-semibold">Buka Scanner Barcode</span>
                    </a>
                    <a href="<?= $urlGenerator->generate('perpustakaan/rekap') ?>" class="btn btn-outline-kutubia w-100 py-2 d-flex align-items-center justify-content-center gap-2 mb-2 rounded-3">
                        <i class="ri-history-line"></i>
                        <span>Lihat Rekap Kunjungan</span>
                    </a>
                    <a href="<?= $urlGenerator->generate('perpustakaan/dashboard') ?>" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center gap-2 rounded-3">
                        <i class="ri-refresh-line"></i>
                        <span>Segarkan Data</span>
                    </a>
                </div>
            </div>

            <!-- Kartu Pengunjung Terbaru -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                    <h6 class="card-title fw-bold mb-0 text-body-emphasis">
                        <i class="ri-time-line text-k-purple me-2"></i>Pengunjung Terbaru
                    </h6>
                    <span class="badge bg-k-purple-subtle text-k-purple border border-k-purple border-opacity-25">Hari Ini</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($kunjunganTerbaru)): ?>
                        <div class="text-center py-4 text-body-secondary small">
                            <i class="ri-inbox-line fs-3 d-block mb-1 opacity-50"></i>
                            Belum ada santri yang berkunjung hari ini.
                        </div>
                    <?php else: ?>
                        <?php $sepuluhTerbaru = array_slice($kunjunganTerbaru, 0, 10); ?>
                        <div class="list-group list-group-flush bg-transparent">
                            <?php foreach ($sepuluhTerbaru as $index => $item): ?>
                                <div class="list-group-item bg-transparent px-4 py-2 border-0 d-flex justify-content-between align-items-center <?= $index < count($sepuluhTerbaru) - 1 ? 'border-bottom' : '' ?>">
                                    <div class="overflow-hidden me-2">
                                        <div class="fw-semibold text-body-emphasis small text-truncate">
                                            <?= Html::encode((string)($item->nama_santri ?? '-')) ?>
                                        </div>
                                        <div class="text-body-secondary" style="font-size: 0.75rem;">
                                            <span class="text-k-purple font-monospace fw-semibold"><?= Html::encode((string)$item->stambuk) ?></span>
                                            • Kls <?= Html::encode((string)($item->kelas ?? '-')) ?>
                                        </div>
                                    </div>
                                    <span class="badge bg-k-purple-subtle text-k-purple border border-k-purple border-opacity-25 font-monospace" style="font-size: 0.72rem;">
                                        <?= $item->waktu_kunjungan ? $item->waktu_kunjungan->format('H:i') : '-' ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 4. MODAL DETAIL KUNJUNGAN (DRILL-DOWN AJAX) ────────────────────── -->
    <div class="modal fade" id="modalDetailKunjungan" tabindex="-1" aria-labelledby="modalDetailTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-kutubia text-white border-0 py-3 px-4">
                    <div>
                        <h5 class="modal-title fw-bold mb-0 d-flex align-items-center gap-2" id="modalDetailTitle">
                            <i class="ri-pie-chart-2-line"></i>
                            <span>Detail Kunjungan Santri</span>
                        </h5>
                        <p class="small text-white-50 mb-0" id="modalDetailSubtitle">
                            Daftar santri yang berkunjung pada kategori ini
                        </p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Loading Spinner State -->
                    <div id="modalLoadingSpinner" class="text-center py-5">
                        <div class="spinner-border text-kutubia" role="status" style="width: 2.5rem; height: 2.5rem;">
                            <span class="visually-hidden">Memuat data...</span>
                        </div>
                        <p class="text-secondary small mt-2 mb-0">Mengambil data santri dari database...</p>
                    </div>

                    <!-- Empty State -->
                    <div id="modalEmptyState" class="text-center py-5 d-none">
                        <i class="ri-inbox-2-line fs-1 d-block mb-2 text-muted opacity-50"></i>
                        <h6 class="fw-semibold text-body-emphasis">Tidak Ada Data Kunjungan</h6>
                        <p class="small text-muted mb-0">Tidak ditemukan santri yang berkunjung pada kategori ini.</p>
                    </div>

                    <!-- Error State -->
                    <div id="modalErrorState" class="text-center py-5 d-none">
                        <i class="ri-error-warning-line fs-1 d-block mb-2 text-danger opacity-75"></i>
                        <h6 class="fw-semibold text-danger">Gagal Memuat Data</h6>
                        <p class="small text-muted mb-0" id="modalErrorMessage">Terjadi kesalahan saat memanggil server.</p>
                    </div>

                    <!-- Content Table -->
                    <div id="modalTableWrapper" class="table-responsive d-none">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-k-purple-subtle text-k-purple px-3 py-1.5 rounded-pill font-monospace fw-bold" id="modalDataCountBadge">
                                0 Santri
                            </span>
                            <span class="text-secondary small">Diurutkan dari kunjungan terbaru</span>
                        </div>
                        <table class="table table-hover table-striped align-middle mb-0" id="modalDetailTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th style="width: 155px;">Waktu</th>
                                    <th style="width: 110px;">Stambuk</th>
                                    <th>Nama Santri</th>
                                    <th style="width: 90px;">Kelas</th>
                                    <th>Rayon</th>
                                    <th>Petugas</th>
                                </tr>
                            </thead>
                            <tbody id="modalDetailTbody">
                                <!-- Baris disuntikkan via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-2 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary rounded-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

</div><!-- /.container-fluid -->

<!-- Sinkronisasi Otomatis Tema Dark/Light Mode dengan Layout Utama -->
<script>
(function() {
    function applyTheme() {
        const currentTheme = document.body.getAttribute('data-theme') || localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', currentTheme);
        document.body.setAttribute('data-bs-theme', currentTheme);
    }
    applyTheme();

    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'data-theme') {
                applyTheme();
            }
        });
    });
    observer.observe(document.body, { attributes: true, attributeFilter: ['data-theme'] });
})();
</script>

<!-- Load Chart.js dari CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Data JSON dari Controller ─────────────────────────────────────────
    const dataKelas    = <?= json_encode($chartKelas, JSON_UNESCAPED_UNICODE) ?>;
    const dataRayon    = <?= json_encode($chartRayon, JSON_UNESCAPED_UNICODE) ?>;
    const dataKonsulat = <?= json_encode($chartKonsulat, JSON_UNESCAPED_UNICODE) ?>;

    const isDarkMode = (document.body.getAttribute('data-theme') || localStorage.getItem('theme')) === 'dark';
    const gridColor  = isDarkMode ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
    const textColor  = isDarkMode ? '#94a3b8' : '#6c757d';

    // Warna KUTUBIA Pastel Purple
    const barBgColor     = isDarkMode ? 'rgba(167, 139, 250, 0.75)' : 'rgba(139, 92, 246, 0.75)';
    const barHoverBg     = isDarkMode ? 'rgba(167, 139, 250, 0.95)' : 'rgba(139, 92, 246, 0.95)';
    const barBorderColor = isDarkMode ? '#a78bfa' : '#8b5cf6';

    const chartInstances = {};

    // ── Elemen DOM Modal Detail ───────────────────────────────────────────
    const modalEl            = document.getElementById('modalDetailKunjungan');
    const modalTitle         = document.getElementById('modalDetailTitle');
    const modalSubtitle      = document.getElementById('modalDetailSubtitle');
    const modalSpinner       = document.getElementById('modalLoadingSpinner');
    const modalTableWrapper  = document.getElementById('modalTableWrapper');
    const modalEmptyState    = document.getElementById('modalEmptyState');
    const modalErrorState    = document.getElementById('modalErrorState');
    const modalErrorMessage  = document.getElementById('modalErrorMessage');
    const modalTbody         = document.getElementById('modalDetailTbody');
    const modalCountBadge    = document.getElementById('modalDataCountBadge');

    const apiDetailUrlBase = '<?= $urlGenerator->generate('perpustakaan/api/detail-kunjungan') ?>';

    function escapeHtml(text) {
        if (text == null) return '-';
        return text.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ── Fungsi AJAX Fetch untuk Modal Drill-Down ──────────────────────────
    function fetchDetailKunjungan(type, value) {
        if (!modalEl) return;
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

        const typeLabel = type.charAt(0).toUpperCase() + type.slice(1);
        modalTitle.innerHTML = `<i class="ri-pie-chart-2-line me-1"></i> Detail Kunjungan ${typeLabel}: "${escapeHtml(value)}"`;
        modalSubtitle.textContent = `Daftar riwayat santri pada ${type.toLowerCase()} ${value}`;

        // Reset state modal
        modalSpinner.classList.remove('d-none');
        modalTableWrapper.classList.add('d-none');
        modalEmptyState.classList.add('d-none');
        modalErrorState.classList.add('d-none');
        modalTbody.innerHTML = '';

        modalInstance.show();

        const fetchUrl = `${apiDetailUrlBase}?type=${encodeURIComponent(type)}&value=${encodeURIComponent(value)}`;

        fetch(fetchUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async res => {
            const json = await res.json().catch(() => ({
                success: false,
                message: 'Format respon server tidak valid.'
            }));
            return { ok: res.ok, data: json };
        })
        .then(({ ok, data }) => {
            modalSpinner.classList.add('d-none');

            if (ok && data.success && Array.isArray(data.data) && data.data.length > 0) {
                modalCountBadge.textContent = `${data.data.length} Santri`;

                let rowsHtml = '';
                data.data.forEach((item, index) => {
                    rowsHtml += `
                        <tr>
                            <td class="text-center text-secondary small fw-semibold">${index + 1}</td>
                            <td class="small">
                                <span class="font-monospace text-body-emphasis">${escapeHtml(item.waktu_kunjungan)}</span>
                            </td>
                            <td>
                                <span class="font-monospace fw-bold text-k-purple">${escapeHtml(item.stambuk)}</span>
                            </td>
                            <td class="fw-semibold text-body-emphasis">${escapeHtml(item.nama_santri)}</td>
                            <td>
                                <span class="badge bg-k-purple-subtle text-k-purple border border-k-purple border-opacity-25 px-2 py-0.5">${escapeHtml(item.kelas)}</span>
                            </td>
                            <td class="text-secondary small">${escapeHtml(item.rayon)}</td>
                            <td class="small text-secondary">
                                <i class="ri-user-line me-1"></i>${escapeHtml(item.petugas || item.penginput)}
                            </td>
                        </tr>
                    `;
                });

                modalTbody.innerHTML = rowsHtml;
                modalTableWrapper.classList.remove('d-none');
            } else if (ok && data.success) {
                modalEmptyState.classList.remove('d-none');
            } else {
                modalErrorMessage.textContent = data.message || 'Terjadi kesalahan sistem.';
                modalErrorState.classList.remove('d-none');
            }
        })
        .catch(err => {
            modalSpinner.classList.add('d-none');
            modalErrorMessage.textContent = err.message || 'Gagal tersambung ke server.';
            modalErrorState.classList.remove('d-none');
        });
    }

    // ── Helper Membuat Instance Bar Chart dengan Event Click ─────────────
    function createBarChart(canvasId, chartData, unitLabel, unitType) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !chartData || !chartData.labels || chartData.labels.length === 0) {
            return null;
        }

        const ctx = canvas.getContext('2d');
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Jumlah Kunjungan',
                    data: chartData.totals,
                    backgroundColor: barBgColor,
                    hoverBackgroundColor: barHoverBg,
                    borderColor: barBorderColor,
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onHover: function(event, chartElement) {
                    event.native.target.style.cursor = chartElement && chartElement.length > 0 ? 'pointer' : 'default';
                },
                onClick: function(event, elements, chart) {
                    if (elements && elements.length > 0) {
                        const index = elements[0].index;
                        const label = chart.data.labels[index];
                        if (label) {
                            fetchDetailKunjungan(unitType, label);
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: isDarkMode ? '#1e293b' : '#212529',
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.parsed.y + ' santri (klik untuk detail)';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0,
                            color: textColor
                        },
                        grid: {
                            color: gridColor
                        }
                    },
                    x: {
                        ticks: {
                            color: textColor,
                            font: { weight: '500' }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Inisialisasi 3 Chart (Kelas, Rayon, Konsulat) dengan Type Identifier
    chartInstances['chartKelas']    = createBarChart('chartKelas', dataKelas, 'Kelas', 'kelas');
    chartInstances['chartRayon']    = createBarChart('chartRayon', dataRayon, 'Rayon', 'rayon');
    chartInstances['chartKonsulat'] = createBarChart('chartKonsulat', dataKonsulat, 'Konsulat', 'konsulat');

    // Listener saat tab Bootstrap berganti: trigger chart.resize()
    document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(function(pillEl) {
        pillEl.addEventListener('shown.bs.tab', function(event) {
            const targetId = event.target.getAttribute('data-bs-target');
            if (!targetId) return;

            const canvas = document.querySelector(targetId + ' canvas');
            if (canvas && chartInstances[canvas.id]) {
                chartInstances[canvas.id].resize();
            }
        });
    });
});
</script>
