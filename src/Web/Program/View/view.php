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
$baseUrl = $this->getParameter('baseUrl') ?? '/teqic-yii3/public';
?>

<div class="crud-container">
    <!-- Back to Entitas Details -->
    <div class="mb-4">
        <a href="<?= $backUrl ?>" class="btn btn-sm btn-secondary" style="display: inline-flex; align-items: center; gap: 0.25rem;">
            <i class="ri-arrow-left-line"></i> Kembali ke Entitas (<?= Html::encode($entitas->nama) ?>)
        </a>
    </div>

    <!-- Main Title Card -->
    <div class="card mb-4 overflow-hidden" style="border-left: 5px solid var(--primary);">
        <div class="d-flex justify-content-between align-items-start" style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <span class="badge badge-primary-light" style="background: var(--primary-light); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 50px; font-weight: 600; font-size: 0.75rem;">
                        <?= Html::encode(ucfirst($model->periode ?? 'tidak ditentukan')) ?>
                    </span>
                    <span class="badge badge-<?= $model->status === 'active' ? 'success' : 'secondary' ?>" style="padding: 0.25rem 0.75rem; border-radius: 50px; font-weight: 600; font-size: 0.75rem;">
                        <?= Html::encode(strtoupper($model->status)) ?>
                    </span>
                </div>
                <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--text-color);">
                    <?= Html::encode($model->namaProgram) ?>
                </h1>
                <p class="text-muted" style="margin-bottom: 0.5rem;">
                    <i class="ri-user-star-line"></i> Penanggung Jawab: <strong><?= Html::encode($model->penanggungJawab?->namaAnggota ?? 'Belum Ditentukan') ?></strong>
                </p>
                <div style="font-size: 0.9rem; color: var(--text-color); border-top: 1px solid rgba(0,0,0,0.05); padding-top: 0.75rem; margin-top: 0.75rem;">
                    <strong>Tupoksi:</strong>
                    <p style="margin: 0.25rem 0 0 0; white-space: pre-wrap; color: var(--text-muted);"><?= Html::encode($model->tupoksi ?? 'Tidak ada tupoksi khusus.') ?></p>
                </div>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;">
                <a href="<?= $urlGenerator->generate('kanban/board', ['program_id' => $model->id]) ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 700; width: 100%; justify-content: center;">
                    <i class="ri-kanban-view"></i> Papan Kanban
                </a>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="<?= $urlGenerator->generate('program/update', ['id' => $model->id]) ?>" class="btn btn-sm btn-outline-primary" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                        <i class="ri-pencil-line"></i> Edit
                    </a>
                    <form action="<?= $urlGenerator->generate('program/delete', ['id' => $model->id]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus program kerja ini?');" style="margin: 0;">
                        <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                        <button type="submit" class="btn btn-sm btn-danger" style="display: inline-flex; align-items: center; gap: 0.25rem;">
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

    <!-- Features Tabs Layout -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; align-items: start;">
        
        <!-- COLUMN 1: KENDALA PROGRAM -->
        <div class="card" style="padding: 1.5rem; min-height: 450px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h2 style="font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.5rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ri-alert-line text-warning"></i> Kendala Program
                </h2>

                <?php if (empty($kendalaList)): ?>
                    <p class="text-muted text-center py-4">Tidak ada kendala yang dicatat.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 350px; overflow-y: auto;">
                        <?php foreach ($kendalaList as $kd): ?>
                            <div style="border: 1px solid rgba(0,0,0,0.05); border-left: 4px solid <?= $kd->jenis === 'terbuka' ? '#f59e0b' : '#10b981' ?>; border-radius: 6px; padding: 0.75rem; background: rgba(0,0,0,0.01);">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0 0 0.25rem 0; text-decoration: <?= $kd->jenis === 'tertutup' ? 'line-through' : 'none' ?>; color: <?= $kd->jenis === 'tertutup' ? 'var(--text-muted)' : 'var(--text-color)' ?>;">
                                        <?= Html::encode($kd->judul) ?>
                                    </h4>
                                    <span class="badge" style="font-size: 0.65rem; background: <?= $kd->jenis === 'terbuka' ? 'rgba(245,158,11,0.1)' : 'rgba(16,185,129,0.1)' ?>; color: <?= $kd->jenis === 'terbuka' ? '#f59e0b' : '#10b981' ?>; border-radius: 4px; padding: 0.1rem 0.3rem;">
                                        <?= Html::encode($kd->jenis) ?>
                                    </span>
                                </div>
                                <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0.25rem 0 0.5rem 0;"><?= Html::encode($kd->deskripsi ?? '') ?></p>
                                
                                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; align-items: center;">
                                    <?php if ($kd->jenis === 'terbuka'): ?>
                                        <form action="<?= $urlGenerator->generate('program/resolve-kendala', ['id' => $model->id, 'kendalaId' => $kd->id]) ?>" method="POST" style="margin:0;">
                                            <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-success" style="font-size: 0.75rem; padding: 0.15rem 0.4rem;">
                                                <i class="ri-check-line"></i> Selesaikan
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form action="<?= $urlGenerator->generate('program/delete-kendala', ['id' => $model->id, 'kendalaId' => $kd->id]) ?>" method="POST" onsubmit="return confirm('Hapus kendala ini?');" style="margin:0;">
                                        <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size: 0.75rem; padding: 0.15rem 0.4rem;" title="Hapus">
                                            <i class="ri-delete-bin-6-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Form to Add Kendala -->
            <div style="margin-top: 1.5rem; border-top: 1px dashed rgba(0,0,0,0.1); padding-top: 1rem;">
                <h4 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 0.75rem;"><i class="ri-add-line"></i> Laporkan Kendala</h4>
                <form action="<?= $urlGenerator->generate('program/add-kendala', ['id' => $model->id]) ?>" method="POST">
                    <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                    <div class="form-group mb-2">
                        <input type="text" name="judul" class="form-control" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;" placeholder="Judul Kendala" required>
                    </div>
                    <div class="form-group mb-2">
                        <textarea name="deskripsi" class="form-control" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;" placeholder="Deskripsi kendala..." rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100" style="width:100%; justify-content:center; font-size:0.8rem; padding:0.4rem;">
                        Laporkan
                    </button>
                </form>
            </div>
        </div>

        <!-- COLUMN 2: NOTULENSI -->
        <div class="card" style="padding: 1.5rem; min-height: 450px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h2 style="font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.5rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ri-file-text-line text-info"></i> Notulensi Rapat
                </h2>

                <?php if (empty($notulensiList)): ?>
                    <p class="text-muted text-center py-4">Belum ada notulensi.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 350px; overflow-y: auto;">
                        <?php foreach ($notulensiList as $notulen): ?>
                            <div style="border: 1px solid rgba(0,0,0,0.05); border-radius: 6px; padding: 0.75rem; background: rgba(0,0,0,0.01);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                    <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0;"><?= Html::encode($notulen->judul) ?></h4>
                                    
                                    <form action="<?= $urlGenerator->generate('program/delete-notulensi', ['id' => $model->id, 'notulensiId' => $notulen->id]) ?>" method="POST" onsubmit="return confirm('Hapus notulensi ini?');" style="margin:0;">
                                        <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                        <button type="submit" class="btn btn-sm btn-icon text-danger" style="background:none; border:none; padding:0; cursor:pointer;">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                                <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0 0 0.5rem 0;"><?= Html::encode($notulen->isi ?? '') ?></p>
                                
                                <?php if ($notulen->filePath): ?>
                                    <a href="<?= Html::encode($baseUrl . '/' . $notulen->filePath) ?>" target="_blank" class="btn btn-sm btn-outline-primary" style="font-size: 0.75rem; padding: 0.15rem 0.4rem; display: inline-flex; align-items: center; gap: 0.2rem;">
                                        <i class="ri-download-2-line"></i> Download Lampiran
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Form to Add Notulensi -->
            <div style="margin-top: 1.5rem; border-top: 1px dashed rgba(0,0,0,0.1); padding-top: 1rem;">
                <h4 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 0.75rem;"><i class="ri-add-line"></i> Unggah Notulensi Baru</h4>
                <form action="<?= $urlGenerator->generate('program/add-notulensi', ['id' => $model->id]) ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                    <div class="form-group mb-2">
                        <input type="text" name="judul" class="form-control" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;" placeholder="Agenda Rapat" required>
                    </div>
                    <div class="form-group mb-2">
                        <textarea name="isi" class="form-control" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;" placeholder="Catatan/Isi rapat..." rows="2"></textarea>
                    </div>
                    <div class="form-group mb-2">
                        <input type="file" name="file" class="form-control" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100" style="width:100%; justify-content:center; font-size:0.8rem; padding:0.4rem;">
                        Unggah Notulensi
                    </button>
                </form>
            </div>
        </div>

        <!-- COLUMN 3: DOKUMENTASI -->
        <div class="card" style="padding: 1.5rem; min-height: 450px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h2 style="font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.5rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ri-image-line text-success"></i> Dokumentasi Program
                </h2>

                <?php if (empty($dokumentasiList)): ?>
                    <p class="text-muted text-center py-4">Belum ada dokumentasi terunggah.</p>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; max-height: 350px; overflow-y: auto;">
                        <?php foreach ($dokumentasiList as $doc): ?>
                            <div style="border: 1px solid rgba(0,0,0,0.05); border-radius: 6px; padding: 0.5rem; position: relative; background: rgba(0,0,0,0.01); display: flex; flex-direction: column; justify-content: space-between;">
                                <div style="position: relative;">
                                    <?php if ($doc->filePath): ?>
                                        <img src="<?= Html::encode($baseUrl . '/' . $doc->filePath) ?>" style="width: 100%; height: 90px; object-fit: cover; border-radius: 4px; margin-bottom: 0.25rem;">
                                    <?php endif; ?>
                                    <div style="font-size: 0.8rem; font-weight: 700; line-height: 1.2; word-break: break-word;"><?= Html::encode($doc->judul) ?></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 0.25rem;">
                                    <a href="<?= Html::encode($baseUrl . '/' . $doc->filePath) ?>" target="_blank" style="font-size: 0.75rem; color: var(--primary);">
                                        <i class="ri-external-link-line"></i> Buka
                                    </a>
                                    <form action="<?= $urlGenerator->generate('program/delete-dokumentasi', ['id' => $model->id, 'dokumentasiId' => $doc->id]) ?>" method="POST" onsubmit="return confirm('Hapus dokumentasi ini?');" style="margin:0;">
                                        <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                        <button type="submit" class="btn btn-sm btn-icon text-danger" style="background:none; border:none; padding:0; cursor:pointer;">
                                            <i class="ri-delete-bin-line" style="font-size: 0.95rem;"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Form to Add Dokumentasi -->
            <div style="margin-top: 1.5rem; border-top: 1px dashed rgba(0,0,0,0.1); padding-top: 1rem;">
                <h4 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 0.75rem;"><i class="ri-add-line"></i> Unggah Dokumentasi</h4>
                <form action="<?= $urlGenerator->generate('program/add-dokumentasi', ['id' => $model->id]) ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                    <div class="form-group mb-2">
                        <input type="text" name="judul" class="form-control" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;" placeholder="Nama Dokumentasi / Kegiatan" required>
                    </div>
                    <div class="form-group mb-2">
                        <input type="file" name="file" class="form-control" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100" style="width:100%; justify-content:center; font-size:0.8rem; padding:0.4rem;">
                        Unggah Gambar
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
