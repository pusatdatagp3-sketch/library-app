<?php

declare(strict_types=1);

use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Rayon\Model\Rayon $model
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Edit Data Rayon');
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Edit Data Rayon</h1>
            <p class="crud-subtitle">Perbarui data rayon terpilih.</p>
        </div>
        <a href="<?= $urlGenerator->generate('rayon/index') ?>" class="btn btn-secondary">
            Kembali
        </a>
    </div>

    <?= $this->render('./_form', [
        'model' => $model,
        'formAction' => $urlGenerator->generate('rayon/update', ['id' => $model->id]),
        'submitLabel' => 'Simpan Perubahan',
        'showReset' => false,
        'cancelUrl' => $urlGenerator->generate('rayon/index'),
    ]) ?>
</div>
