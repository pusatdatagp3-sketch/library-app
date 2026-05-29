<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Perizinan\Model\Perizinan[] $perizinanList
 * @var array $filters
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 * @var string|null $successMsg
 * @var array $errorMsgs
 * @var \App\Web\Auth\Model\UserSession $userSession
 */

$this->setTitle('Data Perizinan - List');
?>

<div class="crud-container">
    <?php if (!empty($successMsg)): ?>
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
                <ul style="margin:0;padding-left:16px;font-size:0.875rem;line-height:1.5;">
                    <?php foreach ($errorMsgs as $err): ?>
                        <li><?= Html::encode($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="crud-header">
        <div>
            <h1 class="crud-title">Data Perizinan</h1>
            <p class="crud-subtitle">Kelola informasi perizinan pondok pesantren dengan mudah.</p>
        </div>
        <?php if ($userSession->hasPermission('create_perizinan')): ?>
            <a href="<?= $urlGenerator->generate('perizinan/create') ?>" class="btn btn-primary">
                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Perizinan
            </a>
        <?php endif; ?>
    </div>

    <form id="filter-form" method="GET" action="<?= $urlGenerator->generate('perizinan/index') ?>"></form>

    <?php if (empty($perizinanList) && empty($filters)): ?>
        <div class="empty-state">
            <svg class="empty-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M2.25 21h19.5M3 11.25a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 11.25V3.545M3 11.25V3.545A2.25 2.25 0 015.25 1.5h13.5A2.25 2.25 0 0121 3.545" />
            </svg>
            <h3>Belum ada data perizinan</h3>
            <p>Klik tombol di atas untuk menambahkan data perizinan pertama Anda.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive card">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Id</th>
                        <th>Kemana</th>
                        <th>Sama siapa</th>
                        <th>Berapa orang</th>
                        <?php if ($userSession->hasPermission('update_perizinan') || $userSession->hasPermission('delete_perizinan')): ?>
                            <th class="text-center">Aksi</th>
                        <?php endif; ?>
                    </tr>
                    <tr class="filter-row">
                    <td>
                        <input type="text" name="id" form="filter-form" value="<?= Html::encode($filters['id'] ?? '') ?>" class="form-control" placeholder="Cari ID...">
                    </td>
                    <td>
                        <input type="text" name="kemana" form="filter-form" value="<?= Html::encode($filters['kemana'] ?? '') ?>" class="form-control" placeholder="Cari Kemana...">
                    </td>
                    <td>
                        <input type="text" name="sama_siapa" form="filter-form" value="<?= Html::encode($filters['sama_siapa'] ?? '') ?>" class="form-control" placeholder="Cari Sama siapa...">
                    </td>
                    <td>
                        <input type="text" name="berapa_orang" form="filter-form" value="<?= Html::encode($filters['berapa_orang'] ?? '') ?>" class="form-control" placeholder="Cari Berapa orang...">
                    </td>
                        <?php if ($userSession->hasPermission('update_perizinan') || $userSession->hasPermission('delete_perizinan')): ?>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="submit" form="filter-form" class="btn btn-sm btn-primary">Cari</button>
                                    <a href="<?= $urlGenerator->generate('perizinan/index') ?>" class="btn btn-sm btn-secondary">Reset</a>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($perizinanList)): ?>
                        <?php $colCount = 4 + ($userSession->hasPermission('update_perizinan') || $userSession->hasPermission('delete_perizinan') ? 1 : 0); ?>
                        <tr>
                            <td colspan="<?= $colCount ?>" class="text-center text-muted py-4">
                                Tidak ada data yang cocok dengan pencarian.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($perizinanList as $perizinan): ?>
                            <tr>
                            <td><span class="badge badge-id"><?= Html::encode((string)$perizinan->id) ?></span></td>
                            <td><?= Html::encode((string)$perizinan->kemana) ?></td>
                            <td><?= Html::encode((string)$perizinan->samaSiapa) ?></td>
                            <td><?= Html::encode((string)$perizinan->berapaOrang) ?></td>
                                <?php if ($userSession->hasPermission('update_perizinan') || $userSession->hasPermission('delete_perizinan')): ?>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($userSession->hasPermission('update_perizinan')): ?>
                                                <a href="<?= $urlGenerator->generate('perizinan/update', ['id' => $perizinan->id]) ?>" class="btn-action btn-edit" title="Edit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                    </svg>
                                                    Edit
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($userSession->hasPermission('delete_perizinan')): ?>
                                                <form action="<?= $urlGenerator->generate('perizinan/delete', ['id' => $perizinan->id]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" style="display:inline;">
                                                    <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
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
