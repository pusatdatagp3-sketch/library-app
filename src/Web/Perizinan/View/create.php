<?php

declare(strict_types=1);

use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $errors
 * @var array $data
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Tambah Perizinan Baru');
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Tambah Perizinan</h1>
            <p class="crud-subtitle">Isi form di bawah ini untuk menambahkan data perizinan baru.</p>
        </div>
        <a href="<?= $urlGenerator->generate('perizinan/index') ?>" class="btn btn-secondary">
            Kembali
        </a>
    </div>

    <?= $this->render('./_form', [
        'errors' => $errors,
        'data' => $data,
        'formAction' => $urlGenerator->generate('perizinan/create'),
        'submitLabel' => 'Simpan Data',
        'showReset' => true,
        'cancelUrl' => null,
    ]) ?>
</div>
