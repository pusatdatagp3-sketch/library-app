<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $roles
 * @var array $campusList   [kode => nama]
 * @var array $errors
 * @var array $data          includes allowed_campuses[]
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
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password baru">
            <?php else: ?>
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="role" class="form-label">Peran (Role)</label>
            <select id="role" name="role" class="form-control" required>
                <?php if (!$isUpdate): ?>
                    <option value="">-- Pilih Peran --</option>
                <?php endif; ?>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= Html::encode($role['name']) ?>" <?= $data['role'] === $role['name'] ? 'selected' : '' ?>>
                        <?= Html::encode($role['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($isUpdate && $user['username'] === 'admin'): ?>
                <small class="form-hint-danger">Akun admin bawaan disarankan untuk tetap memiliki peran Admin.</small>
            <?php endif; ?>
        </div>

        <!-- ── Allowed Campuses ─────────────────────────────────────────── -->
        <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label">
                Akses Kampus <span class="text-danger">*</span>
            </label>
            <p class="text-sm text-muted" style="margin-top: 2px; margin-bottom: 10px;">
                Pilih satu atau lebih kampus yang dapat diakses oleh pengguna ini.
            </p>

            <?php if (empty($campusList)): ?>
                <div style="padding: 10px 14px; font-size: 0.85rem; background: var(--bg-hover); border-radius: 8px; border: 1px solid var(--border);">
                    <i class="ri-information-line"></i>
                    Belum ada data kampus di tabel <code>list_kampus</code>.
                </div>
            <?php else: ?>
                <div style="
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                    padding: 14px 16px;
                    background: var(--bg-hover);
                    border: 1.5px solid var(--border);
                    border-radius: 10px;
                ">
                    <?php foreach ($campusList as $kode => $nama): ?>
                        <?php $checked = in_array((string)$kode, array_map('strval', $data['allowed_campuses'] ?? []), true); ?>
                        <label
                            for="campus_<?= Html::encode($kode) ?>"
                            id="campus-label-<?= Html::encode($kode) ?>"
                            class="campus-checkbox-label"
                            style="
                                display: inline-flex;
                                align-items: center;
                                gap: 8px;
                                padding: 7px 14px 7px 10px;
                                border-radius: 8px;
                                border: 1.5px solid <?= $checked ? 'var(--primary)' : 'var(--border)' ?>;
                                background: <?= $checked ? 'rgba(99,102,241,0.09)' : 'var(--bg-main)' ?>;
                                cursor: pointer;
                                font-size: 0.88rem;
                                font-weight: 500;
                                transition: border-color 0.15s ease, background 0.15s ease;
                                user-select: none;
                            "
                        >
                            <input
                                type="checkbox"
                                id="campus_<?= Html::encode($kode) ?>"
                                name="allowed_campuses[]"
                                value="<?= Html::encode($kode) ?>"
                                <?= $checked ? 'checked' : '' ?>
                                style="accent-color: var(--primary); width: 15px; height: 15px; cursor: pointer; flex-shrink: 0;"
                            >
                            <span class="badge-campus" style="padding: 2px 7px; font-size: 0.68rem;"><?= Html::encode($kode) ?></span>
                            <span style="color: var(--text-main);"><?= Html::encode($nama) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-actions mt-2" style="grid-column: 1 / -1;">
            <button type="submit" class="btn btn-primary btn-w-full">
                <?= Html::encode($submitLabel) ?>
            </button>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('.campus-checkbox-label').forEach(function(label) {
    var cb = label.querySelector('input[type="checkbox"]');
    if (!cb) return;
    cb.addEventListener('change', function() {
        if (cb.checked) {
            label.style.borderColor = 'var(--primary)';
            label.style.background  = 'rgba(99,102,241,0.09)';
        } else {
            label.style.borderColor = 'var(--border)';
            label.style.background  = 'var(--bg-main)';
        }
    });
});
</script>
