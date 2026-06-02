<?php

declare(strict_types=1);

use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $roles
 * @var array $errors
 * @var array $data
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Tambah Pengguna Baru');
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Tambah Pengguna Baru</h1>
            <p class="crud-subtitle">Daftarkan akun baru ke dalam sistem.</p>
        </div>
        <div>
            <a href="<?= $urlGenerator->generate('users/index') ?>" class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </div>

    <?= $this->render('./_form', [
        'roles' => $roles,
        'campusList' => $campusList,
        'errors' => $errors,
        'data' => $data,
        'csrf' => $csrf,
        'formAction' => $urlGenerator->generate('users/create/post'),
        'submitLabel' => 'Simpan Pengguna',
        'isUpdate' => false,
    ]) ?>
</div>
