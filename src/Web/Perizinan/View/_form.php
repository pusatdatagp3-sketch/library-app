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
 * @var string $formAction
 * @var string $submitLabel
 * @var bool $showReset
 * @var string|null $cancelUrl
 */
?>

<div class="card">
    <form action="<?= $formAction ?>" method="POST" class="form-grid">
        <input type="hidden" name="_csrf" value="<?= Html::encode((string)$this->getParameter('csrf')) ?>">

        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger" style="grid-column: span 2;">
                <div class="alert-content"><?= Html::encode($errors['general']) ?></div>
            </div>
        <?php endif; ?>

            <div class="form-group">
                <label for="kemana" class="form-label">Kemana</label>
                <input type="text" id="kemana" name="kemana" class="form-control <?= isset($errors['kemana']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($data['kemana'] ?? '') ?>">
                <?php if (isset($errors['kemana'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($errors['kemana']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="sama_siapa" class="form-label">Sama siapa</label>
                <input type="text" id="sama_siapa" name="sama_siapa" class="form-control <?= isset($errors['sama_siapa']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($data['sama_siapa'] ?? '') ?>">
                <?php if (isset($errors['sama_siapa'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($errors['sama_siapa']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="berapa_orang" class="form-label">Berapa orang</label>
                <input type="text" id="berapa_orang" name="berapa_orang" class="form-control <?= isset($errors['berapa_orang']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($data['berapa_orang'] ?? '') ?>">
                <?php if (isset($errors['berapa_orang'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($errors['berapa_orang']) ?></div>
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
