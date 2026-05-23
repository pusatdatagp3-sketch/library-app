<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Konsulat\Konsulat $model
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Tambah Konsulat Baru');
$csrf = $this->getParameter('csrf');
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Tambah Konsulat</h1>
            <p class="crud-subtitle">Buat data daerah konsulat baru beserta pembagian kampusnya.</p>
        </div>
        <a href="<?= $urlGenerator->generate('konsulat/index') ?>" class="btn btn-secondary">
            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="card">
        <form action="<?= $urlGenerator->generate('konsulat/create') ?>" method="POST" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= Html::encode((string)$csrf) ?>">

            <div class="form-group">
                <label for="konsulat" class="form-label">Nama Konsulat</label>
                <input type="text" id="konsulat" name="konsulat" class="form-control <?= isset($model->errors['konsulat']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($model->konsulat) ?>" placeholder="Contoh: Surabaya, Jakarta, Ponorogo">
                <?php if (isset($model->errors['konsulat'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($model->errors['konsulat']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="kampus" class="form-label">Kampus</label>
                <input type="text" id="kampus" name="kampus" class="form-control <?= isset($model->errors['kampus']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($model->kampus) ?>" placeholder="Contoh: Kampus 1, Kampus 3, Kampus Gontor 2">
                <?php if (isset($model->errors['kampus'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($model->errors['kampus']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="reset" class="btn btn-secondary">Reset</button>
                <button type="submit" class="btn btn-primary">
                    <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Simpan Konsulat
                </button>
            </div>
        </form>
    </div>
</div>
