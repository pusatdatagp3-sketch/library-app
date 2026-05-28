<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $routePermissionMap
 * @var array $permissions
 * @var string|null $successMsg
 * @var array $errorMsgs
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Manajemen Proteksi Rute');
?>

<div class="crud-container">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Proteksi Rute Dinamis</h1>
            <p class="crud-subtitle">Tentukan hak akses minimum yang diperlukan pengguna untuk mengunjungi halaman/rute tertentu.</p>
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
        <form action="<?= $urlGenerator->generate('routes/save-route-permissions') ?>" method="POST">
            <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Rute (Route Name)</th>
                            <th>Izin Minimum yang Dibutuhkan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($routePermissionMap as $routeName => $requiredPermName): ?>
                            <tr>
                                <td>
                                    <code style="font-family: monospace; font-size: 0.9rem; background: var(--bg-hover); padding: 4px 8px; border-radius: 6px; color: var(--primary); font-weight: 600;">
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
