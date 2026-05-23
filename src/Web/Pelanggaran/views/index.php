<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Pelanggaran\Pelanggaran[] $models
 * @var UrlGeneratorInterface $urlGenerator
 * @var \App\Web\Auth\UserSession $userSession
 * @var string|null $successMsg
 * @var array $errorMsgs
 */

$this->setTitle('Data Pelanggaran');
?>

<div class="crud-container">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Data Pelanggaran</h1>
            <p class="crud-subtitle">Kelola informasi data pelanggaran di sistem.</p>
        </div>
        
        <?php if ($userSession->hasPermission('create_pelanggaran')): ?>
            <a href="<?= $urlGenerator->generate('pelanggaran/create') ?>" class="btn btn-primary">
                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Pelanggaran
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

    <?php if (empty($models)): ?>
        <div class="card empty-state text-center">
            <svg class="empty-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v3m0 0h4.5V12c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v9" />
            </svg>
            <h3>Belum Ada Data Pelanggaran</h3>
            <p class="text-muted">Gunakan tombol di atas untuk menambahkan data baru.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive card">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Stambuk</th>
                        <th>Pelanggaran</th>
                        <th>Poin</th>
                        <th>Keterangan</th>
                        <?php if ($userSession->hasPermission('update_pelanggaran') || $userSession->hasPermission('delete_pelanggaran')): ?>
                            <th class="text-center" style="width: 180px;">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <td><span class="badge badge-id"><?= Html::encode((string)$model->id) ?></span></td>
                            <td><?= Html::encode((string)$model->stambuk) ?></td>
                            <td><?= Html::encode((string)$model->pelanggaran) ?></td>
                            <td><?= Html::encode((string)$model->poin) ?></td>
                            <td><?= Html::encode((string)$model->keterangan) ?></td>
                            <?php if ($userSession->hasPermission('update_pelanggaran') || $userSession->hasPermission('delete_pelanggaran')): ?>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($userSession->hasPermission('update_pelanggaran')): ?>
                                            <a href="<?= $urlGenerator->generate('pelanggaran/update', ['id' => $model->id]) ?>" class="btn-action btn-edit">
                                                Edit
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($userSession->hasPermission('delete_pelanggaran')): ?>
                                            <form action="<?= $urlGenerator->generate('pelanggaran/delete', ['id' => $model->id]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" style="display:inline;">
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
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
