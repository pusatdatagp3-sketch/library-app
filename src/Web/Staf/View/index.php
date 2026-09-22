<?php

declare(strict_types=1);

use App\Web\Staf\Model\StafEntity;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var StafEntity[] $staffList
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $successMsg
 * @var array<string> $errorMsgs
 * @var string|null $csrf
 */

$this->setTitle('Manajemen Staf & Petugas Perpustakaan');

// Hitung statistik ringkas
$totalStaf = count($staffList);
$totalLibrary = 0;
$totalStaff = 0;
$totalAktif = 0;

foreach ($staffList as $staf) {
    if ($staf->divisi === 'Library') {
        $totalLibrary++;
    } else {
        $totalStaff++;
    }
    if ($staf->is_active) {
        $totalAktif++;
    }
}
?>

<!-- Bootstrap 5 CSS & JS CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="container-fluid p-0">

    <!-- ── HEADER & BREADCRUMB ────────────────────────────────────────────────── -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="<?= $urlGenerator->generate('home') ?>" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Sistem</li>
                    <li class="breadcrumb-item active" aria-current="page">Manajemen Staf</li>
                </ol>
            </nav>
            <h2 class="h4 fw-bold text-body mb-1 d-flex align-items-center gap-2">
                <i class="ri-team-line text-primary"></i> Manajemen Staf Perpustakaan
            </h2>
            <p class="text-muted small mb-0">Kelola daftar petugas piket dan divisi staf untuk pencatatan presensi scanner.</p>
        </div>
        <div class="d-flex justify-content-md-end gap-2 align-items-center mb-3 mb-md-0">
            <a href="<?= $urlGenerator->generate('perpustakaan/scan') ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="ri-barcode-box-line"></i> Ke Halaman Scan
            </a>
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahStaf">
                <i class="ri-add-line"></i> Tambah Staf
            </button>
        </div>
    </div>

    <!-- ── FLASH NOTIFICATIONS ───────────────────────────────────────────────── -->
    <?php if (!empty($successMsg)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="ri-checkbox-circle-line me-2"></i><?= Html::encode($successMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMsgs)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="ri-error-warning-line"></i> <strong>Pemberitahuan:</strong>
            </div>
            <ul class="mb-0 ps-3">
                <?php foreach ($errorMsgs as $msg): ?>
                    <li><?= Html::encode($msg) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- ── SUMMARY KPI CARDS (HORIZONTAL GRID) ─────────────────────────────────── -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total Staf -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Total Staf</p>
                        <h3 class="fw-bold mb-0"><?= $totalStaf ?></h3>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="ri-team-line fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Sektor (Ustadzah) -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Sektor (Ustadzah)</p>
                        <h3 class="fw-bold mb-0"><?= $totalLibrary ?></h3>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="ri-book-read-line fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: OPPM (Santriwati) -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">OPPM (Santriwati)</p>
                        <h3 class="fw-bold mb-0"><?= $totalStaff ?></h3>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="ri-book-2-line fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Staf Aktif -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Staf Aktif</p>
                        <h3 class="fw-bold mb-0 text-success"><?= $totalAktif ?></h3>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="ri-shield-check-line fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── DATA TABLE CARD ───────────────────────────────────────────────────── -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-transparent py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="ri-list-check-2 text-primary"></i>
                <h5 class="card-title mb-0 fw-bold">Daftar Staf & Petugas Piket</h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width: 250px;">
                    <span class="input-group-text bg-body-secondary border-end-0"><i class="ri-search-line text-muted"></i></span>
                    <input type="text" id="filter-staf-input" class="form-control border-start-0 bg-body" placeholder="Cari staf...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="table-staf">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 60px;">No</th>
                        <th>Nama Staf</th>
                        <th style="width: 160px;">Divisi</th>
                        <th style="width: 140px;">Status Piket</th>
                        <th class="text-center" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($staffList)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="ri-inbox-line fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                Belum ada data staf. Klik <strong>Tambah Staf</strong> untuk mendaftarkan staf baru.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($staffList as $staf): ?>
                            <tr class="staf-row" data-name="<?= strtolower(Html::encode($staf->nama_staf)) ?>" data-divisi="<?= strtolower(Html::encode($staf->divisi)) ?>">
                                <td class="ps-4 fw-semibold text-muted"><?= $no++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                                             style="width: 38px; height: 38px; background: <?= $staf->divisi === 'Library' ? 'linear-gradient(135deg, #8b5cf6, #7c3aed)' : 'linear-gradient(135deg, #0ea5e9, #0284c7)' ?>;">
                                            <?= strtoupper(substr(trim($staf->nama_staf), 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-body"><?= Html::encode($staf->nama_staf) ?></div>
                                            <div class="text-muted small">ID Staf: #<?= $staf->id ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($staf->divisi === 'Library'): ?>
                                        <span class="badge" style="background: rgba(139,92,246,0.12); color: #7c3aed; border: 1px solid rgba(139,92,246,0.25); font-size: 0.8rem; padding: 0.4rem 0.65rem;">
                                             <i class="ri-book-line me-1"></i> Sektor
                                         </span>
                                     <?php else: ?>
                                         <span class="badge" style="background: rgba(14,165,233,0.12); color: #0284c7; border: 1px solid rgba(14,165,233,0.25); font-size: 0.8rem; padding: 0.4rem 0.65rem;">
                                             <i class="ri-book-2-line me-1"></i> OPPM
                                         </span>
                                     <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($staf->is_active): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small d-inline-flex align-items-center gap-1">
                                            <i class="ri-check-line"></i> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 small d-inline-flex align-items-center gap-1">
                                            <i class="ri-close-line"></i> Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <!-- Toggle Status Button -->
                                        <form method="POST" action="<?= $urlGenerator->generate('staf/toggle', ['id' => $staf->id]) ?>" class="d-inline">
                                            <?php if (!empty($csrf)): ?>
                                                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                                            <?php endif; ?>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="<?= $staf->is_active ? 'Nonaktifkan Staf' : 'Aktifkan Staf' ?>">
                                                <i class="<?= $staf->is_active ? 'ri-toggle-fill text-success' : 'ri-toggle-line text-muted' ?>"></i>
                                            </button>
                                        </form>

                                        <!-- Edit Button -->
                                        <a href="<?= $urlGenerator->generate('staf/update', ['id' => $staf->id]) ?>" class="btn btn-sm btn-outline-primary" title="Ubah Data">
                                            <i class="ri-edit-line"></i>
                                        </a>

                                        <!-- Delete Button with Confirmation -->
                                        <form method="POST" action="<?= $urlGenerator->generate('staf/delete', ['id' => $staf->id]) ?>" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus staf <?= htmlspecialchars($staf->nama_staf, ENT_QUOTES) ?>?');">
                                            <?php if (!empty($csrf)): ?>
                                                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                                            <?php endif; ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Staf">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── MODAL TAMBAH STAF ──────────────────────────────────────────────────── -->
<div class="modal fade" id="modalTambahStaf" tabindex="-1" aria-labelledby="modalTambahStafLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white border-0 py-3 px-4" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important;">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalTambahStafLabel">
                    <i class="ri-user-add-line"></i> Tambah Staf Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Form -->
            <form action="<?= $urlGenerator->generate('staf/create/post') ?>" method="POST">
                <?php if (!empty($csrf)): ?>
                    <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                <?php endif; ?>

                <div class="modal-body p-4">
                    <!-- Nama Staf -->
                    <div class="mb-3">
                        <label for="modal_nama_staf" class="form-label fw-semibold small text-secondary">
                            Nama Lengkap Staf <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-body-secondary"><i class="ri-user-line text-muted"></i></span>
                            <input
                                type="text"
                                id="modal_nama_staf"
                                name="nama_staf"
                                class="form-control"
                                placeholder="Contoh: Ust. Ahmad Fauzi, S.Pd.I"
                                required
                            >
                        </div>
                        <div class="form-text small">Nama staf yang akan muncul di daftar petugas piket scanner.</div>
                    </div>

                    <!-- Divisi -->
                    <div class="mb-3">
                        <label for="modal_divisi" class="form-label fw-semibold small text-secondary">
                            Divisi Penugasan <span class="text-danger">*</span>
                        </label>
                        <select id="modal_divisi" name="divisi" class="form-select" required>
                            <option value="Library" selected>📚 Sektor (Ustadzah Perpustakaan)</option>
                            <option value="Staff">📖 OPPM (Bagian Perpustakaan)</option>
                        </select>
                        <div class="form-text small">Menentukan kelompok (optgroup) pada pop-up scanner kartu santri.</div>
                    </div>

                    <!-- Status Keaktifan -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold small text-secondary d-block">Status Keaktifan</label>
                        <div class="form-check form-switch">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="modal_is_active"
                                name="is_active"
                                value="1"
                                checked
                            >
                            <label class="form-check-label small" for="modal_is_active">
                                Aktifkan staf ini (Dapat dipilih saat shift piket perpustakaan)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-transparent border-top py-3 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important; border: none;">
                        <i class="ri-save-line me-1"></i> Simpan Staf
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterInput = document.getElementById('filter-staf-input');
    if (filterInput) {
        filterInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#table-staf tbody .staf-row');
            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const divisi = row.getAttribute('data-divisi') || '';
                if (name.includes(query) || divisi.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Auto-focus input nama ketika modal dibuka
    const modalTambahStaf = document.getElementById('modalTambahStaf');
    if (modalTambahStaf) {
        modalTambahStaf.addEventListener('shown.bs.modal', function () {
            const inputNama = document.getElementById('modal_nama_staf');
            if (inputNama) inputNama.focus();
        });
    }
});
</script>
