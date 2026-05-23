<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $guruList
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Data Guru - List');
?>

<div class="crud-container">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Data Guru</h1>
            <p class="crud-subtitle">Kelola informasi guru pondok pesantren dengan mudah dan cepat.</p>
        </div>
        <a href="<?= $urlGenerator->generate('guru/create') ?>" class="btn btn-primary">
            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Guru
        </a>
    </div>

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
                        <th class="text-center">Aksi</th>
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
                                <div class="action-buttons">
                                    <a href="<?= $urlGenerator->generate('guru/update', ['kdg' => $guru->kdg]) ?>" class="btn-action btn-edit" title="Edit Guru">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                        Edit
                                    </a>
                                    <form action="<?= $urlGenerator->generate('guru/delete', ['kdg' => $guru->kdg]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru ini?');" style="display:inline;">
                                        <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                                        <button type="submit" class="btn-action btn-delete" title="Hapus Guru">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
