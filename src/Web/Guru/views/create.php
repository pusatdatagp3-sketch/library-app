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
 * @var string|null $csrf
 */

$this->setTitle('Tambah Guru Baru');
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Tambah Guru Baru</h1>
            <p class="crud-subtitle">Isi data di bawah ini untuk menambahkan guru baru.</p>
        </div>
        <a href="<?= $urlGenerator->generate('guru/index') ?>" class="btn btn-secondary">
            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="card">
        <form action="<?= $urlGenerator->generate('guru/create') ?>" method="POST" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">

            <div class="form-group">
                <label for="stambuk" class="form-label">Stambuk</label>
                <input type="text" id="stambuk" name="stambuk" class="form-control <?= isset($errors['stambuk']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($data['stambuk'] ?? '') ?>" placeholder="Contoh: 20261001">
                <?php if (isset($errors['stambuk'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($errors['stambuk']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="nama" class="form-label">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" class="form-control <?= isset($errors['nama']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($data['nama'] ?? '') ?>" placeholder="Contoh: Ahmad Fauzi">
                <?php if (isset($errors['nama'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($errors['nama']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="daerah" class="form-label">Daerah</label>
                <input type="text" id="daerah" name="daerah" class="form-control <?= isset($errors['daerah']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($data['daerah'] ?? '') ?>" placeholder="Contoh: Jawa Timur">
                <?php if (isset($errors['daerah'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($errors['daerah']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="konsulat" class="form-label">Konsulat</label>
                <input type="text" id="konsulat" name="konsulat" class="form-control <?= isset($errors['konsulat']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($data['konsulat'] ?? '') ?>" placeholder="Contoh: Gontor">
                <?php if (isset($errors['konsulat'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($errors['konsulat']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($data['email'] ?? '') ?>" placeholder="Contoh: ahmad@gmail.com">
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($errors['email']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="no_telp" class="form-label">Nomor Telefon</label>
                <input type="text" id="no_telp" name="no_telp" class="form-control <?= isset($errors['no_telp']) ? 'is-invalid' : '' ?>" value="<?= Html::encode($data['no_telp'] ?? '') ?>" placeholder="Contoh: 081234567890">
                <?php if (isset($errors['no_telp'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($errors['no_telp']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="reset" class="btn btn-secondary">Reset</button>
                <button type="submit" class="btn btn-primary">
                    <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Simpan Guru
                </button>
            </div>
        </form>
    </div>
</div>
