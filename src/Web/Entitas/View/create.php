<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var App\Web\Entitas\Model\Entitas $model
 * @var string $prefix
 * @var string $modulTitle
 * @var string $indexRoute
 */

$this->setTitle("Tambah Entitas - {$modulTitle}");
?>

<div class="crud-container max-w-2xl">
    <div class="crud-header">
        <div>
            <h1 class="crud-title"><i class="ri-add-circle-line text-primary"></i> Tambah Entitas</h1>
            <p class="crud-subtitle">Tambahkan entitas baru ke modul <?= Html::encode($modulTitle) ?></p>
        </div>
    </div>

    <div class="card">
        <form method="POST" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">

            <div class="form-group mb-4">
                <label class="form-label" for="nama">Nama Entitas</label>
                <input type="text"
                       id="nama"
                       name="nama"
                       value="<?= Html::encode($model->nama) ?>"
                       class="form-control <?= isset($model->errors['nama']) ? 'is-invalid' : '' ?>"
                       placeholder="Contoh: Kurikulum KMI"
                       required>
                <?php if (isset($model->errors['nama'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($model->errors['nama']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group mb-4">
                <label class="form-label" for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi"
                          name="deskripsi"
                          rows="4"
                          class="form-control"
                          placeholder="Jelaskan mengenai entitas ini..."><?= Html::encode($model->deskripsi ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="<?= $this->getParameter('urlGenerator')->generate($indexRoute) ?>" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Entitas</button>
            </div>
        </form>
    </div>
</div>
