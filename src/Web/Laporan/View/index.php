<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array   $reportData   kampus → modul → entitas tree
 * @var \DateTime $dateFrom
 * @var \DateTime $dateTo
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Laporan Program Kerja');
$csrf = $this->getParameter('csrf');
?>

<meta name="csrf-token" content="<?= Html::encode($csrf) ?>">

<div class="crud-container">
    <!-- Header Section -->
    <div class="crud-header">
        <div>
            <h1 class="crud-title">
                <i class="ri-file-list-line"></i> Laporan Program Kerja
            </h1>
            <p class="crud-subtitle">
                Laporan komprehensif program kerja, dikelompokkan berdasarkan kampus, modul, dan entitas.
            </p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card" style="margin-bottom: 24px;">
        <h3 style="margin-top: 0; margin-bottom: 16px; font-weight: 600; color: var(--text-main);">Filter Laporan</h3>
        <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end; margin-bottom: 16px;">
            <div>
                <label for="dateFrom" style="display: block; font-weight: 500; margin-bottom: 6px; color: var(--text-main); font-size: 0.95rem;">
                    Tanggal Dari
                </label>
                <input type="date" class="form-control" id="dateFrom" name="date_from"
                       value="<?= $dateFrom->format('Y-m-d') ?>"
                       style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-main); color: var(--text-main); font-size: 0.95rem;">
            </div>
            <div>
                <label for="dateTo" style="display: block; font-weight: 500; margin-bottom: 6px; color: var(--text-main); font-size: 0.95rem;">
                    Tanggal Sampai
                </label>
                <input type="date" class="form-control" id="dateTo" name="date_to"
                       value="<?= $dateTo->format('Y-m-d') ?>"
                       style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-main); color: var(--text-main); font-size: 0.95rem;">
            </div>
            <div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="ri-search-line"></i> Filter
                </button>
            </div>
        </form>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <form method="POST" action="<?= $urlGenerator->generate('laporan/export-pdf') ?>" target="_blank" style="display: inline;">
                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                <input type="hidden" name="date_from" value="<?= $dateFrom->format('Y-m-d') ?>">
                <input type="hidden" name="date_to" value="<?= $dateTo->format('Y-m-d') ?>">
                <button type="submit" class="btn btn-secondary" title="Download as PDF">
                    <i class="ri-download-cloud-line"></i> Export PDF
                </button>
            </form>
            <button type="button" class="btn btn-secondary" onclick="printReport()" title="Print report">
                <i class="ri-printer-line"></i> Print
            </button>
        </div>
    </div>

    <!-- Report Content -->
    <div id="reportContent">

        <!-- Period info -->
        <div class="card" style="text-align: center; margin-bottom: 24px;">
            <h4 style="font-size: 1.1rem; margin-bottom: 8px; margin-top: 0; color: var(--text-main);">
                Periode: <strong><?= $dateFrom->format('d-m-Y') ?></strong> s/d <strong><?= $dateTo->format('d-m-Y') ?></strong>
            </h4>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">
                Tanggal Generate: <?= date('d-m-Y H:i:s') ?>
            </p>
        </div>

        <?php if (empty($reportData)): ?>
            <div class="alert alert-success">
                <i class="ri-information-line alert-icon"></i>
                <div>Tidak ada data untuk periode yang dipilih.</div>
            </div>
        <?php else: ?>
            <?php $campusCounter = 0; ?>
            <?php foreach ($reportData as $kode => $campusData): ?>
                <?php $campusCounter++; ?>

                <!-- ═══ CAMPUS SECTION ═══════════════════════════════════════ -->
                <div style="margin-bottom: 32px;">
                    <!-- Campus heading -->
                    <div style="
                        display: flex; align-items: center; gap: 12px;
                        background: var(--primary);
                        color: #fff;
                        padding: 12px 20px;
                        border-radius: 10px;
                        margin-bottom: 20px;
                    ">
                        <i class="ri-map-pin-2-fill" style="font-size: 1.3rem;"></i>
                        <div>
                            <div style="font-size: 0.72rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.8;">
                                Kampus <?= Html::encode($kode) ?>
                            </div>
                            <div style="font-size: 1.15rem; font-weight: 700;">
                                <?= Html::encode($campusData['nama']) ?>
                            </div>
                        </div>
                    </div>

                    <?php $modulCounter = 0; ?>
                    <?php foreach ($campusData['modul'] as $modulId => $modulData): ?>
                        <?php $modulCounter++; $modulLetter = chr(64 + $modulCounter); ?>

                        <!-- ── MODUL SECTION ─────────────────────────────────── -->
                        <div style="margin-bottom: 24px; padding-left: 16px;">
                            <h2 style="
                                font-size: 1.3rem; font-weight: 700;
                                color: var(--primary);
                                border-bottom: 2px solid var(--primary);
                                padding-bottom: 10px; margin-bottom: 16px; margin-top: 0;
                            ">
                                <i class="ri-building-2-line"></i>
                                <?= $modulLetter . '. ' . strtoupper(Html::encode($modulData['modul']->nama ?? 'Unknown')) ?>
                            </h2>

                            <?php $entitasCounter = 0; ?>
                            <?php foreach ($modulData['entitas'] as $entitasId => $entitasData): ?>
                                <?php $entitasCounter++; ?>

                                <!-- ── ENTITAS SECTION ─────────────────────────── -->
                                <div style="margin-left: 24px; margin-bottom: 24px; page-break-inside: avoid;">
                                    <h3 style="
                                        font-size: 1.1rem; font-weight: 600;
                                        color: var(--success);
                                        border-left: 4px solid var(--success);
                                        padding-left: 14px;
                                        margin-bottom: 16px; margin-top: 0;
                                    ">
                                        <i class="ri-organization-chart"></i>
                                        <?= $entitasCounter . '. ' . Html::encode($entitasData['entitas']->nama) ?>
                                        <span class="badge-campus" style="margin-left:8px; font-size:0.65rem;"><?= Html::encode($kode) ?></span>
                                    </h3>

                                    <div style="margin-left: 24px;">

                                        <!-- Hasil Usaha (Done) -->
                                        <?php if (!empty($entitasData['done'])): ?>
                                            <div style="margin-bottom: 20px; page-break-inside: avoid;">
                                                <h4 style="font-size: 1rem; font-weight: 600; color: var(--success); display: flex; align-items: center; gap: 8px; margin-bottom: 12px; margin-top: 0;">
                                                    <i class="ri-check-double-line"></i> Hasil Usaha
                                                </h4>
                                                <div style="margin-left: 20px;">
                                                    <?php foreach ($entitasData['done'] as $index => $task): ?>
                                                        <div style="background: rgba(16,185,129,.05); border-left: 3px solid var(--success); padding: 12px 14px; margin-bottom: 10px; border-radius: 4px; page-break-inside: avoid;">
                                                            <h5 style="margin-bottom: 6px; margin-top: 0; font-weight: 600; color: var(--text-main); font-size: 0.9rem;">
                                                                <?= $index + 1 ?>. <?= Html::encode($task->judul) ?>
                                                            </h5>
                                                            <?php if ($task->deskripsi): ?>
                                                                <p style="margin-bottom: 6px; margin-top: 0; color: var(--text-muted); font-size: 0.82rem;"><?= nl2br(Html::encode($task->deskripsi)) ?></p>
                                                            <?php endif; ?>
                                                            <?php if ($task->assignedUser): ?>
                                                                <small style="color: var(--text-muted); font-size: 0.78rem;">
                                                                    <i class="ri-user-line"></i> PIC: <?= Html::encode($task->assignedUser->namaAnggota ?? 'N/A') ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Kendala (Rejected & Pending) -->
                                        <?php if (!empty($entitasData['rejected']) || !empty($entitasData['pending'])): ?>
                                            <div style="margin-bottom: 20px; page-break-inside: avoid;">
                                                <h4 style="font-size: 1rem; font-weight: 600; color: var(--danger); display: flex; align-items: center; gap: 8px; margin-bottom: 12px; margin-top: 0;">
                                                    <i class="ri-alert-line"></i> Kendala
                                                </h4>
                                                <div style="margin-left: 20px;">
                                                    <?php $kendalaIndex = 1; ?>
                                                    <?php foreach ($entitasData['rejected'] as $task): ?>
                                                        <div style="background: rgba(239,68,68,.05); border-left: 3px solid var(--danger); padding: 12px 14px; margin-bottom: 10px; border-radius: 4px; page-break-inside: avoid;">
                                                            <div style="display: flex; justify-content: space-between; align-items: start; gap: 12px;">
                                                                <div style="flex: 1;">
                                                                    <h5 style="margin-bottom: 6px; margin-top: 0; font-weight: 600; color: var(--text-main); font-size: 0.9rem;">
                                                                        <?= $kendalaIndex++ ?>. <?= Html::encode($task->judul) ?>
                                                                    </h5>
                                                                    <?php if ($task->deskripsi): ?>
                                                                        <p style="margin-bottom: 0; margin-top: 0; color: var(--text-muted); font-size: 0.82rem;"><?= nl2br(Html::encode($task->deskripsi)) ?></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <span class="badge" style="background: var(--danger); color: white; white-space: nowrap; font-size: 0.7rem;">DITOLAK</span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                    <?php foreach ($entitasData['pending'] as $task): ?>
                                                        <div style="background: rgba(245,158,11,.05); border-left: 3px solid #f59e0b; padding: 12px 14px; margin-bottom: 10px; border-radius: 4px; page-break-inside: avoid;">
                                                            <div style="display: flex; justify-content: space-between; align-items: start; gap: 12px;">
                                                                <div style="flex: 1;">
                                                                    <h5 style="margin-bottom: 6px; margin-top: 0; font-weight: 600; color: var(--text-main); font-size: 0.9rem;">
                                                                        <?= $kendalaIndex++ ?>. <?= Html::encode($task->judul) ?>
                                                                    </h5>
                                                                    <?php if ($task->deskripsi): ?>
                                                                        <p style="margin-bottom: 0; margin-top: 0; color: var(--text-muted); font-size: 0.82rem;"><?= nl2br(Html::encode($task->deskripsi)) ?></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <span class="badge" style="background: #f59e0b; color: white; white-space: nowrap; font-size: 0.7rem;">TERTUNDA</span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Program Kerja Mendatang (Todo) -->
                                        <?php if (!empty($entitasData['todo'])): ?>
                                            <div style="margin-bottom: 20px; page-break-inside: avoid;">
                                                <h4 style="font-size: 1rem; font-weight: 600; color: var(--primary); display: flex; align-items: center; gap: 8px; margin-bottom: 12px; margin-top: 0;">
                                                    <i class="ri-task-line"></i> Program Kerja Mendatang
                                                </h4>
                                                <div style="margin-left: 20px;">
                                                    <?php foreach ($entitasData['todo'] as $index => $task): ?>
                                                        <div style="background: rgba(79,70,229,.05); border-left: 3px solid var(--primary); padding: 12px 14px; margin-bottom: 10px; border-radius: 4px; page-break-inside: avoid;">
                                                            <h5 style="margin-bottom: 6px; margin-top: 0; font-weight: 600; color: var(--text-main); font-size: 0.9rem;">
                                                                <?= $index + 1 ?>. <?= Html::encode($task->judul) ?>
                                                            </h5>
                                                            <?php if ($task->deskripsi): ?>
                                                                <p style="margin-bottom: 6px; margin-top: 0; color: var(--text-muted); font-size: 0.82rem;"><?= nl2br(Html::encode($task->deskripsi)) ?></p>
                                                            <?php endif; ?>
                                                            <div style="display: flex; gap: 14px; font-size: 0.78rem; color: var(--text-muted); flex-wrap: wrap;">
                                                                <?php if ($task->deadline): ?>
                                                                    <span><i class="ri-calendar-line"></i> Target: <?= $task->deadline->format('d-m-Y') ?></span>
                                                                <?php endif; ?>
                                                                <?php if ($task->progress > 0): ?>
                                                                    <span><i class="ri-progress-5-line"></i> Progress: <?= $task->progress ?>%</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                    </div>
                                </div><!-- /entitas -->
                            <?php endforeach; ?>
                        </div><!-- /modul -->
                    <?php endforeach; ?>
                </div><!-- /campus -->
            <?php endforeach; ?>
        <?php endif; ?>

    </div><!-- /reportContent -->
</div>

<script>
function printReport() {
    window.print();
}
</script>

<style>
@media print {
    .crud-header,
    .card:first-of-type { display: none; }
    .card { border: none; box-shadow: none; page-break-inside: avoid; }
    button, form { display: none !important; }
}
</style>
