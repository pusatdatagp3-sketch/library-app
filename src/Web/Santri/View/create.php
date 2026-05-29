<?php

declare(strict_types=1);

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

    <?= $this->render('./_form', [
        'model' => $model,
        'formAction' => $urlGenerator->generate('santri/create'),
        'submitLabel' => 'Simpan Data',
        'showReset' => true,
        'cancelUrl' => null,
    ]) ?>
</div>
