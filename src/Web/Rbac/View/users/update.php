<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $user
 * @var array $roles
 * @var array $errors
 * @var array $data
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Edit Pengguna: ' . $user['username']);
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Edit Pengguna</h1>
            <p class="crud-subtitle">Perbarui informasi untuk pengguna: <strong><?= Html::encode($user['username']) ?></strong></p>
        </div>
        <div>
            <a href="<?= $urlGenerator->generate('users/index') ?>" class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </div>

    <!-- Alert Kesalahan -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="alert-content">
                <ul style="margin: 0; padding-left: 16px;">
                    <?php foreach ($errors as $msg): ?>
                        <li><?= Html::encode($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <form action="<?= $urlGenerator->generate('users/update/post', ['id' => $user['id']]) ?>" method="POST" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">

            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Contoh: ahmad" value="<?= Html::encode($data['username']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Contoh: ahmad@gmail.com" value="<?= Html::encode($data['email']) ?>" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password Baru <span style="font-weight: normal; color: var(--text-muted);">(Biarkan kosong jika tidak ingin mengubah)</span></label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password baru">
            </div>

            <div class="form-group">
                <label for="role" class="form-label">Peran (Role)</label>
                <select id="role" name="role" class="form-control" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= Html::encode($role['name']) ?>" <?= $data['role'] === $role['name'] ? 'selected' : '' ?>>
                            <?= Html::encode($role['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($user['username'] === 'admin'): ?>
                    <small style="color: var(--danger); font-weight: 500; margin-top: 4px;">Akun admin bawaan disarankan untuk tetap memiliki peran Admin.</small>
                <?php endif; ?>
            </div>

            <div class="form-actions" style="margin-top: 10px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    Perbarui Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
