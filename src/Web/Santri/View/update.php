<?php

declare(strict_types=1);

use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Santri\Model\Santri $model
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Edit Data Santri');
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Edit Data Santri</h1>
            <p class="crud-subtitle">Perbarui data santri terpilih.</p>
        </div>
        <a href="<?= $urlGenerator->generate('santri/index') ?>" class="btn btn-secondary">
            Kembali
        </a>
    </div>

    <?= $this->render('./_form', [
        'model' => $model,
        'formAction' => $urlGenerator->generate('santri/update', ['id' => $model->kds]),
        'submitLabel' => 'Simpan Perubahan',
        'showReset' => false,
        'cancelUrl' => $urlGenerator->generate('santri/index'),
    ]) ?>
</div>
