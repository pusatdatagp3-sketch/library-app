<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Konsulat\Model\Konsulat[] $models
 * @var UrlGeneratorInterface $urlGenerator
 * @var \App\Web\Auth\Model\UserSession $userSession
 * @var string|null $successMsg
 * @var array $errorMsgs
 */

$this->setTitle('Data Konsulat');
?>

<div class="crud-container">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Data Konsulat</h1>
            <p class="crud-subtitle">Kelola informasi daerah asal konsulat santri/guru dan pembagian kampus.</p>
        </div>
        
        <?php if ($userSession->hasPermission('create_konsulat')): ?>
            <a href="<?= $urlGenerator->generate('konsulat/create') ?>" class="btn btn-primary">
                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Konsulat
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

    <?php foreach ($errorMsgs as $errorMsg): ?>
        <div class="alert alert-danger">
            <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <div><?= Html::encode($errorMsg) ?></div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($models)): ?>
        <div class="card empty-state text-center">
            <svg class="empty-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v3m0 0h4.5V12c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v9" />
            </svg>
            <h3>Belum Ada Data Konsulat</h3>
            <p class="text-muted">Gunakan tombol di atas untuk menambahkan data konsulat baru.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive card">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Konsulat</th>
                        <th>Pembagian Kampus</th>
                        <?php if ($userSession->hasPermission('update_konsulat') || $userSession->hasPermission('delete_konsulat')): ?>
                            <th class="text-center" style="width: 180px;">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <td><span class="badge badge-id"><?= Html::encode((string)$model->id) ?></span></td>
                            <td><strong><?= Html::encode($model->konsulat) ?></strong></td>
                            <td>
                                <span class="badge" style="background: #f0fdf4; color: #166534; font-weight: 600; font-size: 0.8rem; padding: 4px 8px; border-radius: 4px;">
                                    <?= Html::encode($model->kampus) ?>
                                </span>
                            </td>
                            <?php if ($userSession->hasPermission('update_konsulat') || $userSession->hasPermission('delete_konsulat')): ?>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($userSession->hasPermission('update_konsulat')): ?>
                                            <a href="<?= $urlGenerator->generate('konsulat/update', ['id' => $model->id]) ?>" class="btn-action btn-edit" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                                </svg>
                                                Edit
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($userSession->hasPermission('delete_konsulat')): ?>
                                            <form action="<?= $urlGenerator->generate('konsulat/delete', ['id' => $model->id]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus konsulat ini?');" style="display:inline;">
                                                <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                                <button type="submit" class="btn-action btn-delete" title="Hapus">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
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
