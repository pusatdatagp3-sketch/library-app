<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $errors
 * @var array $data
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 * @var string $formAction
 * @var string $submitLabel
 * @var bool $showReset
 * @var string|null $cancelUrl
 */

?>

<div class="card">
    <form action="<?= $formAction ?>" method="POST" class="form-grid">
        <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">

        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger" style="grid-column: span 2;">
                <div class="alert-content"><?= Html::encode($errors['general']) ?></div>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="nama_kamar" class="form-label">Nama Kamar</label>
            <input type="text" id="nama_kamar" name="nama_kamar" class="form-control <?= isset($errors['nama_kamar']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($data['nama_kamar'] ?? '') ?>" placeholder="Contoh: Kamar Al-Fatih" required>
            <?php if (isset($errors['nama_kamar'])): ?>
                <div class="invalid-feedback"><?= Html::encode($errors['nama_kamar']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="kapasitas" class="form-label">Kapasitas (Orang)</label>
            <input type="number" id="kapasitas" name="kapasitas" class="form-control <?= isset($errors['kapasitas']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($data['kapasitas'] ?? '') ?>" placeholder="Contoh: 10" required min="1">
            <?php if (isset($errors['kapasitas'])): ?>
                <div class="invalid-feedback"><?= Html::encode($errors['kapasitas']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <?php if ($showReset): ?>
                <button type="reset" class="btn btn-secondary">Reset</button>
            <?php elseif ($cancelUrl !== null): ?>
                <a href="<?= $cancelUrl ?>" class="btn btn-secondary">Batal</a>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">
                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <?= Html::encode($submitLabel) ?>
            </button>
        </div>
    </form>
</div>
