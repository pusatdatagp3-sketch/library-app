<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Rayon\Model\Rayon $model
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
                <label for="nama_rayon" class="form-label">Nama_rayon</label>
                <input type="text" id="nama_rayon" name="nama_rayon" class="form-control <?= isset($model->errors['nama_rayon']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($model->nama_rayon) ?>">
                <?php if (isset($model->errors['nama_rayon'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($model->errors['nama_rayon']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="zona" class="form-label">Zona</label>
                <input type="text" id="zona" name="zona" class="form-control <?= isset($model->errors['zona']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($model->zona) ?>">
                <?php if (isset($model->errors['zona'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($model->errors['zona']) ?></div>
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
