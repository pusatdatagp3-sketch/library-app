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
?>

<div class="container-fluid py-3">
    <!-- Header Title & Quick Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">
                <i class="ri-book-read-line me-2 text-primary"></i>Dashboard Perpustakaan
            </h1>
            <p class="text-muted mb-0 small">
                Rekapitulasi statistik kunjungan santri & monitoring sirkulasi perpustakaan pesantren.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $urlGenerator->generate('perpustakaan/scan') ?>" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <i class="ri-barcode-box-line fs-5"></i>
                <span class="fw-semibold">Scan Barcode Kunjungan</span>
            </a>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row g-3 mb-4">
        <!-- Card Hari Ini -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #2563eb15 0%, #3b82f605 100%); border-left: 4px solid #2563eb !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold small" style="font-size: 0.75rem; letter-spacing: 0.5px;">Kunjungan Hari Ini</span>
                        <h2 class="display-6 fw-bold mb-0 text-primary mt-1"><?= number_format($totalHariIni) ?></h2>
                        <small class="text-muted"><i class="ri-calendar-check-line me-1"></i><?= date('d M Y') ?></small>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                        <i class="ri-user-follow-line fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Minggu Ini -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #10b98115 0%, #10b98105 100%); border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold small" style="font-size: 0.75rem; letter-spacing: 0.5px;">Kunjungan Minggu Ini</span>
                        <h2 class="display-6 fw-bold mb-0 text-success mt-1"><?= number_format($totalMingguIni) ?></h2>
                        <small class="text-muted"><i class="ri-history-line me-1"></i>Senin - Ahad</small>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                        <i class="ri-calendar-event-line fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Kelas Aktif -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #f59e0b15 0%, #f59e0b05 100%); border-left: 4px solid #f59e0b !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold small" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Kelas Aktif</span>
                        <h2 class="display-6 fw-bold mb-0 text-warning mt-1"><?= count($chartPerKelasMingguIni) ?></h2>
                        <small class="text-muted"><i class="ri-community-line me-1"></i>Tercatat berkunjung</small>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                        <i class="ri-book-mark-line fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Scanner Quick Link -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #6366f115 0%, #8b5cf605 100%); border-left: 4px solid #6366f1 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold small" style="font-size: 0.75rem; letter-spacing: 0.5px;">Mode Input</span>
                        <h5 class="fw-bold mb-1 text-indigo mt-1">Barcode USB</h5>
                        <a href="<?= $urlGenerator->generate('perpustakaan/scan') ?>" class="btn btn-sm btn-outline-primary py-1 px-2 mt-1">
                            Buka Scanner <i class="ri-arrow-right-line"></i>
                        </a>
                    </div>
                    <div class="bg-indigo bg-opacity-10 p-3 rounded-circle text-primary">
                        <i class="ri-qr-scan-2-line fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bar Chart Rekap Kunjungan per Kelas -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title fw-bold mb-0 text-dark">
                            <i class="ri-bar-chart-box-line me-2 text-primary"></i>Statistik Kunjungan per Kelas
                        </h5>
                        <span class="text-muted small">Rekapitulasi distribusi kehadiran santri berdasarkan tingkatan kelas</span>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">Minggu Ini</span>
                </div>
                <div class="card-body p-3">
                    <?php if (empty($labels)): ?>
                        <div class="text-center py-5">
                            <i class="ri-bar-chart-line text-muted fs-1 mb-2"></i>
                            <p class="text-muted mb-2">Belum ada rekaman kunjungan santri untuk minggu ini.</p>
                            <a href="<?= $urlGenerator->generate('perpustakaan/scan') ?>" class="btn btn-sm btn-primary">
                                <i class="ri-barcode-box-line me-1"></i>Mulai Scan Barcode
                            </a>
                        </div>
                    <?php else: ?>
                        <div style="position: relative; height: 320px; width: 100%;">
                            <canvas id="kunjunganKelasChart"></canvas>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ringkasan Kelas Terbanyak -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent border-0 pt-3 px-3">
                    <h5 class="card-title fw-bold mb-0 text-dark">
                        <i class="ri-trophy-line me-2 text-warning"></i>Kelas Teraktif
                    </h5>
                    <span class="text-muted small">Peringkat kelas dengan kunjungan terbanyak</span>
                </div>
                <div class="card-body p-3">
                    <?php if (empty($chartPerKelasMingguIni)): ?>
                        <p class="text-muted text-center py-4 mb-0 small">Belum ada data tersedia.</p>
                    <?php else: ?>
                        <?php
                            $sortedKelas = $chartPerKelasMingguIni;
                            arsort($sortedKelas);
                            $topKelas = array_slice($sortedKelas, 0, 5, true);
                            $rank = 1;
                        ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($topKelas as $kls => $jml): ?>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-circle <?= $rank === 1 ? 'bg-warning text-dark' : ($rank === 2 ? 'bg-secondary text-white' : 'bg-light text-muted border') ?>" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                                            <?= $rank++ ?>
                                        </span>
                                        <span class="fw-semibold text-dark">Kelas <?= Html::encode((string)$kls) ?></span>
                                    </div>
                                    <span class="badge bg-primary rounded-pill px-3 py-1"><?= (int)$jml ?> kunjungan</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Kunjungan Terbaru Hari Ini -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-transparent border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title fw-bold mb-0 text-dark">
                    <i class="ri-time-line me-2 text-primary"></i>Kunjungan Terbaru Hari Ini
                </h5>
                <span class="text-muted small">Daftar santri yang telah melakukan scan kunjungan</span>
            </div>
            <a href="<?= $urlGenerator->generate('perpustakaan/scan') ?>" class="btn btn-sm btn-outline-primary">
                <i class="ri-add-line me-1"></i>Scan Baru
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 50px;">No</th>
                            <th style="width: 110px;">Waktu</th>
                            <th style="width: 120px;">Stambuk</th>
                            <th>Nama Santri</th>
                            <th style="width: 100px;">Kelas</th>
                            <th>Rayon</th>
                            <th>Konsulat</th>
                            <th class="pe-3">Penginput</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($kunjunganTerbaru)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="ri-inbox-line fs-2 d-block mb-1 text-secondary"></i>
                                    Belum ada santri yang berkunjung hari ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($kunjunganTerbaru as $index => $item): ?>
                                <tr>
                                    <td class="ps-3 text-muted small"><?= $index + 1 ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= $item->waktu_kunjungan ? $item->waktu_kunjungan->format('H:i:s') : '-' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-monospace fw-semibold text-primary">
                                            <?= Html::encode((string)$item->stambuk) ?>
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <?= Html::encode((string)($item->nama_santri ?? '-')) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2">
                                            <?= Html::encode((string)($item->kelas ?? '-')) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= Html::encode((string)($item->rayon ?? '-')) ?></td>
                                    <td class="text-muted small"><?= Html::encode((string)($item->konsulat ?? '-')) ?></td>
                                    <td class="pe-3 text-muted small"><?= Html::encode((string)$item->penginput) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js dari CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('kunjunganKelasChart');
    if (!canvas) return;

    const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE) ?>;
    const dataValues = <?= json_encode($values) ?>;

    const ctx = canvas.getContext('2d');

    // Gradien modern untuk bar chart
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(37, 99, 235, 0.85)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.25)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Kunjungan',
                data: dataValues,
                backgroundColor: gradient,
                borderColor: '#2563eb',
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
                    backgroundColor: '#1e293b',
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
                        color: '#64748b'
                    },
                    grid: {
                        color: 'rgba(226, 232, 240, 0.6)'
                    }
                },
                x: {
                    ticks: {
                        color: '#64748b',
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
