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
                                        <?= Html::encode($kd->kendala) ?>
                                    </h4>
                                    <span class="badge <?= $kd->jenis === 'terbuka' ? 'badge-open' : 'badge-closed' ?>">
                                        <?= Html::encode($kd->jenis) ?>
                                    </span>
                                </div>
                                <?php if ($kd->solusiSingkat !== null): ?>
                                    <p class="text-xs text-muted mb-2"><?= Html::encode($kd->solusiSingkat) ?></p>
                                <?php endif; ?>

                                <?php if (!empty($kd->fotos)): ?>
                                     <?php 
                                         $kdUrls = array_map(fn($f) => $baseUrl . '/' . $f->filePath, $kd->fotos);
                                         $kdUrlsJson = htmlspecialchars(json_encode($kdUrls), ENT_QUOTES, 'UTF-8');
                                     ?>
                                     <div class="d-flex flex-wrap gap-1 mb-2">
                                         <?php foreach ($kd->fotos as $fIndex => $foto): ?>
                                             <a href="javascript:void(0)" onclick="openImageGalleryModal('<?= Html::encode(addslashes($kd->kendala)) ?>', <?= $kdUrlsJson ?>, <?= $fIndex ?>)">
                                                 <img src="<?= Html::encode($baseUrl . '/' . $foto->filePath) ?>" alt="Foto Kendala" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid rgba(255,255,255,0.15);">
                                             </a>
                                         <?php endforeach; ?>
                                     </div>
                                 <?php endif; ?>

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
                <form action="<?= $urlGenerator->generate('program/add-kendala', ['id' => $model->id]) ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                    <div class="form-group mb-2">
                        <input type="text" name="kendala" class="form-control form-control-sm" placeholder="Kendala" required>
                    </div>
                    <div class="form-group mb-2">
                        <textarea name="solusi_singkat" class="form-control form-control-sm" placeholder="Solusi Singkat (Opsional)" rows="2"></textarea>
                    </div>
                    <div class="form-group mb-2">
                        <input type="file" name="files[]" class="form-control form-control-xs" accept="image/*" multiple>
                        <div class="text-muted" style="font-size: 10px; margin-top: 2px;">Opsional: Foto pendukung (JPG, PNG, WEBP, Maks. 2MB per file)</div>
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
                        <input type="file" name="file" class="form-control form-control-xs" accept=".pdf,image/*">
                        <div class="text-muted" style="font-size: 10px; margin-top: 2px;">Format: PDF, JPG, JPEG, PNG, GIF (Maks. 2MB)</div>
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
                    <div class="d-flex flex-col gap-3 max-h-350 overflow-y-auto">
                        <?php foreach ($dokumentasiList as $doc): ?>
                            <div class="dokumentasi-item p-3 border rounded" style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.08) !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h4 class="text-sm fw-bold m-0"><?= Html::encode($doc->judul) ?></h4>
                                    <form action="<?= $urlGenerator->generate('program/delete-dokumentasi', ['id' => $model->id, 'dokumentasiId' => $doc->id]) ?>" method="POST" onsubmit="return confirm('Hapus dokumentasi ini?');" class="m-0">
                                        <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                        <button type="submit" class="btn-inline-delete" title="Hapus">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                                
                                <?php if (!empty($doc->fotos)): ?>
                                     <?php 
                                         $docUrls = array_map(fn($f) => $baseUrl . '/' . $f->filePath, $doc->fotos);
                                         $docUrlsJson = htmlspecialchars(json_encode($docUrls), ENT_QUOTES, 'UTF-8');
                                     ?>
                                     <div class="grid grid-img-2col gap-1">
                                         <?php foreach ($doc->fotos as $fIndex => $foto): ?>
                                             <a href="javascript:void(0)" onclick="openImageGalleryModal('<?= Html::encode(addslashes($doc->judul)) ?>', <?= $docUrlsJson ?>, <?= $fIndex ?>)">
                                                 <img src="<?= Html::encode($baseUrl . '/' . $foto->filePath) ?>" class="dokumentasi-img" alt="Foto Dokumentasi" style="height: 80px; object-fit: cover;">
                                             </a>
                                         <?php endforeach; ?>
                                     </div>
                                 <?php else: ?>
                                     <p class="text-xs text-muted">Tidak ada foto.</p>
                                 <?php endif; ?>
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
                        <input type="file" name="files[]" class="form-control form-control-xs" accept="image/*" multiple required>
                        <div class="text-muted" style="font-size: 10px; margin-top: 2px;">Hanya Gambar (JPG, PNG, WEBP). Bisa pilih beberapa foto sekaligus (Maks. 2MB per file)</div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary btn-w-full">Unggah Gambar</button>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- Image Gallery Modal -->
<div id="image-gallery-modal" class="modal-overlay">
    <div class="modal-container" style="max-width: 600px; padding: 20px;">
        <div class="modal-header" style="margin-bottom: 12px;">
            <h3 class="modal-title" id="gallery-modal-title" style="font-size: 1.05rem;">Galeri Foto</h3>
            <button type="button" class="modal-close" onclick="closeImageGalleryModal()">&times;</button>
        </div>
        <div class="modal-body d-flex flex-col gap-2">
            <!-- Large Active Image -->
            <div class="gallery-active-container text-center border rounded p-2" style="background: rgba(0,0,0,0.3); min-height: 250px; display: flex; align-items: center; justify-content: center; overflow: hidden; border-color: rgba(255,255,255,0.08) !important;">
                <img id="gallery-active-image" src="" alt="Active Photo" style="max-height: 350px; max-width: 100%; object-fit: contain; border-radius: 6px; transition: opacity 0.2s ease;">
            </div>
            <!-- Thumbnails Row -->
            <div id="gallery-thumbnails" class="d-flex gap-2 overflow-x-auto py-1" style="scrollbar-width: thin; max-width: 100%;">
                <!-- Dynamically populated -->
            </div>
        </div>
    </div>
</div>

<script>
function openImageGalleryModal(title, images, startIndex = 0) {
    if (!images || images.length === 0) return;

    document.getElementById('gallery-modal-title').textContent = title;
    const activeImg = document.getElementById('gallery-active-image');
    activeImg.src = images[startIndex];

    const thumbnailsContainer = document.getElementById('gallery-thumbnails');
    thumbnailsContainer.innerHTML = '';

    images.forEach((imgUrl, index) => {
        const thumb = document.createElement('img');
        thumb.src = imgUrl;
        thumb.style.width = '50px';
        thumb.style.height = '50px';
        thumb.style.objectFit = 'cover';
        thumb.style.borderRadius = '4px';
        thumb.style.cursor = 'pointer';
        thumb.style.border = index === startIndex ? '2px solid var(--primary)' : '2px solid transparent';
        thumb.style.transition = 'all 0.15s ease';

        thumb.onclick = function() {
            Array.from(thumbnailsContainer.children).forEach(child => {
                child.style.border = '2px solid transparent';
            });
            thumb.style.border = '2px solid var(--primary)';
            activeImg.style.opacity = '0.3';
            setTimeout(() => {
                activeImg.src = imgUrl;
                activeImg.style.opacity = '1';
            }, 100);
        };

        thumbnailsContainer.appendChild(thumb);
    });

    document.getElementById('image-gallery-modal').classList.add('open');
}

function closeImageGalleryModal() {
    document.getElementById('image-gallery-modal').classList.remove('open');
}

document.getElementById('image-gallery-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageGalleryModal();
    }
});
</script>
