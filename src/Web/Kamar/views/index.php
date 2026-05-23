<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Kamar\Kamar[] $kamarList
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 * @var string|null $successMsg
 * @var array $errorMsgs
 * @var \App\Web\Auth\UserSession $userSession
 */

$this->setTitle('Data Kamar - List');
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
            <h1 class="crud-title">Data Kamar</h1>
            <p class="crud-subtitle">Kelola informasi kamar pondok pesantren dengan mudah dan cepat.</p>
        </div>
        <?php if ($userSession->hasPermission('create_kamar')): ?>
            <a href="<?= $urlGenerator->generate('kamar/create') ?>" class="btn btn-primary">
                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Kamar
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($kamarList)): ?>
        <div class="empty-state">
            <svg class="empty-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M2.25 21h19.5M3 11.25a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 11.25V3.545M3 11.25V3.545A2.25 2.25 0 015.25 1.5h13.5A2.25 2.25 0 0121 3.545" />
            </svg>
            <h3>Belum ada data kamar</h3>
            <p>Klik tombol di atas untuk menambahkan data kamar pertama Anda.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kamar</th>
                        <th>Kapasitas</th>
                        <?php if ($userSession->hasPermission('update_kamar') || $userSession->hasPermission('delete_kamar')): ?>
                            <th class="text-center">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kamarList as $kamar): ?>
                        <tr>
                            <td><span class="badge badge-id"><?= Html::encode((string)$kamar->id) ?></span></td>
                            <td><strong><?= Html::encode($kamar->namaKamar) ?></strong></td>
                            <td><?= Html::encode((string)$kamar->kapasitas) ?> Orang</td>
                            <?php if ($userSession->hasPermission('update_kamar') || $userSession->hasPermission('delete_kamar')): ?>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($userSession->hasPermission('update_kamar')): ?>
                                            <a href="<?= $urlGenerator->generate('kamar/update', ['id' => $kamar->id]) ?>" class="btn-action btn-edit" title="Edit Kamar">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                                Edit
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($userSession->hasPermission('delete_kamar')): ?>
                                            <form action="<?= $urlGenerator->generate('kamar/delete', ['id' => $kamar->id]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data kamar ini?');" style="display:inline;">
                                                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                                                <button type="submit" class="btn-action btn-delete" title="Hapus Kamar">
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
