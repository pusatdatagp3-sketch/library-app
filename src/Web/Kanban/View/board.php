<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Program\Model\Program $program
 * @var App\Web\Entitas\Model\Entitas $entitas
 * @var App\Web\Kanban\Model\KanbanColumn[] $columns
 * @var App\Web\Task\Model\Task[] $tasks
 * @var App\Web\Entitas\Model\AnggotaEntitas[] $members
 * @var App\Web\Task\Model\TaskProgressLog[][] $taskLogs
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $successMsg
 * @var array $errorMsgs
 */

$this->setTitle("Papan Kanban - {$program->namaProgram}");
?>

<meta name="csrf-token" content="<?= Html::encode($this->getParameter('csrf')) ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css">

<div class="kanban-board-wrapper">
    <!-- Top Action Row -->
    <div class="sticky-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 text-sm text-muted mb-1">
                <a href="<?= $urlGenerator->generate('program/view', ['id' => $program->id]) ?>" class="text-muted">
                    <?= Html::encode($program->namaProgram) ?>
                </a>
                <span>/</span>
                <span>Papan Kanban</span>
            </div>
            <h1 class="text-xl fw-extrabold m-0 d-flex align-items-center gap-2">
                <i class="ri-kanban-view text-primary"></i> Papan Kanban: <?= Html::encode($program->namaProgram) ?>
            </h1>
        </div>

        <div class="d-flex gap-2">
            <button onclick="openModal('add-task-modal')" class="btn btn-primary">
                <i class="ri-add-line"></i> Tambah Tugas
            </button>
            <a href="<?= $urlGenerator->generate('program/view', ['id' => $program->id]) ?>" class="btn btn-secondary">
                <i class="ri-arrow-go-back-line"></i> Detail Program
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    <?php if ($successMsg): ?>
        <div class="alert alert-success mb-3">
            <i class="ri-checkbox-circle-fill alert-icon"></i>
            <div><?= Html::encode($successMsg) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMsgs)): ?>
        <div class="alert alert-danger mb-3">
            <i class="ri-error-warning-fill alert-icon"></i>
            <div>
                <?php foreach ($errorMsgs as $err): ?>
                    <p><?= Html::encode($err) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Kanban board container -->
    <div class="kanban-columns-container">
        <?php foreach ($columns as $col): ?>
            <?php 
                $colTasks = array_filter($tasks, fn($t) => $t->kanbanColumnId === $col->id);
                usort($colTasks, fn($a, $b) => $a->urutan <=> $b->urutan);
            ?>
            <div class="kanban-col">
                <div class="kanban-col-header">
                    <span><?= Html::encode($col->nama) ?></span>
                    <span class="badge badge-primary-light">
                        <?= count($colTasks) ?>
                    </span>
                </div>

                <div class="kanban-column-body" data-column-id="<?= $col->id ?>">
                    <?php foreach ($colTasks as $task): ?>
                        <div class="kanban-card hover-glow" data-task-id="<?= $task->id ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h4 class="text-sm fw-bold m-0 text-color"><?= Html::encode($task->judul) ?></h4>
                                <div class="position-relative">
                                    <button onclick="openModal('task-log-modal-<?= $task->id ?>')" class="kanban-card-more-btn" title="Lihat Log">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    <button onclick="toggleDropdown(event, 'card-actions-<?= $task->id ?>')" class="kanban-card-more-btn">
                                        <i class="ri-more-2-line"></i>
                                    </button>
                                    <div id="card-actions-<?= $task->id ?>" class="kanban-dropdown-menu">
                                        <button onclick="openModal('edit-task-modal-<?= $task->id ?>')" class="kanban-dropdown-item">
                                            <i class="ri-pencil-line"></i> Edit
                                        </button>
                                        <form action="<?= $urlGenerator->generate('kanban/delete-task', ['program_id' => $program->id, 'id' => $task->id]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?');" class="m-0">
                                            <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                            <button type="submit" class="kanban-dropdown-item text-danger">
                                                <i class="ri-delete-bin-line"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-muted mb-2 text-ellipsis">
                                <?= Html::encode($task->deskripsi ?? 'Tidak ada deskripsi.') ?>
                            </p>

                            <!-- Progress Bar -->
                            <div class="mb-2">
                                <div class="d-flex justify-content-between text-xs text-muted mb-1">
                                    <span>Progres</span>
                                    <span><?= $task->progress ?>%</span>
                                </div>
                                <div class="task-progress-bar-container">
                                    <div class="task-progress-bar bar-todo" style="width: <?= $task->progress ?>%;"></div>
                                </div>
                            </div>

                            <!-- Footer of Card -->
                            <div class="d-flex justify-content-between align-items-center text-xs mt-2">
                                <span class="badge badge-primary-light">
                                    <i class="ri-user-star-line text-xs"></i> <?= Html::encode($task->assignedUser?->namaAnggota ?? 'Unassigned') ?>
                                </span>
                                
                                <?php if ($task->deadline): ?>
                                    <span class="inline-flex align-items-center gap-1 fw-semibold <?= (new \DateTime() > $task->deadline) ? 'text-danger' : 'text-muted' ?>">
                                        <i class="ri-calendar-line"></i> <?= $task->deadline->format('d M') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- MODAL EDIT TUGAS -->
                        <div id="edit-task-modal-<?= $task->id ?>" class="kanban-modal">
                            <div class="kanban-modal-content">
                                <span onclick="closeModal('edit-task-modal-<?= $task->id ?>')" class="kanban-modal-close">&times;</span>
                                <h3 class="m-0 mb-4 fw-extrabold"><i class="ri-edit-box-line text-primary"></i> Edit Tugas</h3>
                                <form action="<?= $urlGenerator->generate('kanban/edit-task', ['program_id' => $program->id, 'id' => $task->id]) ?>" method="POST" class="form-grid">
                                    <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label text-sm" for="judul-<?= $task->id ?>">Judul Tugas</label>
                                        <input type="text" id="judul-<?= $task->id ?>" name="judul" class="form-control" value="<?= Html::encode($task->judul) ?>" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label text-sm" for="deskripsi-<?= $task->id ?>">Deskripsi</label>
                                        <textarea id="deskripsi-<?= $task->id ?>" name="deskripsi" class="form-control" rows="3"><?= Html::encode($task->deskripsi ?? '') ?></textarea>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label text-sm" for="assigned_to-<?= $task->id ?>">PIC</label>
                                        <select id="assigned_to-<?= $task->id ?>" name="assigned_to" class="form-control">
                                            <option value="">-- Pilih PIC --</option>
                                            <?php foreach ($members as $mb): ?>
                                                <option value="<?= $mb->id ?>" <?= $task->assignedTo === $mb->id ? 'selected' : '' ?>>
                                                    <?= Html::encode($mb->namaAnggota) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label text-sm" for="progress-<?= $task->id ?>">Progress (%)</label>
                                        <input type="number" id="progress-<?= $task->id ?>" name="progress" class="form-control" min="0" max="100" value="<?= $task->progress ?>" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label text-sm" for="deadline-<?= $task->id ?>">Deadline</label>
                                        <input type="date" id="deadline-<?= $task->id ?>" name="deadline" class="form-control" value="<?= $task->deadline ? $task->deadline->format('Y-m-d') : '' ?>">
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-w-full py-2 mt-3">
                                        Simpan Perubahan
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- TASK LOG MODALS (dikumpulkan di luar board agar tidak terjebak overflow/stacking context) -->
<?php foreach ($tasks as $task): ?>
    <?= $this->render('./_task_log', [
        'task' => $task,
        'logs' => $taskLogs[$task->id] ?? [],
    ]) ?>
<?php endforeach; ?>

<!-- MODAL TAMBAH TUGAS -->
<div id="add-task-modal" class="kanban-modal">
    <div class="kanban-modal-content">
        <span onclick="closeModal('add-task-modal')" class="kanban-modal-close">&times;</span>
        <h3 class="m-0 mb-4 fw-extrabold"><i class="ri-add-circle-line text-primary"></i> Tambah Tugas Baru</h3>
        <form action="<?= $urlGenerator->generate('kanban/add-task', ['program_id' => $program->id]) ?>" method="POST" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
            
            <div class="form-group mb-3">
                <label class="form-label text-sm" for="add-judul">Judul Tugas</label>
                <input type="text" id="add-judul" name="judul" class="form-control" placeholder="Tugas baru..." required>
            </div>

            <div class="form-group mb-3">
                <label class="form-label text-sm" for="add-deskripsi">Deskripsi</label>
                <textarea id="add-deskripsi" name="deskripsi" class="form-control" placeholder="Detail rincian tugas..." rows="3"></textarea>
            </div>

            <div class="form-group mb-3">
                <label class="form-label text-sm" for="add-assigned_to">PIC</label>
                <select id="add-assigned_to" name="assigned_to" class="form-control">
                    <option value="">-- Pilih PIC --</option>
                    <?php foreach ($members as $mb): ?>
                        <option value="<?= $mb->id ?>"><?= Html::encode($mb->namaAnggota) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <label class="form-label text-sm" for="add-deadline">Deadline</label>
                <input type="date" id="add-deadline" name="deadline" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary btn-w-full py-2 mt-3">
                Tambahkan Tugas
            </button>
        </form>
    </div>
</div>

<!-- DATA KOLOM UNTUK JAVASCRIPT -->
<script>
const KANBAN_COLUMNS = <?= json_encode(array_map(fn($c) => [
    'id'             => $c->id,
    'nama'           => $c->nama,
    'requiresProof'  => (bool)$c->requiresProof,
    'requiresReason' => (bool)$c->requiresReason,
], $columns), JSON_UNESCAPED_UNICODE) ?>;
</script>

<!-- MODAL BUKTI FOTO -->
<div id="kanban-proof-modal" class="kanban-modal">
    <div class="kanban-modal-content" style="max-width:480px;">
        <span onclick="cancelKanbanModal()" class="kanban-modal-close">&times;</span>
        <h3 class="m-0 mb-1 fw-extrabold"><i class="ri-camera-line text-primary"></i> Upload Bukti Pekerjaan</h3>
        <p class="text-xs text-muted mb-4" id="proof-modal-subtitle">Upload foto bukti untuk memindahkan tugas ke kolom ini.</p>
        <div id="proof-dropzone" class="proof-dropzone" onclick="document.getElementById('proof-file-input').click()">
            <i class="ri-image-add-line" style="font-size:2rem;color:var(--primary);"></i>
            <p class="text-sm fw-semibold mt-2 mb-1">Klik atau seret foto ke sini</p>
            <p class="text-xs text-muted">JPG, PNG, WEBP</p>
        </div>
        <input type="file" id="proof-file-input" accept="image/*" capture="environment" style="display:none">
        <button type="button" class="btn btn-secondary btn-w-full mt-2 text-sm" onclick="document.getElementById('proof-file-input').click()">
            <i class="ri-camera-line"></i> Gunakan Kamera / Pilih File
        </button>
        <div id="proof-preview-wrap" style="display:none;margin-top:12px;">
            <img id="proof-preview-img" src="" alt="Preview" style="width:100%;border-radius:10px;max-height:220px;object-fit:cover;">
            <button type="button" class="btn btn-secondary text-xs mt-2" onclick="clearProofFile()">
                <i class="ri-delete-bin-line"></i> Hapus Foto
            </button>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="button" class="btn btn-secondary flex-1" onclick="cancelKanbanModal()">Batal</button>
            <button type="button" id="proof-submit-btn" class="btn btn-primary flex-1" onclick="submitProofModal()">
                <i class="ri-upload-cloud-line"></i> Simpan &amp; Pindahkan
            </button>
        </div>
    </div>
</div>

<!-- MODAL ALASAN -->
<div id="kanban-reason-modal" class="kanban-modal">
    <div class="kanban-modal-content" style="max-width:460px;">
        <span onclick="cancelKanbanModal()" class="kanban-modal-close">&times;</span>
        <h3 class="m-0 mb-1 fw-extrabold"><i class="ri-question-answer-line text-warning"></i> Berikan Alasan</h3>
        <p class="text-xs text-muted mb-4" id="reason-modal-subtitle">Jelaskan mengapa tugas ini dipindahkan.</p>
        <div class="form-group mb-3">
            <label class="form-label text-sm" for="reason-textarea">Alasan <span class="text-danger">*</span></label>
            <textarea id="reason-textarea" class="form-control" rows="4" placeholder="Tulis alasan di sini..."></textarea>
        </div>
        <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-secondary flex-1" onclick="cancelKanbanModal()">Batal</button>
            <button type="button" id="reason-submit-btn" class="btn btn-primary flex-1" onclick="submitReasonModal()">
                <i class="ri-check-line"></i> Konfirmasi &amp; Pindahkan
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    // =============================================
    // MODAL UTILITIES
    // =============================================
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function toggleDropdown(evt, id) {
        evt.stopPropagation();
        const el = document.getElementById(id);
        document.querySelectorAll('.kanban-dropdown-menu').forEach(dd => {
            if (dd.id !== id) dd.style.display = 'none';
        });
        el.style.display = (el.style.display === 'block') ? 'none' : 'block';
    }
    document.addEventListener('click', function() {
        document.querySelectorAll('.kanban-dropdown-menu').forEach(dd => dd.style.display = 'none');
    });
    window.addEventListener('click', function(e) {
        document.querySelectorAll('.kanban-modal').forEach(modal => {
            if (e.target === modal) modal.style.display = 'none';
        });
    });

    // =============================================
    // PHOTO LIGHTBOX
    // =============================================
    function openPhotoLightbox(src) {
        let lb = document.getElementById('kanban-photo-lightbox');
        if (!lb) {
            lb = document.createElement('div');
            lb.id = 'kanban-photo-lightbox';
            lb.style.cssText = 'position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.88);display:flex;align-items:center;justify-content:center;cursor:zoom-out;';
            lb.innerHTML = '<img id="kanban-lb-img" style="max-width:92vw;max-height:92vh;border-radius:10px;box-shadow:0 8px 48px #0008;" src="">';
            lb.addEventListener('click', () => lb.style.display = 'none');
            document.body.appendChild(lb);
        }
        document.getElementById('kanban-lb-img').src = src;
        lb.style.display = 'flex';
    }

    // =============================================
    // KANBAN DRAG STATE
    // =============================================
    let _pendingDrag = null; // { taskId, columnId, taskIds, fromEl, fromIndex, item }

    function cancelKanbanModal() {
        // Rollback: kembalikan card ke posisi asal
        if (_pendingDrag) {
            const { fromEl, item, fromIndex } = _pendingDrag;
            const refNode = fromEl.children[fromIndex] || null;
            fromEl.insertBefore(item, refNode);
            _pendingDrag = null;
        }
        closeModal('kanban-proof-modal');
        closeModal('kanban-reason-modal');
        clearProofFile();
        document.getElementById('reason-textarea').value = '';
    }

    // =============================================
    // PROOF MODAL LOGIC
    // =============================================
    document.getElementById('proof-file-input').addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('proof-preview-img').src = e.target.result;
            document.getElementById('proof-preview-wrap').style.display = 'block';
            document.getElementById('proof-dropzone').style.display = 'none';
        };
        reader.readAsDataURL(file);
    });

    // Drag & Drop for Proof Dropzone
    const proofDropzone = document.getElementById('proof-dropzone');
    const proofFileInput = document.getElementById('proof-file-input');

    if (proofDropzone && proofFileInput) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            proofDropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            proofDropzone.addEventListener(eventName, function() {
                proofDropzone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            proofDropzone.addEventListener(eventName, function() {
                proofDropzone.classList.remove('dragover');
            }, false);
        });

        proofDropzone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                const file = files[0];
                if (!file.type.startsWith('image/')) {
                    alert('Hanya file gambar yang diperbolehkan!');
                    return;
                }
                proofFileInput.files = files;
                proofFileInput.dispatchEvent(new Event('change'));
            }
        }, false);
    }

    function clearProofFile() {
        document.getElementById('proof-file-input').value = '';
        document.getElementById('proof-preview-wrap').style.display = 'none';
        document.getElementById('proof-dropzone').style.display = 'flex';
        document.getElementById('proof-preview-img').src = '';
    }

    function compressImage(file, maxWidth, maxHeight, quality) {
        return new Promise((resolve) => {
            if (!file.type.startsWith('image/')) {
                resolve(file);
                return;
            }
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function(event) {
                const img = new Image();
                img.src = event.target.result;
                img.onload = function() {
                    let width = img.width;
                    let height = img.height;

                    if (width > height) {
                        if (width > maxWidth) {
                            height = Math.round((height * maxWidth) / width);
                            width = maxWidth;
                        }
                    } else {
                        if (height > maxHeight) {
                            width = Math.round((width * maxHeight) / height);
                            height = maxHeight;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(function(blob) {
                        if (blob) {
                            let originalName = file.name || 'image.jpg';
                            const dotIndex = originalName.lastIndexOf('.');
                            if (dotIndex !== -1) {
                                originalName = originalName.substring(0, dotIndex) + '.jpg';
                            } else {
                                originalName += '.jpg';
                            }
                            const compressedFile = new File([blob], originalName, {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });
                            resolve(compressedFile);
                        } else {
                            resolve(file);
                        }
                    }, 'image/jpeg', quality);
                };
                img.onerror = function() {
                    resolve(file);
                };
            };
            reader.onerror = function() {
                resolve(file);
            };
        });
    }

    function submitProofModal() {
        if (!_pendingDrag) return;
        const fileInput = document.getElementById('proof-file-input');
        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Silakan pilih foto bukti terlebih dahulu.');
            return;
        }
        const btn = document.getElementById('proof-submit-btn');
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Memproses...';

        const originalFile = fileInput.files[0];
        compressImage(originalFile, 1600, 1600, 0.8)
            .then(compressedFile => {
                btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Mengunggah...';
                const fd = buildFormData();
                fd.append('bukti_foto', compressedFile);
                sendMoveRequest(fd, function() {
                    closeModal('kanban-proof-modal');
                    clearProofFile();
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
            })
            .catch(err => {
                console.error(err);
                btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Mengunggah...';
                const fd = buildFormData();
                fd.append('bukti_foto', originalFile);
                sendMoveRequest(fd, function() {
                    closeModal('kanban-proof-modal');
                    clearProofFile();
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
            });
    }

    // =============================================
    // REASON MODAL LOGIC
    // =============================================
    function submitReasonModal() {
        if (!_pendingDrag) return;
        const alasan = document.getElementById('reason-textarea').value.trim();
        if (!alasan) {
            alert('Alasan wajib diisi.');
            return;
        }
        const btn = document.getElementById('reason-submit-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-4-line"></i> Menyimpan...';

        const fd = buildFormData();
        fd.append('alasan', alasan);
        sendMoveRequest(fd, function() {
            closeModal('kanban-reason-modal');
            document.getElementById('reason-textarea').value = '';
            btn.disabled = false;
        });
    }

    // =============================================
    // SHARED HELPERS
    // =============================================
    function buildFormData() {
        const { taskId, columnId, taskIds } = _pendingDrag;
        const fd = new FormData();
        fd.append('taskId', taskId);
        fd.append('columnId', columnId);
        fd.append('_csrf', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        taskIds.forEach(id => fd.append('taskIds[]', id));
        return fd;
    }

    function sendMoveRequest(formData, onSuccess) {
        const moveUrl = "<?= $urlGenerator->generate('kanban/move-task') ?>";
        fetch(moveUrl, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal: ' + (data.error || 'Terjadi kesalahan.'));
                    cancelKanbanModal();
                    if (onSuccess) onSuccess();
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
                cancelKanbanModal();
                if (onSuccess) onSuccess();
            });
    }

    // =============================================
    // SORTABLEJS INIT
    // =============================================
    document.addEventListener('DOMContentLoaded', function() {
        const colEls = document.querySelectorAll('.kanban-column-body');
        colEls.forEach(colEl => {
            new Sortable(colEl, {
                group: 'kanban',
                animation: 150,
                draggable: '.kanban-card',
                ghostClass: 'kanban-ghost',
                onEnd: function(evt) {
                    // Guard: abaikan jika drag dalam kolom yang sama
                    if (evt.from === evt.to) return;

                    const taskId   = evt.item.getAttribute('data-task-id');
                    const columnId = evt.to.getAttribute('data-column-id');
                    const taskIds  = Array.from(evt.to.querySelectorAll('.kanban-card'))
                        .map(c => c.getAttribute('data-task-id'))
                        .filter(id => id !== null && id !== undefined && id !== '');

                    const colData = KANBAN_COLUMNS.find(c => c.id == columnId);

                    // Simpan state drag
                    _pendingDrag = {
                        taskId, columnId, taskIds,
                        fromEl: evt.from,
                        fromIndex: evt.oldIndex,
                        item: evt.item
                    };

                    if (colData && colData.requiresProof) {
                        document.getElementById('proof-modal-subtitle').textContent =
                            'Upload 1 foto bukti untuk memindahkan tugas ke kolom "' + colData.nama + '".';
                        openModal('kanban-proof-modal');

                    } else if (colData && colData.requiresReason) {
                        document.getElementById('reason-modal-subtitle').textContent =
                            'Kenapa tugas ini dipindahkan ke kolom "' + colData.nama + '"?';
                        openModal('kanban-reason-modal');

                    } else {
                        // Tidak perlu konfirmasi — langsung kirim
                        const fd = buildFormData();
                        sendMoveRequest(fd, null);
                    }
                }
            });
        });
    });
</script>

