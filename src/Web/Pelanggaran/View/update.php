<?php

declare(strict_types=1);

use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Pelanggaran\Model\Pelanggaran $model
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

    <?= $this->render('./_form', [
        'model' => $model,
        'formAction' => $urlGenerator->generate('pelanggaran/update', ['id' => $model->id]),
        'submitLabel' => 'Simpan Perubahan',
        'showReset' => false,
        'cancelUrl' => $urlGenerator->generate('pelanggaran/index'),
    ]) ?>
</div>
