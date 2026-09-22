<?php

declare(strict_types=1);

use App\Web\Perpustakaan\Model\KunjunganEntity;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var KunjunganEntity[] $kunjunganHariIni
 * @var array{Library: array, Staff: array} $groupedStaff
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 * @var string|null $currentPetugas
 * @var bool|null $hasStaffActive
 */

$this->setTitle('Scan Kunjungan Perpustakaan');
$totalHariIni = count($kunjunganHariIni);
$groupedStaff = $groupedStaff ?? ['Library' => [], 'Staff' => []];
$currentPetugas = $currentPetugas ?? null;
$hasStaffActive = $hasStaffActive ?? (!empty($currentPetugas));
?>

<!-- Bootstrap 5 CSS & JS CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
/* ── Scan Page Styles ───────────────────────────────────────────────────── */
.scan-page { display: flex; flex-direction: column; gap: 1.5rem; }

/* Scanner Card */
.scanner-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
}
.scanner-card-accent {
    height: 4px;
    background: linear-gradient(90deg, #8b5cf6 0%, #818cf8 100%);
}
.scanner-card-body {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.25rem;
}

/* Pulse Icon */
.scan-icon-wrap {
    position: relative;
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.scan-icon-wrap::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: #8b5cf6;
    opacity: 0.12;
    animation: pulse-ring 2s cubic-bezier(0.455, 0.03, 0.515, 0.955) infinite;
}
@keyframes pulse-ring {
    0%   { transform: scale(0.9); opacity: 0.15; }
    50%  { transform: scale(1.12); opacity: 0.07; }
    100% { transform: scale(0.9); opacity: 0.15; }
}
.scan-icon-inner {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #8b5cf6 0%, #818cf8 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: #fff;
    box-shadow: 0 4px 14px rgba(139, 92, 246, 0.4);
    position: relative;
    z-index: 1;
}

/* Scanner heading */
.scanner-heading {
    text-align: center;
}
.scanner-heading h2 {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--text-main, #0f172a);
    margin: 0 0 0.35rem 0;
    letter-spacing: -0.02em;
}
.scanner-heading p {
    font-size: 0.85rem;
    color: var(--text-muted, #64748b);
    margin: 0;
    line-height: 1.6;
}

/* Petugas Piket Bar */
.petugas-piket-box {
    width: 100%;
    max-width: 480px;
    background: var(--bg-hover, #f8fafc);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 12px;
    padding: 0.65rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    transition: background 0.2s ease, border-color 0.2s ease;
}
.petugas-piket-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-align: left;
}
.petugas-piket-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(139, 92, 246, 0.12);
    color: #8b5cf6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}
.petugas-piket-label {
    font-size: 0.72rem;
    color: var(--text-muted, #64748b);
    line-height: 1.1;
}
.petugas-piket-name {
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--text-main, #0f172a);
}

/* Input Scan */
.scan-input-wrapper {
    width: 100%;
    max-width: 480px;
}
.scan-input-group {
    display: flex;
    align-items: center;
    background: var(--bg-main, #ffffff);
    border: 2px solid var(--border, #cbd5e1);
    border-radius: 12px;
    overflow: hidden;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.scan-input-group:focus-within {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.18);
}
.scan-input-icon {
    padding: 0 1rem;
    color: var(--text-muted, #64748b);
    font-size: 1.35rem;
    flex-shrink: 0;
    line-height: 1;
}
#stambuk-input {
    flex: 1;
    border: none;
    background: transparent;
    color: var(--text-main, #0f172a);
    font-size: 1.35rem;
    font-weight: 700;
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    letter-spacing: 1.5px;
    padding: 0.9rem 0.5rem;
    outline: none;
    min-width: 0;
}
#stambuk-input::placeholder {
    color: var(--text-muted, #94a3b8);
    font-weight: 400;
    font-size: 1rem;
    letter-spacing: 0;
}
.scan-submit-btn {
    border: none;
    background: #8b5cf6;
    color: #fff;
    padding: 0.9rem 1.4rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: background 0.2s ease;
    flex-shrink: 0;
}
.scan-submit-btn:hover { background: #7c3aed; }
.scan-submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

/* Quick fill badges */
.quick-fill-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: center;
    font-size: 0.8rem;
    color: var(--text-muted, #64748b);
    width: 100%;
    max-width: 480px;
}
.quick-fill-badge {
    padding: 3px 10px;
    border-radius: 6px;
    background: var(--bg-hover, #f1f5f9);
    border: 1px solid var(--border, #cbd5e1);
    color: #8b5cf6;
    font-family: monospace;
    font-weight: 600;
    font-size: 0.8rem;
    cursor: pointer;
    transition: background 0.15s ease;
    text-decoration: none;
}
.quick-fill-badge:hover {
    background: rgba(139,92,246,0.1);
    border-color: #8b5cf6;
}

/* Alert feedback */
.scan-alert-container { width: 100%; max-width: 480px; min-height: 56px; }
.scan-alert {
    border-radius: 10px;
    padding: 0.85rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    animation: fadeSlideIn 0.25s ease;
}
@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.scan-alert-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.scan-alert-icon.success { background: rgba(34,197,94,0.15); color: #16a34a; }
.scan-alert-icon.error   { background: rgba(239,68,68,0.15);  color: #dc2626; }
.scan-alert-body h6 {
    font-size: 0.9rem;
    font-weight: 700;
    margin: 0 0 0.2rem 0;
    color: var(--text-main, #0f172a);
}
.scan-alert-body p {
    font-size: 0.8rem;
    color: var(--text-muted, #64748b);
    margin: 0;
    line-height: 1.5;
}
.scan-alert-meta {
    margin-left: auto;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.2rem;
}
.scan-alert-time {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted, #64748b);
    font-family: monospace;
}

/* Live Table */
.live-table-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
.live-table-header {
    padding: 1.1rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--border, #e2e8f0);
    gap: 1rem;
    flex-wrap: wrap;
}
.live-table-header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.live-table-header-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: rgba(34, 197, 94, 0.12);
    color: #16a34a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.live-table-header h3 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-main, #0f172a);
    margin: 0;
}
.live-table-header p {
    font-size: 0.78rem;
    color: var(--text-muted, #64748b);
    margin: 0.1rem 0 0 0;
}
.badge-total {
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
    border: 1px solid rgba(34, 197, 94, 0.25);
    border-radius: 20px;
    padding: 0.35rem 0.9rem;
    font-size: 0.85rem;
    font-weight: 700;
    white-space: nowrap;
}

/* Live table row animation */
@keyframes highlightNewRow {
    0%   { background-color: rgba(34, 197, 94, 0.15); }
    100% { background-color: transparent; }
}
.row-newly-scanned {
    animation: highlightNewRow 3s ease-out;
}

/* Empty state */
.empty-state {
    padding: 3rem 1rem;
    text-align: center;
    color: var(--text-muted, #64748b);
}
.empty-state i { font-size: 2.5rem; display: block; margin-bottom: 0.5rem; opacity: 0.4; }
.empty-state p { font-size: 0.9rem; margin: 0; }

/* Status badge */
.status-dot {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: #16a34a;
}
.status-dot::before {
    content: '';
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #16a34a;
    display: inline-block;
    animation: blink 1.4s infinite;
}
@keyframes blink {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.3; }
}

/* Penyesuaian Elemen Dark Mode */
[data-bs-theme="dark"] .petugas-piket-box,
body[data-theme="dark"] .petugas-piket-box {
    background: rgba(255, 255, 255, 0.04);
    border-color: #1f2937;
}
[data-bs-theme="dark"] .modal-content,
body[data-theme="dark"] .modal-content {
    background-color: #111827 !important;
    border-color: #1f2937 !important;
    color: #f1f5f9;
}
[data-bs-theme="dark"] .form-select,
body[data-theme="dark"] .form-select {
    background-color: #1f2937;
    border-color: #374151;
    color: #f1f5f9;
}
[data-bs-theme="dark"] .table,
body[data-theme="dark"] .table {
    --bs-table-bg: transparent !important;
    --bs-table-color: var(--text-main, #f8fafc) !important;
}
[data-bs-theme="dark"] .table th,
body[data-theme="dark"] .table th {
    background-color: var(--bg-hover, #1f2937) !important;
    color: var(--text-muted, #94a3b8) !important;
    border-bottom-color: var(--border, #1f2937) !important;
    box-shadow: none !important;
}
[data-bs-theme="dark"] .table td,
body[data-theme="dark"] .table td {
    background-color: transparent !important;
    color: var(--text-main, #f8fafc) !important;
    border-bottom-color: var(--border, #1f2937) !important;
    box-shadow: none !important;
}
</style>

<div class="scan-page">

    <!-- ── Page Header ──────────────────────────────────────────────────── -->
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Scan Kunjungan Perpustakaan</h1>
            <p class="crud-subtitle">Arahkan USB barcode scanner ke kartu santri. Kunjungan dicatat instan tanpa reload halaman.</p>
        </div>
        <div>
            <a href="<?= $urlGenerator->generate('home') ?>" class="btn btn-secondary">
                <i class="ri-dashboard-line" style="margin-right: 6px;"></i>Dashboard
            </a>
        </div>
    </div>

    <!-- ── Scanner Card ──────────────────────────────────────────────────── -->
    <div class="scanner-card">
        <div class="scanner-card-accent"></div>
        <div class="scanner-card-body">

            <!-- Animated Icon -->
            <div class="scan-icon-wrap">
                <div class="scan-icon-inner">
                    <i class="ri-qr-scan-2-line"></i>
                </div>
            </div>

            <!-- Heading & Description -->
            <div class="scanner-heading">
                <h2>Siap Memindai Barcode</h2>
                <p>
                    Gunakan USB Scanner atau ketik nomor stambuk lalu tekan
                    <kbd style="background: var(--bg-hover); border: 1px solid var(--border); border-radius: 5px; padding: 2px 7px; font-size: 0.8rem; color: var(--text-main);">Enter</kbd>
                </p>
                <div class="status-dot" style="margin-top: 0.5rem;">Input Aktif &amp; Siap</div>
            </div>

            <!-- On-Duty Staff Bar (Petugas Piket) -->
            <div class="petugas-piket-box">
                <div class="petugas-piket-info">
                    <span class="petugas-piket-icon"><i class="ri-user-star-line"></i></span>
                    <div>
                        <div class="petugas-piket-label">Petugas Piket Saat Ini:</div>
                        <strong class="petugas-piket-name" id="label-petugas-aktif">Memuat...</strong>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" id="btn-ganti-petugas">
                    <i class="ri-swap-line me-1"></i>Ganti Petugas
                </button>
            </div>

            <!-- Input Form -->
            <div class="scan-input-wrapper">
                <form id="barcode-scan-form" autocomplete="off">
                    <?php if (!empty($csrf)): ?>
                        <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>" id="csrf-token">
                    <?php endif; ?>
                    <div class="scan-input-group">
                        <span class="scan-input-icon"><i class="ri-barcode-line"></i></span>
                        <input
                            type="text"
                            id="stambuk-input"
                            name="stambuk"
                            placeholder="Scan / ketik stambuk..."
                            autofocus
                            required
                            spellcheck="false"
                            autocomplete="off"
                        >
                        <button type="submit" class="scan-submit-btn" id="btn-submit-scan">
                            <span class="spinner" id="scan-spinner" style="display:none; width:16px; height:16px; border:2px solid rgba(255,255,255,0.4); border-top-color:#fff; border-radius:50%; animation: spin 0.6s linear infinite;"></span>
                            <span id="btn-submit-text">Catat</span>
                            <i class="ri-arrow-right-line" id="btn-submit-icon"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Fill -->
            <div class="quick-fill-row">
                <span><i class="ri-flashlight-line"></i> Uji cepat:</span>
                <a href="javascript:void(0)" class="quick-fill-badge" onclick="quickFill('12345')">12345</a>
                <a href="javascript:void(0)" class="quick-fill-badge" onclick="quickFill('18001')">18001</a>
                <a href="javascript:void(0)" class="quick-fill-badge" onclick="quickFill('35412')">35412</a>
            </div>

            <!-- Alert Feedback Container -->
            <div class="scan-alert-container" id="scan-alert-container"></div>

        </div>
    </div>

    <!-- ── Live Kunjungan Table ───────────────────────────────────────────── -->
    <div class="live-table-card">
        <div class="live-table-header">
            <div class="live-table-header-left">
                <div class="live-table-header-icon">
                    <i class="ri-list-check-2"></i>
                </div>
                <div>
                    <h3>Kunjungan Terdata Hari Ini</h3>
                    <p>Data terupdate langsung saat barcode berhasil dipindai</p>
                </div>
            </div>
            <span class="badge-total" id="badge-total-kunjungan">
                <i class="ri-group-line" style="margin-right: 4px;"></i>
                <span id="badge-total-count"><?= $totalHariIni ?></span> Santri
            </span>
        </div>

        <div class="table-responsive">
            <table class="table" id="table-kunjungan-live">
                <thead>
                    <tr>
                        <th style="padding-left: 1.5rem; width: 95px;">Waktu</th>
                        <th style="width: 120px;">Stambuk</th>
                        <th>Nama Santri</th>
                        <th style="width: 100px;">Kelas</th>
                        <th>Rayon</th>
                        <th>Konsulat</th>
                        <th style="padding-right: 1.5rem;">Penginput</th>
                    </tr>
                </thead>
                <tbody id="kunjungan-tbody">
                    <?php if (empty($kunjunganHariIni)): ?>
                        <tr id="empty-row">
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="ri-inbox-2-line"></i>
                                    <p>Belum ada santri yang melakukan kunjungan hari ini.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($kunjunganHariIni as $item): ?>
                            <tr>
                                <td style="padding-left: 1.5rem;">
                                    <span style="font-family: monospace; font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">
                                        <?= $item->waktu_kunjungan ? $item->waktu_kunjungan->format('H:i:s') : '-' ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-family: monospace; font-weight: 700; color: #8b5cf6; font-size: 0.95rem;">
                                        <?= Html::encode((string)$item->stambuk) ?>
                                    </span>
                                </td>
                                <td style="font-weight: 600; color: var(--text-main);">
                                    <?= Html::encode((string)($item->nama_santri ?? '-')) ?>
                                </td>
                                <td>
                                    <span class="badge" style="background: rgba(139,92,246,0.1); color: #8b5cf6; border: 1px solid rgba(139,92,246,0.2); font-size: 0.78rem;">
                                        <?= Html::encode((string)($item->kelas ?? '-')) ?>
                                    </span>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.85rem;"><?= Html::encode((string)($item->rayon ?? '-')) ?></td>
                                <td style="color: var(--text-muted); font-size: 0.85rem;"><?= Html::encode((string)($item->konsulat ?? '-')) ?></td>
                                <td style="padding-right: 1.5rem; color: var(--text-muted); font-size: 0.85rem;">
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
        </div>
    </div>

</div><!-- /.scan-page -->

<!-- ── MODAL IDENTIFIKASI PETUGAS PIKET (GATEKEEPER MODAL SEPERTI DI AWDA) ── -->
<div class="modal fade" id="modalPetugas" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalPetugasTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                <span class="badge rounded-pill" style="background: rgba(139, 92, 246, 0.12); color: #8b5cf6; font-size: 0.75rem; font-weight: 600; padding: 5px 12px;">
                    <i class="ri-shield-user-line me-1"></i>Piket Perpustakaan
                </span>
                <button type="button" class="btn-close" id="btnModalClose" onclick="closePetugasModal()" style="display: none;" aria-label="Tutup"></button>
            </div>
            <form id="formPetugasPiket" autocomplete="off">
                <div class="modal-body px-4 pt-2 pb-4 text-center">
                    <!-- User Icon Badge -->
                    <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-circle shadow-sm" style="width: 68px; height: 68px; background: linear-gradient(135deg, rgba(139, 92, 246, 0.15) 0%, rgba(129, 140, 248, 0.2) 100%); color: #8b5cf6;">
                        <i class="ri-user-star-line" style="font-size: 2.2rem;"></i>
                    </div>

                    <h4 class="fw-bold mb-1 text-body-emphasis" id="modalPetugasTitle">Siapa yang Sedang Bertugas?</h4>
                    <p class="text-secondary small mb-4" id="modalPetugasSubtitle">
                        Pilih nama Anda dari daftar staf untuk mencatat riwayat transaksi kunjungan santri dengan akurat.
                    </p>

                    <div class="mb-3 text-start">
                        <label for="selectPetugas" class="form-label small fw-semibold text-secondary">
                            Pilih Staf / Petugas Piket <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary border-end-0" style="color: var(--text-muted);">
                                <i class="ri-user-line"></i>
                            </span>
                            <select id="selectPetugas" name="nama_petugas" class="form-select form-select-lg rounded-end-3 border-start-0" required style="font-size: 0.95rem;">
                                <option value="" selected disabled>-- Pilih Nama Petugas Piket --</option>
                                <?php if (!empty($groupedStaff['Library'])): ?>
                                    <optgroup label="📚 Sektor (Ustadzah Perpustakaan)">
                                        <?php foreach ($groupedStaff['Library'] as $staf): ?>
                                            <?php $namaStaf = is_array($staf) ? ($staf['nama_staf'] ?? '') : (string)($staf->nama_staf ?? ''); ?>
                                            <?php if ($namaStaf !== ''): ?>
                                                <option value="<?= Html::encode($namaStaf) ?>" <?= ($currentPetugas === $namaStaf) ? 'selected' : '' ?>><?= Html::encode($namaStaf) ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                                <?php if (!empty($groupedStaff['Staff'])): ?>
                                    <optgroup label="📖 OPPM (Bagian Perpustakaan)">
                                        <?php foreach ($groupedStaff['Staff'] as $staf): ?>
                                            <?php $namaStaf = is_array($staf) ? ($staf['nama_staf'] ?? '') : (string)($staf->nama_staf ?? ''); ?>
                                            <?php if ($namaStaf !== ''): ?>
                                                <option value="<?= Html::encode($namaStaf) ?>" <?= ($currentPetugas === $namaStaf) ? 'selected' : '' ?>><?= Html::encode($namaStaf) ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-text text-muted small mt-1.5" style="font-size: 0.76rem;">
                            Data bersumber dari modul Manajemen Staf dan dikelompokkan berdasarkan divisi.
                        </div>
                        <div class="invalid-feedback" id="selectPetugasFeedback">
                            Silakan pilih nama petugas terlebih dahulu sebelum memindai.
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-2">
                        <button type="button" class="btn btn-secondary px-3 py-2.5 rounded-3 fw-semibold" id="btnBatalPetugas" onclick="closePetugasModal()" style="display: none;">
                            Batal
                        </button>
                        <button id="btnMulaiBertugas" type="submit" class="btn btn-primary bg-kutubia border-0 w-100 py-2.5 rounded-3 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2" style="background-color: #8b5cf6 !important;">
                            <span class="spinner-border spinner-border-sm" id="spinnerPetugas" style="display: none;" role="status" aria-hidden="true"></span>
                            <i class="ri-check-line fs-5" id="iconPetugas"></i>
                            <span id="btnSubmitPetugasText">Mulai Bertugas</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<script>
// ── Audio Feedback via Web Audio API ──────────────────────────────────────
const AudioFeedback = {
    ctx: null,
    init() {
        if (!this.ctx) {
            const AC = window.AudioContext || window.webkitAudioContext;
            if (AC) this.ctx = new AC();
        }
    },
    play(freq1, freq2, type, duration, vol) {
        this.init();
        if (!this.ctx) return;
        const now = this.ctx.currentTime;
        const osc  = this.ctx.createOscillator();
        const gain = this.ctx.createGain();
        osc.connect(gain);
        gain.connect(this.ctx.destination);
        osc.type = type;
        osc.frequency.setValueAtTime(freq1, now);
        if (freq2) osc.frequency.exponentialRampToValueAtTime(freq2, now + duration * 0.5);
        gain.gain.setValueAtTime(vol, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + duration);
        osc.start(now);
        osc.stop(now + duration);
    },
    success() { this.play(880, 1174.66, 'sine',     0.25, 0.3); },
    error()   { this.play(220, null,     'sawtooth', 0.35, 0.4); }
};

function escapeHtml(text) {
    if (text == null) return '-';
    return text.toString()
        .replace(/&/g,  '&amp;')
        .replace(/</g,  '&lt;')
        .replace(/>/g,  '&gt;')
        .replace(/"/g,  '&quot;')
        .replace(/'/g,  '&#039;');
}

function quickFill(stambuk) {
    const input = document.getElementById('stambuk-input');
    if (!input) return;
    input.value = stambuk;
    input.focus();
    document.getElementById('barcode-scan-form')
        .dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
}

document.addEventListener('DOMContentLoaded', function () {
    const form              = document.getElementById('barcode-scan-form');
    const input             = document.getElementById('stambuk-input');
    const alertBox          = document.getElementById('scan-alert-container');
    const tbody             = document.getElementById('kunjungan-tbody');
    const badgeCount        = document.getElementById('badge-total-count');
    const spinner           = document.getElementById('scan-spinner');
    const btnText           = document.getElementById('btn-submit-text');
    const btnIcon           = document.getElementById('btn-submit-icon');
    const btn               = document.getElementById('btn-submit-scan');
    const csrfInput         = document.getElementById('csrf-token');

    // ── Bersihkan localStorage legacy agar tidak membypass sesi login ────
    try {
        localStorage.removeItem('petugas_piket');
    } catch (e) {}

    // State sesi dari server
    let hasStaffActive  = <?= json_encode($hasStaffActive) ?>;
    let currentStaff    = <?= json_encode($currentPetugas ?? '') ?>;
    let isSwitchingMode = false;

    const setStaffUrl    = '<?= $urlGenerator->generate('perpustakaan/set-staff') ?>';
    const switchStaffUrl = '<?= $urlGenerator->generate('perpustakaan/switch-staff') ?>';
    const scanUrl        = '<?= $urlGenerator->generate('perpustakaan/scan') ?>';

    // ── Elemen Petugas Piket & Modal ──────────────────────────────────────
    const modalEl           = document.getElementById('modalPetugas');
    const modalPetugas      = modalEl && typeof bootstrap !== 'undefined' ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    const formPetugas       = document.getElementById('formPetugasPiket');
    const selectPetugas     = document.getElementById('selectPetugas');
    const btnModalClose     = document.getElementById('btnModalClose');
    const btnBatalPetugas   = document.getElementById('btnBatalPetugas');
    const modalTitle        = document.getElementById('modalPetugasTitle');
    const modalSubtitle     = document.getElementById('modalPetugasSubtitle');
    const btnSubmitPetugas  = document.getElementById('btnMulaiBertugas');
    const btnSubmitText     = document.getElementById('btnSubmitPetugasText');
    const spinnerPetugas    = document.getElementById('spinnerPetugas');
    const iconPetugas       = document.getElementById('iconPetugas');
    const btnGantiPetugas   = document.getElementById('btn-ganti-petugas');
    const labelPetugasAktif = document.getElementById('label-petugas-aktif');

    let total = <?= $totalHariIni ?>;

    function updatePetugasUI(name) {
        currentStaff   = (name || '').trim();
        hasStaffActive = Boolean(currentStaff);

        if (labelPetugasAktif) {
            labelPetugasAktif.textContent = currentStaff || 'Belum Dipilih';
        }
        if (selectPetugas && currentStaff) {
            selectPetugas.value = currentStaff;
        }
    }

    window.openPetugasModal = function(isSwitching = false) {
        isSwitchingMode = isSwitching;

        if (isSwitching && hasStaffActive) {
            if (btnModalClose) btnModalClose.style.display = 'inline-block';
            if (btnBatalPetugas) btnBatalPetugas.style.display = 'inline-block';
            if (modalTitle) modalTitle.textContent = 'Ganti Petugas Piket';
            if (modalSubtitle) modalSubtitle.textContent = 'Pilih staf baru yang akan menggantikan tugas piket perpustakaan.';
            if (btnSubmitText) btnSubmitText.textContent = 'Simpan & Ganti Petugas';
        } else {
            if (btnModalClose) btnModalClose.style.display = 'none';
            if (btnBatalPetugas) btnBatalPetugas.style.display = 'none';
            if (modalTitle) modalTitle.textContent = 'Siapa yang Sedang Bertugas?';
            if (modalSubtitle) modalSubtitle.textContent = 'Pilih nama Anda dari daftar staf untuk mencatat riwayat transaksi kunjungan santri dengan akurat.';
            if (btnSubmitText) btnSubmitText.textContent = 'Mulai Bertugas';
        }

        if (selectPetugas) {
            selectPetugas.classList.remove('is-invalid');
            if (currentStaff) {
                selectPetugas.value = currentStaff;
            }
        }

        if (modalPetugas) {
            modalPetugas.show();
        }

        setTimeout(() => {
            if (selectPetugas) selectPetugas.focus();
        }, 250);
    };

    window.closePetugasModal = function() {
        if (isSwitchingMode && hasStaffActive && modalPetugas) {
            modalPetugas.hide();
            setTimeout(() => {
                if (input) input.focus();
            }, 300);
        }
    };

    // Handler form penetapan petugas piket (AJAX ke set-staff)
    if (formPetugas) {
        formPetugas.addEventListener('submit', function (e) {
            e.preventDefault();
            const selected = selectPetugas ? selectPetugas.value.trim() : '';
            if (!selected) {
                if (selectPetugas) selectPetugas.classList.add('is-invalid');
                return;
            }
            if (selectPetugas) selectPetugas.classList.remove('is-invalid');

            // Loading state tombol modal
            if (btnSubmitPetugas) btnSubmitPetugas.disabled = true;
            if (spinnerPetugas) spinnerPetugas.style.display = 'inline-block';
            if (iconPetugas) iconPetugas.style.display = 'none';

            const payload = new URLSearchParams({ nama_petugas: selected });
            if (csrfInput?.value) payload.append('_csrf', csrfInput.value);

            fetch(setStaffUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload.toString()
            })
            .then(res => res.json().catch(() => ({ success: false, message: 'Format respon server tidak valid.' })))
            .then(data => {
                if (data.success) {
                    const activeName = data.staff_name || selected;
                    updatePetugasUI(activeName);
                    isSwitchingMode = false;
                    if (modalPetugas) {
                        modalPetugas.hide();
                    }
                    setTimeout(() => {
                        if (input) input.focus();
                    }, 350);
                } else {
                    alert(data.message || 'Gagal menyimpan petugas piket.');
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan komunikasi server: ' + err.message);
            })
            .finally(() => {
                if (btnSubmitPetugas) btnSubmitPetugas.disabled = false;
                if (spinnerPetugas) spinnerPetugas.style.display = 'none';
                if (iconPetugas) iconPetugas.style.display = 'inline-block';
            });
        });
    }

    // Handler tombol Ganti Petugas di kartu scanner
    if (btnGantiPetugas) {
        btnGantiPetugas.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openPetugasModal(true);
        });
    }

    // Modal dismiss preventer saat mode wajib (gatekeeper)
    if (modalEl) {
        modalEl.addEventListener('hide.bs.modal', function (e) {
            if (!isSwitchingMode && !hasStaffActive) {
                e.preventDefault();
            }
        });
    }

    // ── Inisialisasi status awal saat halaman dimuat ──────────────────────
    if (!hasStaffActive) {
        updatePetugasUI('');
        openPetugasModal(false);
    } else {
        updatePetugasUI(currentStaff);
    }

    // ── Auto-focus: selalu fokus kecuali klik tombol/link/input/modal ─────
    input.focus();
    document.addEventListener('click', function (e) {
        if (document.body.classList.contains('modal-open') || (modalEl && modalEl.classList.contains('show'))) {
            return;
        }
        if (!e.target.closest('a, button, input, select, textarea, .modal')) {
            input.focus();
        }
    });

    // ── Submit handler pemindaian barcode ─────────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const stambuk = input.value.trim();
        if (!stambuk) return;

        // Gatekeeper: jika belum ada petugas aktif, langsung buka modal
        if (!hasStaffActive || !currentStaff) {
            openPetugasModal(false);
            return;
        }

        // Loading state
        btn.disabled          = true;
        spinner.style.display = 'inline-block';
        btnText.textContent   = 'Mencatat…';
        btnIcon.style.display = 'none';

        const payload = new URLSearchParams({
            stambuk: stambuk,
            petugas_piket: currentStaff
        });
        if (csrfInput?.value) payload.append('_csrf', csrfInput.value);

        fetch(scanUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: payload.toString()
        })
        .then(async res => {
            const data = await res.json().catch(() => ({
                success: false,
                message: 'Format respon server tidak valid.'
            }));
            return { ok: res.ok, data };
        })
        .then(({ ok, data }) => {
            if (data.require_staff) {
                AudioFeedback.error();
                hasStaffActive = false;
                currentStaff = '';
                updatePetugasUI('');
                openPetugasModal(false);
                return;
            }

            if (data.success && data.data) {
                AudioFeedback.success();

                const s = data.data;

                // ── Alert sukses ────────────────────────────────────
                alertBox.innerHTML = `
                    <div class="scan-alert" style="background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.25);">
                        <div class="scan-alert-icon success"><i class="ri-check-double-line"></i></div>
                        <div class="scan-alert-body" style="flex: 1; min-width: 0;">
                            <h6>${escapeHtml(s.nama)}</h6>
                            <p>
                                Stambuk: <strong style="font-family:monospace;">${escapeHtml(s.stambuk)}</strong>
                                &bull; Kelas: <strong>${escapeHtml(s.kelas)}</strong>
                                &bull; Rayon: ${escapeHtml(s.rayon)}
                                ${s.konsulat ? '&bull; Konsulat: ' + escapeHtml(s.konsulat) : ''}
                                &bull; Petugas: <strong>${escapeHtml(s.penginput)}</strong>
                            </p>
                        </div>
                        <div class="scan-alert-meta">
                            <span class="scan-alert-time">${escapeHtml(s.waktu)}</span>
                            <span style="font-size:0.7rem; color: #16a34a; font-weight:600;">✓ Tercatat</span>
                        </div>
                    </div>`;

                // ── Hapus empty state placeholder ───────────────────
                const emptyRow = document.getElementById('empty-row');
                if (emptyRow) emptyRow.remove();

                // ── Tambahkan baris baru di atas tabel ──────────────
                const tr = document.createElement('tr');
                tr.className = 'row-newly-scanned';
                tr.innerHTML = `
                    <td style="padding-left:1.5rem;">
                        <span style="font-family:monospace;font-size:0.85rem;font-weight:600;color:var(--text-muted);">${escapeHtml(s.waktu)}</span>
                    </td>
                    <td>
                        <span style="font-family:monospace;font-weight:700;color:#8b5cf6;font-size:0.95rem;">${escapeHtml(s.stambuk)}</span>
                    </td>
                    <td style="font-weight:600;color:var(--text-main);">${escapeHtml(s.nama)}</td>
                    <td>
                        <span class="badge" style="background:rgba(139,92,246,0.1);color:#8b5cf6;border:1px solid rgba(139,92,246,0.2);font-size:0.78rem;">${escapeHtml(s.kelas)}</span>
                    </td>
                    <td style="color:var(--text-muted);font-size:0.85rem;">${escapeHtml(s.rayon || '-')}</td>
                    <td style="color:var(--text-muted);font-size:0.85rem;">${escapeHtml(s.konsulat || '-')}</td>
                    <td style="padding-right:1.5rem;color:var(--text-muted);font-size:0.85rem;">
                        <span class="badge bg-body-secondary text-body border px-2 py-1 small fw-normal d-inline-flex align-items-center gap-1">
                            <i class="ri-user-line text-secondary"></i>${escapeHtml(s.penginput)}
                        </span>
                    </td>
                `;
                tbody.insertBefore(tr, tbody.firstChild);

                // ── Update badge counter ─────────────────────────────
                total++;
                badgeCount.textContent = total;

            } else {
                AudioFeedback.error();

                alertBox.innerHTML = `
                    <div class="scan-alert" style="background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.2);">
                        <div class="scan-alert-icon error"><i class="ri-error-warning-line"></i></div>
                        <div class="scan-alert-body">
                            <h6>Barcode Tidak Dikenali</h6>
                            <p>${escapeHtml(data.message || 'Santri tidak ditemukan dalam database.')}</p>
                        </div>
                    </div>`;
            }
        })
        .catch(err => {
            AudioFeedback.error();
            alertBox.innerHTML = `
                <div class="scan-alert" style="background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.2);">
                    <div class="scan-alert-icon error"><i class="ri-wifi-off-line"></i></div>
                    <div class="scan-alert-body">
                        <h6>Kegagalan Jaringan</h6>
                        <p>${escapeHtml(err.message)}</p>
                    </div>
                </div>`;
        })
        .finally(() => {
            btn.disabled          = false;
            spinner.style.display = 'none';
            btnText.textContent   = 'Catat';
            btnIcon.style.display = '';
            input.value           = '';
            input.focus();
        });
    });
});
</script>
