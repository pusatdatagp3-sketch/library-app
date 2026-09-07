<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $roles
 * @var array $errors
 * @var array $data
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 * @var string $formAction
 * @var string $submitLabel
 * @var bool $isUpdate
 * @var array|null $user
 */

?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div class="alert-content">
            <ul class="list-unstyled">
                <?php foreach ($errors as $msg): ?>
                    <li><?= Html::encode($msg) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <form action="<?= $formAction ?>" method="POST" class="form-grid">
        <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">

        <div class="form-group">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" class="form-control"
                   placeholder="Contoh: ahmad" value="<?= Html::encode($data['username']) ?>" required>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control"
                   placeholder="Contoh: ahmad@gmail.com" value="<?= Html::encode($data['email']) ?>" required>
        </div>

        <div class="form-group">
            <?php if ($isUpdate): ?>
                <label for="password" class="form-label">
                    Password Baru <span class="fw-normal text-muted">(Biarkan kosong jika tidak ingin mengubah)</span>
                </label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password baru (min. 6 karakter)">
            <?php else: ?>
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="role" class="form-label">Peran (Role)</label>
            <select id="role" name="role" class="form-control" required>
                <option value="">-- Pilih Peran --</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= Html::encode($role['name']) ?>" <?= $data['role'] === $role['name'] ? 'selected' : '' ?>>
                        <?= Html::encode($role['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($isUpdate && ($user['username'] ?? '') === 'superadmin'): ?>
                <small class="form-hint-danger">Akun superadmin bawaan disarankan untuk tetap memiliki peran super-admin.</small>
            <?php endif; ?>
        </div>

        <div class="form-actions mt-2" style="grid-column: 1 / -1;">
            <button type="submit" class="btn btn-primary btn-w-full">
                <?= Html::encode($submitLabel) ?>
            </button>
        </div>
    </form>
</div>
