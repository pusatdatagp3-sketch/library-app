<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Rayon\Model\Rayon[] $models
 * @var array $filters
 * @var UrlGeneratorInterface $urlGenerator
 * @var \App\Web\Auth\Model\UserSession $userSession
 * @var string|null $successMsg
 * @var array $errorMsgs
 */

$this->setTitle('Data Rayon');
?>

<div class="crud-container">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Data Rayon</h1>
            <p class="crud-subtitle">Kelola informasi data rayon di sistem.</p>
        </div>
        
        <?php if ($userSession->hasPermission('create_rayon')): ?>
            <a href="<?= $urlGenerator->generate('rayon/create') ?>" class="btn btn-primary">
                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Rayon
            </a>
        <?php endif; ?>
    </div>

    <?php if ($successMsg): ?>
        <div class="alert alert-success">
            <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div><?= Html::encode($successMsg) ?></div>
        </div>
    <?php endif; ?>

    <form id="filter-form" method="GET" action="<?= $urlGenerator->generate('rayon/index') ?>"></form>

    <?php if (empty($models) && empty($filters)): ?>
        <div class="card empty-state text-center">
            <svg class="empty-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v3m0 0h4.5V12c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v9" />
            </svg>
            <h3>Belum Ada Data Rayon</h3>
            <p class="text-muted">Gunakan tombol di atas untuk menambahkan data baru.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive card">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Nama_rayon</th>
                        <th>Zona</th>
                        <?php if ($userSession->hasPermission('update_rayon') || $userSession->hasPermission('delete_rayon')): ?>
                            <th class="text-center" style="width: 180px;">Aksi</th>
                        <?php endif; ?>
                    </tr>
                    <tr class="filter-row">
                    <td>
                        <input type="text" name="id" form="filter-form" value="<?= Html::encode($filters['id'] ?? '') ?>" class="form-control" placeholder="Cari ID...">
                    </td>
                    <td>
                        <input type="text" name="nama_rayon" form="filter-form" value="<?= Html::encode($filters['nama_rayon'] ?? '') ?>" class="form-control" placeholder="Cari Nama rayon...">
                    </td>
                    <td>
                        <input type="text" name="zona" form="filter-form" value="<?= Html::encode($filters['zona'] ?? '') ?>" class="form-control" placeholder="Cari Zona...">
                    </td>
                        <?php if ($userSession->hasPermission('update_rayon') || $userSession->hasPermission('delete_rayon')): ?>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="submit" form="filter-form" class="btn btn-sm btn-primary">Cari</button>
                                    <a href="<?= $urlGenerator->generate('rayon/index') ?>" class="btn btn-sm btn-secondary">Reset</a>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($models)): ?>
                        <?php $colCount = 3 + ($userSession->hasPermission('update_rayon') || $userSession->hasPermission('delete_rayon') ? 1 : 0); ?>
                        <tr>
                            <td colspan="<?= $colCount ?>" class="text-center text-muted py-4">
                                Tidak ada data yang cocok dengan pencarian.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($models as $model): ?>
                            <tr>
                            <td><span class="badge badge-id"><?= Html::encode((string)$model->id) ?></span></td>
                            <td><?= Html::encode((string)$model->nama_rayon) ?></td>
                            <td><?= Html::encode((string)$model->zona) ?></td>
                                <?php if ($userSession->hasPermission('update_rayon') || $userSession->hasPermission('delete_rayon')): ?>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($userSession->hasPermission('update_rayon')): ?>
                                                <a href="<?= $urlGenerator->generate('rayon/update', ['id' => $model->id]) ?>" class="btn-action btn-edit">
                                                    Edit
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($userSession->hasPermission('delete_rayon')): ?>
                                                <form action="<?= $urlGenerator->generate('rayon/delete', ['id' => $model->id]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" style="display:inline;">
                                                    <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                                    <button type="submit" class="btn-action btn-delete">
                                                        Hapus
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('[form="filter-form"]').forEach(input => {
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('filter-form').submit();
        }
    });
    if (input.tagName === 'SELECT') {
        input.addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
    }
});
</script>
