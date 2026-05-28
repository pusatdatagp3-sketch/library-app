<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $permission
 * @var array $errors
 * @var array $data
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Edit Izin: ' . $permission['name']);
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Edit Izin (Permission)</h1>
            <p class="crud-subtitle">Ubah informasi kegunaan untuk kunci izin: <strong><?= Html::encode($permission['name']) ?></strong></p>
        </div>
        <div>
            <a href="<?= $urlGenerator->generate('permissions/index') ?>" class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </div>

    <!-- Alert Kesalahan -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="alert-content">
                <ul style="margin: 0; padding-left: 16px;">
                    <?php foreach ($errors as $msg): ?>
                        <li><?= Html::encode($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <form action="<?= $urlGenerator->generate('permissions/update/post', ['name' => $permission['name']]) ?>" method="POST" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">

            <div class="form-group">
                <label class="form-label">Kunci Hak Akses (Permission Key)</label>
                <input type="text" class="form-control" value="<?= Html::encode($permission['name']) ?>" disabled style="background-color: var(--bg-hover); cursor: not-allowed;">
                <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px;">Kunci hak akses unik dan tidak dapat diubah setelah didaftarkan.</small>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Penjelasan / Deskripsi Kegunaan</label>
                <textarea id="description" name="description" class="form-control" placeholder="Contoh: Mengizinkan pengguna untuk membuka dan mengunduh laporan keuangan bulanan." rows="4" required><?= Html::encode($data['description']) ?></textarea>
            </div>

            <div class="form-actions" style="margin-top: 10px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    Perbarui Izin
                </button>
            </div>
        </form>
    </div>
</div>
