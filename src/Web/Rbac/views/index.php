<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $roles
 * @var array $permissions
 * @var array $matrix
 * @var array $users
 * @var array $routePermissionMap
 * @var string|null $successMsg
 * @var array $errorMsgs
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Manajemen RBAC & Pengguna');
?>

<div class="crud-container">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Manajemen Akses & Pengguna</h1>
            <p class="crud-subtitle">Kelola pembagian peran, hak akses, dan akun pengguna aplikasi.</p>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    <?php if ($successMsg !== null): ?>
        <div class="alert alert-success">
            <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="alert-content"><?= Html::encode($successMsg) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMsgs)): ?>
        <div class="alert alert-danger">
            <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="alert-content">
                <ul style="margin: 0; padding-left: 16px;">
                    <?php foreach ($errorMsgs as $msg): ?>
                        <li><?= Html::encode($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="rbac-grid">
        <!-- Bagian Kiri: Matriks Hak Akses (Role-Permission Matrix) & Proteksi Rute -->
        <div class="rbac-section-matrix">
            <div class="card">
                <div class="card-header-styled">
                    <h3>Matriks Hak Akses</h3>
                    <p>Centang untuk mengaitkan izin tertentu ke peran terkait.</p>
                </div>

                <form action="<?= $urlGenerator->generate('rbac/save-matrix') ?>" method="POST">
                    <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">

                    <div class="table-responsive">
                        <table class="table table-matrix">
                            <thead>
                                <tr>
                                    <th>Izin (Permission)</th>
                                    <?php foreach ($roles as $role): ?>
                                        <th class="text-center"><?= Html::encode($role['name']) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($permissions as $perm): ?>
                                    <tr>
                                        <td>
                                            <div class="perm-info">
                                                <strong><?= Html::encode($perm['name']) ?></strong>
                                                <small><?= Html::encode($perm['description']) ?></small>
                                            </div>
                                        </td>
                                        <?php foreach ($roles as $role): ?>
                                            <td class="text-center">
                                                <input type="hidden" name="matrix[<?= Html::encode($role['name']) ?>][<?= Html::encode($perm['name']) ?>]" value="0">
                                                <label class="switch-container">
                                                    <input type="checkbox" name="matrix[<?= Html::encode($role['name']) ?>][<?= Html::encode($perm['name']) ?>]" value="1" <?= ($matrix[$role['name']][$perm['name']] ?? false) ? 'checked' : '' ?>>
                                                    <span class="switch-slider"></span>
                                                </label>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-actions" style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Simpan Matriks Hak Akses</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Kartu Proteksi Rute Dinamis -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header-styled">
                    <h3>Proteksi Rute Dinamis</h3>
                    <p>Tentukan tingkat izin minimum yang dibutuhkan untuk masing-masing rute aplikasi.</p>
                </div>

                <form action="<?= $urlGenerator->generate('rbac/save-route-permissions') ?>" method="POST">
                    <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Rute Aplikasi</th>
                                    <th>Izin Minimum yang Dibutuhkan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($routePermissionMap as $routeName => $requiredPermName): ?>
                                    <tr>
                                        <td>
                                            <code style="font-family:monospace; background: #f1f5f9; padding: 4px 8px; border-radius: 6px; color: var(--primary); font-weight: 600; font-size: 0.85rem;">
                                                <?= Html::encode($routeName) ?>
                                            </code>
                                        </td>
                                        <td>
                                            <select name="route_permissions[<?= Html::encode($routeName) ?>]" class="form-control" style="padding: 6px 12px; font-size: 0.85rem; height: 38px;">
                                                <option value="" <?= ($requiredPermName === null || $requiredPermName === '') ? 'selected' : '' ?>>Login Only (Tanpa Izin Khusus)</option>
                                                <?php foreach ($permissions as $perm): ?>
                                                    <option value="<?= Html::encode($perm['name']) ?>" <?= $requiredPermName === $perm['name'] ? 'selected' : '' ?>>
                                                        <?= Html::encode($perm['name']) ?> (<?= Html::encode($perm['description']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-actions" style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            <span>Simpan Konfigurasi Proteksi Rute</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bagian Kanan: Tambah Pengguna Baru -->
        <div class="rbac-section-adduser">
            <div class="card">
                <div class="card-header-styled">
                    <h3>Tambah Pengguna</h3>
                    <p>Daftarkan akun pengguna baru ke dalam aplikasi.</p>
                </div>

                <form action="<?= $urlGenerator->generate('rbac/create-user') ?>" method="POST" class="form-grid">
                    <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">

                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Contoh: ahmad" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Contoh: ahmad@gmail.com" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>

                    <div class="form-group">
                        <label for="role" class="form-label">Peran (Role)</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="">-- Pilih Peran --</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= Html::encode($role['name']) ?>"><?= Html::encode($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <span>Simpan Pengguna</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bagian Bawah: Daftar Pengguna & Perannya -->
    <div class="card" style="margin-top: 30px;">
        <div class="card-header-styled">
            <h3>Daftar Pengguna & Peran</h3>
            <p>Daftar semua pengguna terdaftar dan modifikasi perannya masing-masing.</p>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Peran Saat Ini</th>
                        <th>Aksi Ubah Peran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="user-avatar-initials">
                                        <?= strtoupper(substr($user['username'], 0, 2)) ?>
                                    </div>
                                    <strong><?= Html::encode($user['username']) ?></strong>
                                </div>
                            </td>
                            <td><?= Html::encode($user['email']) ?></td>
                            <td>
                                <span class="badge badge-role badge-role-<?= strtolower($user['role']) ?>">
                                    <?= Html::encode($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <form action="<?= $urlGenerator->generate('rbac/update-user-role') ?>" method="POST" class="role-update-form" style="display: flex; gap: 8px; align-items: center; max-width: 320px;">
                                    <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                                    <input type="hidden" name="user_id" value="<?= Html::encode((string)$user['id']) ?>">
                                    
                                    <select name="role" class="form-control" style="padding: 6px 12px; font-size: 0.85rem;" required>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= Html::encode($role['name']) ?>" <?= $user['role'] === $role['name'] ? 'selected' : '' ?>><?= Html::encode($role['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <button type="submit" class="btn btn-secondary btn-action" style="padding: 6px 12px; font-size: 0.8rem;">
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
