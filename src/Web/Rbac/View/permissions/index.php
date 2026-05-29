<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $permissions
 * @var string|null $successMsg
 * @var array $errorMsgs
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Manajemen Hak Akses (Permissions)');
?>

<div class="crud-container">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Manajemen Izin (Permissions)</h1>
            <p class="crud-subtitle">Daftar kunci hak akses halus (fine-grained keys) untuk mengontrol aksi-aksi di aplikasi.</p>
        </div>
        <div>
            <a href="<?= $urlGenerator->generate('permissions/create') ?>" class="btn btn-primary">
                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Izin</span>
            </a>
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

    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 250px;">Kunci Hak Akses (Key)</th>
                        <th>Penjelasan / Deskripsi</th>
                        <th class="text-center" style="width: 200px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($permissions)): ?>
                        <tr>
                            <td colspan="3" class="text-center" style="color: var(--text-muted); padding: 30px;">
                                Belum ada permission terdaftar di database.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($permissions as $perm): ?>
                            <tr>
                                <td>
                                    <code style="font-family: monospace; font-size: 0.9rem; background: var(--bg-hover); padding: 4px 8px; border-radius: 6px; color: var(--primary); font-weight: 700;">
                                        <?= Html::encode($perm['name']) ?>
                                    </code>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.9rem;">
                                    <?= Html::encode($perm['description'] ?? '-') ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?= $urlGenerator->generate('permissions/update', ['name' => $perm['name']]) ?>" class="btn-action btn-edit" title="Edit Permission">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                            <span>Edit</span>
                                        </a>
                                        
                                        <form action="<?= $urlGenerator->generate('permissions/delete', ['name' => $perm['name']]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus izin (permission) ini? Menghapus izin ini juga akan mencopot hak ini dari semua peran yang dikaitkan.');" style="margin: 0; display: inline;">
                                            <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                                            <button type="submit" class="btn-action btn-delete" title="Hapus Permission" <?= $perm['name'] === 'manage_rbac' ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
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
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
