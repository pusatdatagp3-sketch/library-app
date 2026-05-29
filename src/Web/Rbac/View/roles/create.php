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

$this->setTitle('Tambah Peran Baru');
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Tambah Peran Baru</h1>
            <p class="crud-subtitle">Daftarkan kelompok jabatan/peran pengguna baru ke dalam aplikasi.</p>
        </div>
        <div>
            <a href="<?= $urlGenerator->generate('roles/index') ?>" class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </div>

    <?= $this->render('./_form', [
        'errors' => $errors,
        'data' => $data,
        'csrf' => $csrf,
        'formAction' => $urlGenerator->generate('roles/create/post'),
        'submitLabel' => 'Simpan Peran',
        'isUpdate' => false,
    ]) ?>
</div>
