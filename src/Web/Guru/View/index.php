<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Guru\Model\GuruEntity[] $guruList
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 * @var string|null $successMsg
 * @var array $errorMsgs
 * @var \App\Web\Auth\Model\UserSession $userSession
 */

$this->setTitle('Data Guru - List');
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
                <strong style="display:block;margin-bottom:4px;">Gagal memproses beberapa data:</strong>
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
            <h1 class="crud-title">Data Guru</h1>
            <p class="crud-subtitle">Kelola informasi guru pondok pesantren dengan mudah dan cepat.</p>
        </div>
        <?php if ($userSession->hasPermission('create_guru')): ?>
            <a href="<?= $urlGenerator->generate('guru/create') ?>" class="btn btn-primary">
                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Guru
            </a>
        <?php endif; ?>
    </div>

    <!-- Card Import Excel Premium -->
    <?php if ($userSession->hasPermission('create_guru')): ?>
        <div class="card import-card" style="margin-bottom: 24px;">
            <div class="import-card-header">
                <h3 class="import-card-title">Import Massal via Excel</h3>
                <a href="<?= $urlGenerator->generate('guru/download-template') ?>" class="btn-link">
                    <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Unduh Template Excel
                </a>
            </div>
            <form action="<?= $urlGenerator->generate('guru/upload') ?>" method="POST" enctype="multipart/form-data" class="import-form">
                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                <div class="import-input-group">
                    <div class="file-input-wrapper">
                        <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                        <span class="file-input-label">Pilih file Excel (.xlsx, .xls, .csv)...</span>
                    </div>
                    <button type="submit" class="btn btn-secondary">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        Proses Import (Upsert)
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if (empty($guruList)): ?>
        <div class="empty-state">
            <svg class="empty-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
            </svg>
            <h3>Belum ada data guru</h3>
            <p>Klik tombol di atas untuk menambahkan data guru pertama Anda.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive card">
            <table class="table">
                <thead>
                    <tr>
                        <th>KDG</th>
                        <th>Stambuk</th>
                        <th>Nama Lengkap</th>
                        <th>Daerah</th>
                        <th>Konsulat</th>
                        <th>Email</th>
                        <th>Nomor Telefon</th>
                        <th>Kamar</th>
                        <?php if ($userSession->hasPermission('update_guru') || $userSession->hasPermission('delete_guru')): ?>
                            <th class="text-center">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($guruList as $guru): ?>
                        <tr>
                            <td><span class="badge badge-id"><?= Html::encode((string)$guru->kdg) ?></span></td>
                            <td><strong><?= Html::encode($guru->stambuk) ?></strong></td>
                            <td><?= Html::encode($guru->nama) ?></td>
                            <td><?= Html::encode($guru->daerah) ?></td>
                            <td><?= Html::encode($guru->konsulat) ?></td>
                            <td><span class="email-text"><?= Html::encode($guru->email) ?></span></td>
                            <td><?= Html::encode($guru->noTelp) ?></td>
                            <td>
                                <?php if ($guru->namaKamar): ?>
                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 600; font-size: 0.8rem; padding: 4px 8px; border-radius: 4px;">
                                        <?= Html::encode($guru->namaKamar) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 0.8rem; font-style: italic;">Belum Diatur</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($userSession->hasPermission('update_guru') || $userSession->hasPermission('delete_guru')): ?>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($userSession->hasPermission('update_guru')): ?>
                                            <a href="<?= $urlGenerator->generate('guru/update', ['kdg' => $guru->kdg]) ?>" class="btn-action btn-edit" title="Edit Guru">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                                Edit
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($userSession->hasPermission('delete_guru')): ?>
                                            <form action="<?= $urlGenerator->generate('guru/delete', ['kdg' => $guru->kdg]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru ini?');" style="display:inline;">
                                                <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                                                <button type="submit" class="btn-action btn-delete" title="Hapus Guru">
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

<script>
document.getElementById('excel_file').addEventListener('change', function(e) {
    var fileName = e.target.files[0] ? e.target.files[0].name : 'Pilih file Excel (.xlsx, .xls, .csv)...';
    document.querySelector('.file-input-label').textContent = fileName;
});
</script>
