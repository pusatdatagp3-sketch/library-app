<?php

declare(strict_types=1);

use App\Web\Perpustakaan\Model\KunjunganEntity;
use Yiisoft\Html\Html;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var string $mode 'input' | 'kelas' | 'rayon' | 'konsulat'
 * @var array<KunjunganEntity|array<string, mixed>> $data
 * @var KunjunganEntity[] $kunjunganList
 * @var int $limit
 * @var string $rentangWaktu 'semua' | 'minggu_ini' | 'bulan_ini'
 * @var string $searchQuery
 * @var string $labelRentang
 * @var string $periodeDetail
 * @var CurrentRoute $route
 * @var UrlGeneratorInterface $urlGenerator
 */

$mode = $mode ?? 'input';
$rentangWaktu = $rentangWaktu ?? 'semua';
$searchQuery = $searchQuery ?? '';
$totalItems = count($data);

// Mapping judul kategori dan ikon untuk mode agregasi
$modeLabels = [
    'kelas'    => ['label' => 'Kelas', 'col' => 'NAMA KELAS', 'icon' => 'ri-building-line'],
    'rayon'    => ['label' => 'Rayon', 'col' => 'NAMA RAYON', 'icon' => 'ri-community-line'],
    'konsulat' => ['label' => 'Konsulat', 'col' => 'NAMA KONSULAT', 'icon' => 'ri-global-line'],
];

$activeModeConfig = $modeLabels[$mode] ?? null;

$this->setTitle('Rekap Kunjungan Perpustakaan' . ($activeModeConfig ? ' - Per ' . $activeModeConfig['label'] : ''));

// Hitung total kunjungan jika dalam mode agregasi
$grandTotalAgregasi = 0;
if ($mode !== 'input') {
    foreach ($data as $row) {
        $grandTotalAgregasi += (int)($row['total'] ?? 0);
    }
}

// Generate URL untuk Export Excel dan PDF dengan parameter aktif
$baseExportParams = [
    'mode'          => $mode,
    'rentang_waktu' => $rentangWaktu,
    'action'        => 'export',
];
if ($searchQuery !== '') {
    $baseExportParams['q'] = $searchQuery;
}

$urlExportExcel = '?' . http_build_query(array_merge($baseExportParams, ['format' => 'excel']));
$urlExportPdf   = '?' . http_build_query(array_merge($baseExportParams, ['format' => 'pdf']));
?>

<!-- Bootstrap 5 CSS & JS CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
/* KUTUBIA Custom Color Scheme & Dark/Light Mode Tokens */
:root {
    --k-purple: #8b5cf6;
    --k-purple-bg: rgba(139, 92, 246, 0.12);
    --k-purple-border: rgba(139, 92, 246, 0.25);

    --k-blue: #3b82f6;
    --k-blue-bg: rgba(59, 130, 246, 0.12);
    --k-blue-border: rgba(59, 130, 246, 0.25);

    --k-green: #10b981;
    --k-green-bg: rgba(16, 185, 129, 0.12);
    --k-green-border: rgba(16, 185, 129, 0.25);

    --bs-primary: #8b5cf6;
    --bs-primary-rgb: 139, 92, 246;
}

[data-bs-theme="dark"] {
    --k-purple: #a78bfa;
    --k-purple-bg: rgba(167, 139, 250, 0.18);
    --k-purple-border: rgba(167, 139, 250, 0.35);

    --k-blue: #60a5fa;
    --k-blue-bg: rgba(96, 165, 250, 0.18);
    --k-blue-border: rgba(96, 165, 250, 0.35);

    --k-green: #34d399;
    --k-green-bg: rgba(52, 211, 153, 0.18);
    --k-green-border: rgba(52, 211, 153, 0.35);

    --bs-primary: #a78bfa;
    --bs-primary-rgb: 167, 139, 250;
}

/* Brand Kutubia */
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
.bg-k-purple-subtle {
    background-color: var(--k-purple-bg) !important;
}
.text-k-purple {
    color: var(--k-purple) !important;
}

.btn-kutubia {
    background-color: var(--k-purple) !important;
    border-color: var(--k-purple) !important;
    color: #ffffff !important;
}
.btn-kutubia:hover, .btn-kutubia:focus {
    opacity: 0.92;
    color: #ffffff !important;
}

/* Penyesuaian Elemen Dark Mode */
[data-bs-theme="dark"] .card {
    background-color: #111827 !important;
    border-color: #1f2937 !important;
}
[data-bs-theme="dark"] .table {
    --bs-table-bg: transparent;
    --bs-table-striped-bg: rgba(255, 255, 255, 0.02);
    --bs-table-hover-bg: rgba(255, 255, 255, 0.04);
    color: #e2e8f0;
}
[data-bs-theme="dark"] .table thead th {
    background-color: rgba(255, 255, 255, 0.03);
    border-bottom-color: #374151;
    color: #94a3b8;
}
[data-bs-theme="dark"] .table tfoot th {
    background-color: rgba(255, 255, 255, 0.03);
    border-top-color: #374151;
    color: #e2e8f0;
}

/* Search Input Styling */
.search-input-group {
    position: relative;
    max-width: 320px;
    width: 100%;
}
.search-input-group i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
}
.search-input-group input {
    padding-left: 38px;
    border-radius: 10px;
}

/* Nav Pills Custom Transition */
.nav-pill-btn {
    transition: all 0.2s ease-in-out;
}
.nav-pill-btn:hover:not(.active) {
    background-color: var(--k-purple-bg);
    color: var(--k-purple);
    border-color: var(--k-purple-border);
}
</style>

<div class="container-fluid py-3">

    <!-- ── 1. HEADER HERO BANNER (KUTUBIA PURPLE) ────────────────────────── -->
    <div class="p-4 p-md-4 mb-4 text-white rounded-4 shadow-sm bg-kutubia">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-2.5 py-1 small">
                        <i class="ri-history-line me-1"></i>Visit Analytics
                    </span>
                </div>
                <h1 class="h3 fw-bold text-white mb-1">Rekap Kunjungan Perpustakaan</h1>
                <p class="text-white-50 small mb-0">
                    Analitik &amp; rekapitulasi presensi santri perpustakaan KUTUBIA (Tampilan Detail &amp; Group By).
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="<?= $urlGenerator->generate('perpustakaan/scan') ?>" class="btn btn-light text-kutubia fw-semibold px-3 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-1">
                    <i class="ri-barcode-box-line fs-5"></i>
                    <span>Scan Kunjungan</span>
                </a>
                <a href="<?= $urlGenerator->generate('perpustakaan/dashboard') ?>" class="btn btn-outline-light px-3 py-2 rounded-3 d-inline-flex align-items-center gap-1">
                    <i class="ri-dashboard-line"></i>
                    <span>Dashboard</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ── 2. TAB NAV PILLS (MODE SWITCHER) ───────────────────────────────── -->
    <?php
    $tabQueryStr = ($rentangWaktu !== 'semua' ? '&rentang_waktu=' . urlencode($rentangWaktu) : '')
        . ($searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '');
    ?>
    <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
        <a href="?mode=input<?= $tabQueryStr ?>"
           class="btn nav-pill-btn rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 <?= $mode === 'input' ? 'btn-primary bg-kutubia border-0 text-white shadow-sm active' : 'btn-outline-secondary' ?>">
            <i class="ri-file-list-3-line"></i>
            <span>Rekap Per-Input</span>
        </a>

        <a href="?mode=kelas<?= $tabQueryStr ?>"
           class="btn nav-pill-btn rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 <?= $mode === 'kelas' ? 'btn-primary bg-kutubia border-0 text-white shadow-sm active' : 'btn-outline-secondary' ?>">
            <i class="ri-building-line"></i>
            <span>Rekap Per-Kelas</span>
        </a>

        <a href="?mode=rayon<?= $tabQueryStr ?>"
           class="btn nav-pill-btn rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 <?= $mode === 'rayon' ? 'btn-primary bg-kutubia border-0 text-white shadow-sm active' : 'btn-outline-secondary' ?>">
            <i class="ri-community-line"></i>
            <span>Rekap Per-Rayon</span>
        </a>

        <a href="?mode=konsulat<?= $tabQueryStr ?>"
           class="btn nav-pill-btn rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 <?= $mode === 'konsulat' ? 'btn-primary bg-kutubia border-0 text-white shadow-sm active' : 'btn-outline-secondary' ?>">
            <i class="ri-global-line"></i>
            <span>Rekap Per-Konsulat</span>
        </a>
    </div>

    <!-- ── 3. METRIC & FILTER CONTROLS BAR ───────────────────────────────── -->
    <div class="row g-3 align-items-center mb-3">
        <!-- Kolom Kiri: Badge Metrik Data & Filter Aktif -->
        <div class="col-12 col-lg-auto d-flex align-items-center gap-2 flex-wrap">
            <?php if ($mode === 'input'): ?>
                <span class="badge bg-k-purple-subtle text-k-purple px-3 py-2 rounded-pill fw-semibold fs-6">
                    <i class="ri-user-follow-line me-1"></i>
                    <span id="rekap-total-badge"><?= $totalItems ?></span> Data Terkini
                </span>
                <span class="badge bg-body-secondary text-body border px-3 py-2 rounded-pill fw-semibold fs-6" title="<?= Html::encode($periodeDetail ?? '') ?>">
                    <i class="ri-calendar-line text-k-purple me-1"></i><?= Html::encode($labelRentang ?? 'Semua Waktu') ?>
                </span>
            <?php else: ?>
                <span class="badge bg-k-purple-subtle text-k-purple px-3 py-2 rounded-pill fw-semibold fs-6">
                    <i class="<?= $activeModeConfig['icon'] ?> me-1"></i>
                    <span id="rekap-total-badge"><?= $totalItems ?></span> <?= Html::encode($activeModeConfig['label']) ?>
                </span>
                <span class="badge bg-body-secondary text-body border px-3 py-2 rounded-pill fw-semibold fs-6">
                    <i class="ri-bar-chart-line me-1 text-k-purple"></i>Total: <?= number_format($grandTotalAgregasi) ?> Kunjungan
                </span>
                <span class="badge bg-body-secondary text-body border px-3 py-2 rounded-pill fw-semibold fs-6" title="<?= Html::encode($periodeDetail ?? '') ?>">
                    <i class="ri-calendar-line text-k-purple me-1"></i><?= Html::encode($labelRentang ?? 'Semua Waktu') ?>
                </span>
            <?php endif; ?>
        </div>

        <!-- Kolom Kanan: Form Filter Waktu, Search Input, & Dropdown Export Data -->
        <div class="col-12 col-lg-auto ms-lg-auto">
            <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Parameter Mode Aktif -->
                <input type="hidden" name="mode" value="<?= Html::encode($mode) ?>">

                <!-- 1. Dropdown Filter Rentang Waktu (Sabtu-Jumat, Bulan Ini, Semua) -->
                <select name="rentang_waktu" class="form-select form-select-sm w-auto border-secondary-subtle rounded-3 py-1.5" onchange="this.form.submit()" title="Pilih Rentang Waktu Rekap">
                    <option value="semua" <?= $rentangWaktu === 'semua' ? 'selected' : '' ?>>Semua Waktu</option>
                    <option value="minggu_ini" <?= $rentangWaktu === 'minggu_ini' ? 'selected' : '' ?>>Minggu Ini (Sabtu - Jumat)</option>
                    <option value="bulan_ini" <?= $rentangWaktu === 'bulan_ini' ? 'selected' : '' ?>>Bulan Ini</option>
                </select>

                <!-- 2. Input Pencarian Data -->
                <div class="search-input-group">
                    <i class="ri-search-line"></i>
                    <input
                        type="text"
                        name="q"
                        value="<?= Html::encode($searchQuery) ?>"
                        class="form-control form-control-sm border-secondary-subtle"
                        id="table-search-input"
                        placeholder="<?= $mode === 'input' ? 'Cari stambuk, nama, kelas...' : 'Cari nama ' . strtolower($activeModeConfig['label'] ?? '') . '...' ?>"
                        autocomplete="off"
                    >
                </div>

                <!-- 3. Bootstrap Dropdown Export Data (Excel & PDF) -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-1 rounded-3 px-3 py-1.5" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Export Rekap Kunjungan">
                        <i class="ri-download-2-line"></i>
                        <span>Export Data</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-2">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= Html::encode($urlExportExcel) ?>">
                                <i class="ri-file-excel-2-line text-success fs-5"></i>
                                <div>
                                    <div class="fw-semibold">Export ke Excel (.xlsx)</div>
                                    <small class="text-secondary" style="font-size: 0.75rem;">Unduh berkas spreadsheet Microsoft Excel</small>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= Html::encode($urlExportPdf) ?>" target="_blank">
                                <i class="ri-file-pdf-2-line text-danger fs-5"></i>
                                <div>
                                    <div class="fw-semibold">Export ke PDF</div>
                                    <small class="text-secondary" style="font-size: 0.75rem;">Cetak dokumen &bull; Layout bersih</small>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </form>
        </div>
    </div>

    <!-- ── 4. KARTU TABEL DATA REKAP KUNJUNGAN ───────────────────────────── -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body table-responsive">

            <?php if ($mode === 'input'): ?>
                <!-- ── 4A. TABEL MODE: PER-INPUT (DETAIL DATA) ──────────────── -->
                <table class="table table-hover table-striped align-middle mb-0" id="table-rekap-kunjungan">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">No</th>
                            <th style="width: 175px;">Waktu Kunjungan</th>
                            <th style="width: 130px;">Stambuk</th>
                            <th>Nama Santri</th>
                            <th style="width: 110px;">Kelas</th>
                            <th>Rayon</th>
                            <th style="width: 160px;">Petugas</th>
                        </tr>
                    </thead>
                    <tbody id="rekap-tbody">
                        <?php if (empty($data)): ?>
                            <tr id="empty-row">
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="ri-inbox-2-line fs-1 d-block mb-2 text-muted opacity-50"></i>
                                    <h6 class="fw-semibold">Belum Ada Data Kunjungan</h6>
                                    <p class="small text-muted mb-3">Belum ada rekaman santri yang berkunjung ke perpustakaan.</p>
                                    <a href="<?= $urlGenerator->generate('perpustakaan/scan') ?>" class="btn btn-sm bg-kutubia text-white rounded-3">
                                        <i class="ri-barcode-box-line me-1"></i>Mulai Scan Barcode
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data as $index => $item): ?>
                                <tr class="rekap-data-row">
                                    <td class="text-center text-secondary small fw-semibold">
                                        <?= $index + 1 ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-body-emphasis small">
                                                <?= $item->waktu_kunjungan ? $item->waktu_kunjungan->format('d M Y') : '-' ?>
                                            </span>
                                            <span class="font-monospace text-secondary" style="font-size: 0.78rem;">
                                                <i class="ri-time-line me-1"></i><?= $item->waktu_kunjungan ? $item->waktu_kunjungan->format('H:i:s') : '-' ?> WIB
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-monospace fw-bold text-k-purple fs-6">
                                            <?= Html::encode((string)$item->stambuk) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-body-emphasis">
                                            <?= Html::encode((string)($item->nama_santri ?? '-')) ?>
                                        </div>
                                        <?php if (!empty($item->konsulat)): ?>
                                            <div class="text-secondary" style="font-size: 0.75rem;">
                                                Konsulat: <?= Html::encode((string)$item->konsulat) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-k-purple-subtle text-k-purple border border-k-purple border-opacity-25 px-2.5 py-1">
                                            <?= Html::encode((string)($item->kelas ?? '-')) ?>
                                        </span>
                                    </td>
                                    <td class="text-body-secondary small">
                                        <?= Html::encode((string)($item->rayon ?? '-')) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-body-secondary text-body border px-2 py-1 small fw-normal d-inline-flex align-items-center gap-1">
                                            <i class="ri-user-line text-secondary"></i>
                                            <?= Html::encode((string)$item->penginput) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

            <?php else: ?>
                <!-- ── 4B. TABEL MODE: AGREGASI (KELAS / RAYON / KONSULAT) ─────── -->
                <table class="table table-hover table-striped align-middle mb-0" id="table-rekap-kunjungan">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 70px;">NO</th>
                            <th>
                                <i class="<?= $activeModeConfig['icon'] ?> text-k-purple me-1"></i>
                                <?= Html::encode($activeModeConfig['col'] ?? 'NAMA') ?>
                            </th>
                            <th class="text-end" style="width: 220px; padding-right: 1.5rem;">
                                <i class="ri-user-voice-line text-k-purple me-1"></i>JUMLAH KUNJUNGAN
                            </th>
                        </tr>
                    </thead>
                    <tbody id="rekap-tbody">
                        <?php if (empty($data)): ?>
                            <tr id="empty-row">
                                <td colspan="3" class="text-center py-5 text-secondary">
                                    <i class="ri-inbox-2-line fs-1 d-block mb-2 text-muted opacity-50"></i>
                                    <h6 class="fw-semibold">Belum Ada Data Rekapitulasi</h6>
                                    <p class="small text-muted mb-3">Belum ada kunjungan santri yang terkelompokkan.</p>
                                    <a href="<?= $urlGenerator->generate('perpustakaan/scan') ?>" class="btn btn-sm bg-kutubia text-white rounded-3">
                                        <i class="ri-barcode-box-line me-1"></i>Mulai Scan Barcode
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data as $index => $row): ?>
                                <?php
                                $namaKategori = (string)($row['nama'] ?? $row[$mode] ?? 'Tidak Terdata');
                                $totalKunjungan = (int)($row['total'] ?? 0);
                                ?>
                                <tr class="rekap-data-row">
                                    <td class="text-center text-secondary small fw-semibold">
                                        <?= $index + 1 ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-k-purple-subtle text-k-purple p-2 rounded-circle">
                                                <i class="<?= $activeModeConfig['icon'] ?> fs-6"></i>
                                            </span>
                                            <span class="fw-bold text-body-emphasis fs-6">
                                                <?= Html::encode($namaKategori) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-end" style="padding-right: 1.5rem;">
                                        <span class="badge bg-k-purple-subtle text-k-purple border border-k-purple border-opacity-25 px-3 py-1.5 rounded-pill font-monospace fw-bold fs-6">
                                            <?= number_format($totalKunjungan) ?> <span class="fw-normal small">Santri</span>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($data)): ?>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-end fw-bold text-body-emphasis" style="padding-right: 1rem;">
                                    TOTAL KESELURUHAN:
                                </th>
                                <th class="text-end" style="padding-right: 1.5rem;">
                                    <span class="badge bg-kutubia text-white px-3 py-1.5 rounded-pill font-monospace fw-bold fs-6">
                                        <?= number_format($grandTotalAgregasi) ?> Santri
                                    </span>
                                </th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            <?php endif; ?>

            <!-- Placeholder Pencarian Kosong -->
            <div id="no-search-results" class="text-center py-5 text-secondary d-none">
                <i class="ri-search-eye-line fs-1 d-block mb-2 text-muted opacity-50"></i>
                <h6 class="fw-semibold">Tidak Ada Hasil Ditemukan</h6>
                <p class="small text-muted mb-0">Tidak ada data yang cocok dengan kata kunci pencarian tersebut.</p>
            </div>

        </div>
    </div>

</div>

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

// Client-side Instant Filter Search (Berlaku untuk semua mode)
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('table-search-input');
    const tableRows = document.querySelectorAll('.rekap-data-row');
    const noResultsDiv = document.getElementById('no-search-results');
    const totalBadge = document.getElementById('rekap-total-badge');

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const keyword = this.value.toLowerCase().trim();
        let visibleCount = 0;

        tableRows.forEach(function (row) {
            const rowText = row.innerText.toLowerCase();
            if (keyword === '' || rowText.includes(keyword)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (totalBadge) {
            totalBadge.textContent = visibleCount;
        }

        if (noResultsDiv) {
            if (visibleCount === 0 && tableRows.length > 0) {
                noResultsDiv.classList.remove('d-none');
            } else {
                noResultsDiv.classList.add('d-none');
            }
        }
    });
});
</script>
