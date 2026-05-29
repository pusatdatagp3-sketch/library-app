<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Santri\Model\Santri $model
 * @var UrlGeneratorInterface $urlGenerator
 * @var string $formAction
 * @var string $submitLabel
 * @var bool $showReset
 * @var string|null $cancelUrl
 */

?>

<div class="card">
    <form action="<?= $formAction ?>" method="POST" class="form-grid">
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
            <?php if ($showReset): ?>
                <button type="reset" class="btn btn-secondary">Reset</button>
            <?php elseif ($cancelUrl !== null): ?>
                <a href="<?= $cancelUrl ?>" class="btn btn-secondary">Batal</a>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">
                <?= Html::encode($submitLabel) ?>
            </button>
        </div>
    </form>
</div>
