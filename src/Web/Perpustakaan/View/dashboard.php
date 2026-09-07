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
 * @var array<string, int> $chartPerKelasMingguIni
 * @var array<string, int> $chartPerKelasHariIni
 * @var KunjunganEntity[] $kunjunganTerbaru
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Dashboard Perpustakaan');

// Siapkan data JSON untuk Chart.js
$labels = array_keys($chartPerKelasMingguIni);
$values = array_values($chartPerKelasMingguIni);

// Total kelas aktif
$totalKelasAktif = count($chartPerKelasMingguIni);

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

<!-- Bootstrap 5 CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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

    <!-- 2. KARTU METRIK BERJEJER (BARIS PERTAMA) - WARNA ANALOGOUS LEMBUT / PASTEL -->
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
                    <p class="text-body-secondary small mb-0">Total Kelas Aktif</p>
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

    <!-- 3. GRAFIK BATANG & TABEL (BARIS KEDUA) -->
    <div class="row g-4">
        <!-- Kolom Kiri: Grafik (75% lebar di desktop) -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title fw-bold mb-0 text-body-emphasis">
                            <i class="ri-bar-chart-box-line text-k-purple me-2"></i>Grafik Kunjungan per Kelas
                        </h5>
                        <p class="text-body-secondary small mb-0">Statistik volume santri berkunjung berdasarkan kelas minggu ini</p>
                    </div>
                    <span class="badge bg-k-purple-subtle text-k-purple px-3 py-2 rounded-pill fw-semibold">Minggu Ini</span>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($labels)): ?>
                        <div class="text-center py-5 text-body-secondary">
                            <i class="ri-bar-chart-line fs-1 mb-2 d-block"></i>
                            <p class="mb-2">Belum ada rekaman kunjungan santri untuk minggu ini.</p>
                            <a href="<?= $urlGenerator->generate('perpustakaan/scan') ?>" class="btn btn-sm bg-kutubia text-white">
                                <i class="ri-barcode-box-line me-1"></i>Mulai Scan Barcode
                            </a>
                        </div>
                    <?php else: ?>
                        <div style="position: relative; height: 350px; width: 100%;">
                            <canvas id="kunjunganChart"></canvas>
                        </div>
                    <?php endif; ?>
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
                    <p class="text-body-secondary small mb-3">Mulai proses pencatatan presensi atau segarkan data.</p>
                    <a href="<?= $urlGenerator->generate('perpustakaan/scan') ?>" class="btn bg-kutubia text-white w-100 py-2 d-flex align-items-center justify-content-center gap-2 mb-2 rounded-3 shadow-sm">
                        <i class="ri-barcode-box-line fs-5"></i>
                        <span class="fw-semibold">Buka Scanner Barcode</span>
                    </a>
                    <a href="<?= $urlGenerator->generate('perpustakaan/dashboard') ?>" class="btn btn-outline-kutubia w-100 py-2 d-flex align-items-center justify-content-center gap-2 rounded-3">
                        <i class="ri-refresh-line"></i>
                        <span>Segarkan Data</span>
                    </a>
                </div>
            </div>

            <!-- Kartu Pengunjung Terbaru -->
            <div class="card border-0 shadow-sm rounded-4 flex-grow-1">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                    <h6 class="card-title fw-bold mb-0 text-body-emphasis">
                        <i class="ri-time-line text-k-purple me-2"></i>Pengunjung Terbaru
                    </h6>
                    <span class="badge bg-k-purple-subtle text-k-purple border border-k-purple border-opacity-25">Hari Ini</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($kunjunganTerbaru)): ?>
                        <div class="text-center py-4 text-body-secondary small">
                            <i class="ri-inbox-line fs-3 d-block mb-1"></i>
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

    // Pantau perubahan atribut data-theme saat tombol tema di navbar diklik
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
    const canvas = document.getElementById('kunjunganChart') || document.getElementById('kunjunganKelasChart');
    if (!canvas) return;

    const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE) ?>;
    const dataValues = <?= json_encode($values) ?>;

    const ctx = canvas.getContext('2d');
    const isDarkMode = (document.body.getAttribute('data-theme') || localStorage.getItem('theme')) === 'dark';
    const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
    const textColor = isDarkMode ? '#94a3b8' : '#6c757d';

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Kunjungan',
                data: dataValues,
                backgroundColor: isDarkMode ? 'rgba(167, 139, 250, 0.75)' : 'rgba(139, 92, 246, 0.75)',
                borderColor: isDarkMode ? '#a78bfa' : '#8b5cf6',
                borderWidth: 1.5,
                borderRadius: 6,
                borderSkipped: false,
                maxBarThickness: 45
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
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
                            return ' ' + context.parsed.y + ' santri berkunjung';
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
});
</script>
