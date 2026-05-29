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
 * @var string $formAction
 * @var string $submitLabel
 * @var bool $isUpdate
 * @var array|null $role
 */

?>

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
    <form action="<?= $formAction ?>" method="POST" class="form-grid">
        <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">

        <div class="form-group">
            <?php if ($isUpdate): ?>
                <label class="form-label">Nama Peran (Role Name)</label>
                <input type="text" class="form-control" value="<?= Html::encode($role['name']) ?>" disabled style="background-color: var(--bg-hover); cursor: not-allowed;">
                <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px;">Nama peran bersifat unik dan tidak dapat diubah setelah didaftarkan.</small>
            <?php else: ?>
                <label for="name" class="form-label">Nama Peran (Role Name)</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: Bendahara" value="<?= Html::encode($data['name']) ?>" required>
                <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px;">Hanya karakter huruf, angka, tanda hubung (-), dan garis bawah (_).</small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Deskripsi / Penjelasan Singkat</label>
            <textarea id="description" name="description" class="form-control" placeholder="Contoh: Mengatur segala keuangan operasional lembaga." rows="4"<?= $isUpdate ? ' required' : '' ?>><?= Html::encode($data['description']) ?></textarea>
        </div>

        <div class="form-actions" style="margin-top: 10px;">
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                <?= Html::encode($submitLabel) ?>
            </button>
        </div>
    </form>
</div>
