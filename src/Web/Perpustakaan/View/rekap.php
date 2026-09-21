<?php

declare(strict_types=1);

use App\Web\Perpustakaan\Model\KunjunganEntity;
use Yiisoft\Html\Html;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var KunjunganEntity[] $kunjunganList
 * @var int $limit
 * @var CurrentRoute $route
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Rekap Kunjungan Perpustakaan');
$totalData = count($kunjunganList);
?>

<!-- Bootstrap 5 CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
</style>

<div class="container-fluid py-3">

    <!-- ── 1. HEADER HALAMAN (SENADA DENGAN UI KUTUBIA) ───────────────────── -->
    <div class="p-4 p-md-4 mb-4 text-white rounded-4 shadow-sm bg-kutubia">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-2.5 py-1 small">
                        <i class="ri-history-line me-1"></i>Visit History
                    </span>
                </div>
                <h1 class="h3 fw-bold text-white mb-1">Rekap Kunjungan Perpustakaan</h1>
                <p class="text-white-50 small mb-0">Riwayat presensi santri yang telah melakukan scan barcode di perpustakaan KUTUBIA.</p>
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

    <!-- ── 2. METRIC / FILTER BAR ────────────────────────────────────────── -->
    <div class="row g-3 align-items-center mb-3">
        <div class="col-12 col-md-auto d-flex align-items-center gap-2">
            <span class="badge bg-k-purple-subtle text-k-purple px-3 py-2 rounded-pill fw-semibold fs-6">
                <i class="ri-user-follow-line me-1"></i>
                <span id="rekap-total-badge"><?= $totalData ?></span> Data Terkini
            </span>
            <span class="text-secondary small d-none d-sm-inline">
                (Menampilkan maksimal <?= $limit ?> kunjungan terbaru)
            </span>
        </div>
        <div class="col-12 col-md-auto ms-md-auto d-flex align-items-center gap-2">
            <!-- Live Search Bar -->
            <div class="search-input-group">
                <i class="ri-search-line"></i>
                <input
                    type="text"
                    class="form-control form-control-sm border-secondary-subtle"
                    id="table-search-input"
                    placeholder="Cari stambuk, nama, kelas..."
                    autocomplete="off"
                >
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-3" onclick="window.print()" title="Cetak / Simpan PDF">
                <i class="ri-printer-line me-1"></i>Cetak
            </button>
        </div>
    </div>

    <!-- ── 3. TABEL DATA REKAP KUNJUNGAN ─────────────────────────────────── -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body table-responsive">
            <table class="table table-hover table-striped align-middle mb-0" id="table-rekap-kunjungan">
                <thead class="table-light">
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
                    <?php if (empty($kunjunganList)): ?>
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
                        <?php foreach ($kunjunganList as $index => $item): ?>
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
                                    <span class="badge bg-light text-dark border px-2 py-1 small fw-normal d-inline-flex align-items-center gap-1">
                                        <i class="ri-user-line text-secondary"></i>
                                        <?= Html::encode((string)$item->penginput) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Placeholder Pencarian Kosong -->
            <div id="no-search-results" class="text-center py-5 text-secondary d-none">
                <i class="ri-search-eye-line fs-1 d-block mb-2 text-muted opacity-50"></i>
                <h6 class="fw-semibold">Tidak Ada Hasil Ditemukan</h6>
                <p class="small text-muted mb-0">Tidak ada santri yang cocok dengan kata kunci pencarian tersebut.</p>
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

// Client-side Instant Filter Search
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
