<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Pelanggaran\Pelanggaran $model
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Edit Data Pelanggaran');
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Edit Data Pelanggaran</h1>
            <p class="crud-subtitle">Perbarui data pelanggaran terpilih.</p>
        </div>
        <a href="<?= $urlGenerator->generate('pelanggaran/index') ?>" class="btn btn-secondary">
            Kembali
        </a>
    </div>

    <div class="card">
        <form action="<?= $urlGenerator->generate('pelanggaran/update', ['id' => $model->id]) ?>" method="POST" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= Html::encode((string)$this->getParameter('csrf')) ?>">

            <div class="form-group">
                <label for="stambuk" class="form-label">Stambuk</label>
                <input type="text" id="stambuk" name="stambuk" class="form-control <?= isset($model->errors['stambuk']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($model->stambuk) ?>">
                <?php if (isset($model->errors['stambuk'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($model->errors['stambuk']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="pelanggaran" class="form-label">Pelanggaran</label>
                <input type="text" id="pelanggaran" name="pelanggaran" class="form-control <?= isset($model->errors['pelanggaran']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($model->pelanggaran) ?>">
                <?php if (isset($model->errors['pelanggaran'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($model->errors['pelanggaran']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="poin" class="form-label">Poin</label>
                <input type="text" id="poin" name="poin" class="form-control <?= isset($model->errors['poin']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($model->poin) ?>">
                <?php if (isset($model->errors['poin'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($model->errors['poin']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="keterangan" class="form-label">Keterangan</label>
                <input type="text" id="keterangan" name="keterangan" class="form-control <?= isset($model->errors['keterangan']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($model->keterangan) ?>">
                <?php if (isset($model->errors['keterangan'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($model->errors['keterangan']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <a href="<?= $urlGenerator->generate('pelanggaran/index') ?>" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
