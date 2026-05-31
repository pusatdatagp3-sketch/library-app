<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Program\Model\Program $model
 * @var App\Web\Program\Model\KendalaProgram[] $kendalaList
 * @var App\Web\Program\Model\Notulensi[] $notulensiList
 * @var App\Web\Program\Model\Dokumentasi[] $dokumentasiList
 * @var App\Web\Entitas\Model\Entitas $entitas
 * @var UrlGeneratorInterface $urlGenerator
 * @var \App\Web\Auth\Model\UserSession $userSession
 * @var string $backUrl
 * @var string|null $successMsg
 * @var array $errorMsgs
 */

$this->setTitle("Program Kerja - {$model->namaProgram}");
$baseUrl = $this->hasParameter('baseUrl') ? $this->getParameter('baseUrl') : '/teqic-yii3/public';
?>

<div class="crud-container">
    <div class="sticky-page-header">
        <a href="<?= $backUrl ?>" class="btn btn-sm btn-secondary">
            <i class="ri-arrow-left-line"></i> Kembali ke Entitas (<?= Html::encode($entitas->nama) ?>)
        </a>
    </div>

    <!-- Main Title Card -->
    <div class="card mb-4 overflow-hidden card-accent">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="d-flex gap-2 mb-2">
                    <span class="badge badge-primary-light">
                        <?= Html::encode(ucfirst($model->periode ?? 'tidak ditentukan')) ?>
                    </span>
                    <span class="badge <?= $model->status === 'active' ? 'badge-success' : 'badge-secondary' ?>">
                        <?= Html::encode(strtoupper($model->status)) ?>
                    </span>
                </div>
                <h1 class="text-2xl fw-extrabold mb-2 text-color"><?= Html::encode($model->namaProgram) ?></h1>
                <p class="text-muted mb-2">
                    <i class="ri-user-star-line"></i> Penanggung Jawab: <strong><?= Html::encode($model->penanggungJawab?->namaAnggota ?? 'Belum Ditentukan') ?></strong>
                </p>
                <div class="text-sm text-color border-top pt-3 mt-3">
                    <strong>Tupoksi:</strong>
                    <p class="m-0 mt-1 white-space-pre text-muted"><?= Html::encode($model->tupoksi ?? 'Tidak ada tupoksi khusus.') ?></p>
                </div>
            </div>

            <div class="d-flex flex-col gap-2 items-end">
                <a href="<?= $urlGenerator->generate('kanban/board', ['program_id' => $model->id]) ?>" class="btn btn-primary btn-w-full">
                    <i class="ri-kanban-view"></i> Papan Kanban
                </a>
                <div class="d-flex gap-2">
                    <a href="<?= $urlGenerator->generate('program/update', ['id' => $model->id]) ?>" class="btn btn-sm btn-outline-primary">
                        <i class="ri-pencil-line"></i> Edit
                    </a>
                    <form action="<?= $urlGenerator->generate('program/delete', ['id' => $model->id]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus program kerja ini?');" class="m-0">
                        <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="ri-delete-bin-line"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($successMsg): ?>
        <div class="alert alert-success mb-4">
            <i class="ri-checkbox-circle-fill alert-icon"></i>
            <div><?= Html::encode($successMsg) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMsgs)): ?>
        <div class="alert alert-danger mb-4">
            <i class="ri-error-warning-fill alert-icon"></i>
            <div>
                <?php foreach ($errorMsgs as $errorMsg): ?>
                    <p><?= Html::encode($errorMsg) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Three-column feature grid -->
    <div class="grid grid-auto-fit gap-4">

        <!-- COLUMN 1: KENDALA PROGRAM -->
        <div class="card p-5 d-flex flex-col" style="min-height:450px; justify-content:space-between;">
            <div>
                <h2 class="card-section-header">
                    <i class="ri-alert-line text-warning"></i> Kendala Program
                </h2>

                <?php if (empty($kendalaList)): ?>
                    <p class="text-muted text-center py-4">Tidak ada kendala yang dicatat.</p>
                <?php else: ?>
                    <div class="d-flex flex-col gap-2 max-h-350 overflow-y-auto">
                        <?php foreach ($kendalaList as $kd): ?>
                            <div class="kendala-item <?= $kd->jenis === 'terbuka' ? 'kendala-item-open' : 'kendala-item-closed' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h4 class="text-sm fw-bold m-0 mb-1 <?= $kd->jenis === 'tertutup' ? 'line-through text-muted' : 'text-color' ?>">
                                        <?= Html::encode($kd->judul) ?>
                                    </h4>
                                    <span class="badge <?= $kd->jenis === 'terbuka' ? 'badge-open' : 'badge-closed' ?>">
                                        <?= Html::encode($kd->jenis) ?>
                                    </span>
                                </div>
                                <p class="text-xs text-muted mb-2"><?= Html::encode($kd->deskripsi ?? '') ?></p>

                                <div class="d-flex justify-content-end gap-2 align-items-center">
                                    <?php if ($kd->jenis === 'terbuka'): ?>
                                        <form action="<?= $urlGenerator->generate('program/resolve-kendala', ['id' => $model->id, 'kendalaId' => $kd->id]) ?>" method="POST" class="m-0">
                                            <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-success text-xs py-1 px-3">
                                                <i class="ri-check-line"></i> Selesaikan
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form action="<?= $urlGenerator->generate('program/delete-kendala', ['id' => $model->id, 'kendalaId' => $kd->id]) ?>" method="POST" onsubmit="return confirm('Hapus kendala ini?');" class="m-0">
                                        <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger text-xs py-1 px-3" title="Hapus">
                                            <i class="ri-delete-bin-6-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mini-form-section">
                <h4 class="text-sm fw-bold mb-3"><i class="ri-add-line"></i> Laporkan Kendala</h4>
                <form action="<?= $urlGenerator->generate('program/add-kendala', ['id' => $model->id]) ?>" method="POST">
                    <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                    <div class="form-group mb-2">
                        <input type="text" name="judul" class="form-control form-control-sm" placeholder="Judul Kendala" required>
                    </div>
                    <div class="form-group mb-2">
                        <textarea name="deskripsi" class="form-control form-control-sm" placeholder="Deskripsi kendala..." rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary btn-w-full">Laporkan</button>
                </form>
            </div>
        </div>

        <!-- COLUMN 2: NOTULENSI -->
        <div class="card p-5 d-flex flex-col" style="min-height:450px; justify-content:space-between;">
            <div>
                <h2 class="card-section-header">
                    <i class="ri-file-text-line text-info"></i> Notulensi Rapat
                </h2>

                <?php if (empty($notulensiList)): ?>
                    <p class="text-muted text-center py-4">Belum ada notulensi.</p>
                <?php else: ?>
                    <div class="d-flex flex-col gap-2 max-h-350 overflow-y-auto">
                        <?php foreach ($notulensiList as $notulen): ?>
                            <div class="notulensi-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h4 class="text-sm fw-bold m-0"><?= Html::encode($notulen->judul) ?></h4>
                                    <form action="<?= $urlGenerator->generate('program/delete-notulensi', ['id' => $model->id, 'notulensiId' => $notulen->id]) ?>" method="POST" onsubmit="return confirm('Hapus notulensi ini?');" class="m-0">
                                        <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                        <button type="submit" class="btn-inline-delete" title="Hapus">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                                <p class="text-xs text-muted mb-2"><?= Html::encode($notulen->isi ?? '') ?></p>
                                <?php if ($notulen->filePath): ?>
                                    <a href="<?= Html::encode($baseUrl . '/' . $notulen->filePath) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="ri-download-2-line"></i> Download Lampiran
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mini-form-section">
                <h4 class="text-sm fw-bold mb-3"><i class="ri-add-line"></i> Unggah Notulensi Baru</h4>
                <form action="<?= $urlGenerator->generate('program/add-notulensi', ['id' => $model->id]) ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                    <div class="form-group mb-2">
                        <input type="text" name="judul" class="form-control form-control-sm" placeholder="Agenda Rapat" required>
                    </div>
                    <div class="form-group mb-2">
                        <textarea name="isi" class="form-control form-control-sm" placeholder="Catatan/Isi rapat..." rows="2"></textarea>
                    </div>
                    <div class="form-group mb-2">
                        <input type="file" name="file" class="form-control form-control-xs">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary btn-w-full">Unggah Notulensi</button>
                </form>
            </div>
        </div>

        <!-- COLUMN 3: DOKUMENTASI -->
        <div class="card p-5 d-flex flex-col" style="min-height:450px; justify-content:space-between;">
            <div>
                <h2 class="card-section-header">
                    <i class="ri-image-line text-success"></i> Dokumentasi Program
                </h2>

                <?php if (empty($dokumentasiList)): ?>
                    <p class="text-muted text-center py-4">Belum ada dokumentasi terunggah.</p>
                <?php else: ?>
                    <div class="grid grid-img-2col gap-2 max-h-350 overflow-y-auto">
                        <?php foreach ($dokumentasiList as $doc): ?>
                            <div class="dokumentasi-item d-flex flex-col" style="justify-content:space-between;">
                                <div>
                                    <?php if ($doc->filePath): ?>
                                        <img src="<?= Html::encode($baseUrl . '/' . $doc->filePath) ?>" class="dokumentasi-img" alt="<?= Html::encode($doc->judul) ?>">
                                    <?php endif; ?>
                                    <div class="text-sm fw-bold word-break"><?= Html::encode($doc->judul) ?></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 border-top pt-1">
                                    <a href="<?= Html::encode($baseUrl . '/' . $doc->filePath) ?>" target="_blank" class="text-sm text-primary">
                                        <i class="ri-external-link-line"></i> Buka
                                    </a>
                                    <form action="<?= $urlGenerator->generate('program/delete-dokumentasi', ['id' => $model->id, 'dokumentasiId' => $doc->id]) ?>" method="POST" onsubmit="return confirm('Hapus dokumentasi ini?');" class="m-0">
                                        <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                        <button type="submit" class="btn-inline-delete" title="Hapus">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mini-form-section">
                <h4 class="text-sm fw-bold mb-3"><i class="ri-add-line"></i> Unggah Dokumentasi</h4>
                <form action="<?= $urlGenerator->generate('program/add-dokumentasi', ['id' => $model->id]) ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                    <div class="form-group mb-2">
                        <input type="text" name="judul" class="form-control form-control-sm" placeholder="Nama Dokumentasi / Kegiatan" required>
                    </div>
                    <div class="form-group mb-2">
                        <input type="file" name="file" class="form-control form-control-xs" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary btn-w-full">Unggah Gambar</button>
                </form>
            </div>
        </div>

    </div>
</div>
