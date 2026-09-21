<?php

declare(strict_types=1);

use App\Web\Staf\Model\StafEntity;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var StafEntity $staf
 * @var array{id: int, nama_staf: string, divisi: string, is_active: bool} $data
 * @var array<string> $errors
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Ubah Data Staf - Manajemen Staf');
?>

<div class="container-fluid p-0" style="max-width: 760px;">

    <!-- Breadcrumb & Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="<?= $urlGenerator->generate('home') ?>" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= $urlGenerator->generate('staf/index') ?>" class="text-decoration-none">Manajemen Staf</a></li>
                <li class="breadcrumb-item active" aria-current="page">Ubah Staf</li>
            </ol>
        </nav>
        <h2 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="ri-edit-line text-primary"></i> Ubah Data Staf
        </h2>
        <p class="text-muted small mb-0">Perbarui informasi staf atau status keaktifan penugasan.</p>
    </div>

    <!-- Error Alert -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="ri-error-warning-line"></i> <strong>Mohon periksa kesalahan berikut:</strong>
            </div>
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                    <li><?= Html::encode($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form method="POST" action="<?= $urlGenerator->generate('staf/update/post', ['id' => $staf->id]) ?>">
                <?php if (!empty($csrf)): ?>
                    <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                <?php endif; ?>

                <!-- Nama Staf -->
                <div class="mb-3">
                    <label for="nama_staf" class="form-label fw-semibold">
                        Nama Lengkap Staf <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="ri-user-line text-muted"></i></span>
                        <input
                            type="text"
                            id="nama_staf"
                            name="nama_staf"
                            class="form-control form-control-lg fs-6"
                            placeholder="Contoh: Ust. Ahmad Fauzi, S.Pd.I"
                            value="<?= Html::encode($data['nama_staf']) ?>"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Divisi -->
                <div class="mb-3">
                    <label for="divisi" class="form-label fw-semibold">
                        Divisi Penugasan <span class="text-danger">*</span>
                    </label>
                    <select id="divisi" name="divisi" class="form-select form-select-lg fs-6" required>
                        <option value="Library" <?= $data['divisi'] === 'Library' ? 'selected' : '' ?>>
                            📚 Library (Staf Pengelola Perpustakaan)
                        </option>
                        <option value="Staff" <?= $data['divisi'] === 'Staff' ? 'selected' : '' ?>>
                            👥 Staff (Staf Umum / Pengawas Piket)
                        </option>
                    </select>
                </div>

                <!-- Status Aktif -->
                <div class="mb-4">
                    <label class="form-label fw-semibold d-block">Status Keaktifan</label>
                    <div class="form-check form-switch fs-5">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            id="is_active"
                            name="is_active"
                            value="1"
                            <?= !empty($data['is_active']) ? 'checked' : '' ?>
                        >
                        <label class="form-check-label fs-6 pt-1" for="is_active">
                            Aktifkan staf ini (Bisa dipilih pada saat shift scan kartu santri)
                        </label>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                    <a href="<?= $urlGenerator->generate('staf/index') ?>" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ri-save-line me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
