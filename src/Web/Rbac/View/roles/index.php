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
 * @var string|null $successMsg
 * @var array $errorMsgs
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Manajemen Peran & Hak Akses');
?>

<div class="crud-container">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Manajemen Peran &amp; Matriks Akses</h1>
            <p class="crud-subtitle">Kelola peran (roles) dan kaitan hak aksesnya (permissions) pada sistem.</p>
        </div>
        <div>
            <a href="<?= $urlGenerator->generate('roles/create') ?>" class="btn btn-primary">
                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Peran</span>
            </a>
        </div>
    </div>

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
                <ul class="list-unstyled">
                    <?php foreach ($errorMsgs as $msg): ?>
                        <li><?= Html::encode($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="d-flex flex-col gap-4">
        <!-- Daftar Peran -->
        <div class="card">
            <div class="card-header-styled">
                <h3>Daftar Peran (Roles)</h3>
                <p>Tingkat jabatan/kelompok kerja yang terdaftar di aplikasi.</p>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:250px">Nama Peran</th>
                            <th>Deskripsi / Penjelasan</th>
                            <th class="text-center" style="width:200px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-role badge-role-<?= strtolower($role['name']) ?> text-sm py-1 px-3">
                                        <?= Html::encode($role['name']) ?>
                                    </span>
                                </td>
                                <td class="text-muted text-sm"><?= Html::encode($role['description'] ?? '-') ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?= $urlGenerator->generate('roles/update', ['name' => $role['name']]) ?>" class="btn-action btn-edit" title="Edit Peran">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                            <span>Edit</span>
                                        </a>

                                        <form action="<?= $urlGenerator->generate('roles/delete', ['name' => $role['name']]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus peran ini? Menghapus peran akan memutus semua hak akses user terkait.');" class="form-inline">
                                            <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                                            <button type="submit" class="btn-action btn-delete <?= $role['name'] === 'Admin' ? 'opacity-50 cursor-not-allowed' : '' ?>" title="Hapus Peran" <?= $role['name'] === 'Admin' ? 'disabled' : '' ?>>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Matriks Hak Akses -->
        <div class="card mt-2">
            <div class="card-header-styled">
                <h3>Matriks Hubungan Peran &amp; Izin</h3>
                <p>Centang kotak untuk mengaitkan/memberi izin (permission) tertentu kepada peran (role) yang sesuai.</p>
            </div>

            <form action="<?= $urlGenerator->generate('roles/save-matrix') ?>" method="POST">
                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">

                <div class="table-responsive">
                    <table class="table table-matrix">
                        <thead>
                            <tr>
                                <th>Izin (Permission)</th>
                                <?php foreach ($roles as $role): ?>
                                    <th class="text-center" style="width:120px"><?= Html::encode($role['name']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($permissions)): ?>
                                <tr>
                                    <td colspan="<?= count($roles) + 1 ?>" class="text-center text-muted py-5">
                                        Belum ada izin (permission) yang terdaftar. <a href="<?= $urlGenerator->generate('permissions/index') ?>">Kelola Permissions</a>.
                                    </td>
                                </tr>
                            <?php else: ?>
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
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($permissions)): ?>
                    <div class="form-actions mt-4 justify-content-start">
                        <button type="submit" class="btn btn-primary">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Simpan Matriks Hak Akses</span>
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
