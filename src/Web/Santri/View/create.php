<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Santri\Model\Santri $model
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Tambah Santri Baru');
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Tambah Santri</h1>
            <p class="crud-subtitle">Isi form di bawah ini untuk menambahkan data santri baru.</p>
        </div>
        <a href="<?= $urlGenerator->generate('santri/index') ?>" class="btn btn-secondary">
            Kembali
        </a>
    </div>

    <div class="card">
        <form action="<?= $urlGenerator->generate('santri/create') ?>" method="POST" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= Html::encode((string)$this->getParameter('csrf')) ?>">

            <div class="form-group">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" id="nama" name="nama" class="form-control <?= isset($model->errors['nama']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($model->nama) ?>">
                <?php if (isset($model->errors['nama'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($model->errors['nama']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="kelas" class="form-label">Kelas</label>
                <input type="text" id="kelas" name="kelas" class="form-control <?= isset($model->errors['kelas']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($model->kelas) ?>">
                <?php if (isset($model->errors['kelas'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($model->errors['kelas']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="daerah" class="form-label">Daerah</label>
                <input type="text" id="daerah" name="daerah" class="form-control <?= isset($model->errors['daerah']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($model->daerah) ?>">
                <?php if (isset($model->errors['daerah'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($model->errors['daerah']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="reset" class="btn btn-secondary">Reset</button>
                <button type="submit" class="btn btn-primary">
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>
