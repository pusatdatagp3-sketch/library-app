<?php

declare(strict_types=1);

use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $errors
 * @var array $data
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Tambah Kunci Hak Akses Baru');
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Tambah Izin Baru</h1>
            <p class="crud-subtitle">Daftarkan kunci izin halus (permission key) baru untuk proteksi spesifik di aplikasi.</p>
        </div>
        <div>
            <a href="<?= $urlGenerator->generate('permissions/index') ?>" class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </div>

    <?= $this->render('./_form', [
        'errors' => $errors,
        'data' => $data,
        'csrf' => $csrf,
        'formAction' => $urlGenerator->generate('permissions/create/post'),
        'submitLabel' => 'Simpan Izin Baru',
        'isUpdate' => false,
    ]) ?>
</div>
