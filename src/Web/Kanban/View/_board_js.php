<?php

declare(strict_types=1);

/**
 * @var App\Web\Kanban\Model\KanbanColumn[] $columns
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 */
?>
<!-- DATA KOLOM UNTUK JAVASCRIPT -->
<script>
const KANBAN_COLUMNS = <?= json_encode(array_map(fn($c) => [
    'id'             => $c->id,
    'nama'           => $c->nama,
    'progress'       => (int)$c->progress,
    'requiresProof'  => (bool)$c->requiresProof,
    'requiresReason' => (bool)$c->requiresReason,
], $columns), JSON_UNESCAPED_UNICODE) ?>;
</script>

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
        // Rollback: kembalikan card ke posisi asal (jika diseret secara fisik)
        if (_pendingDrag) {
            const { fromEl, item, fromIndex } = _pendingDrag;
            if (fromEl && item) {
                const refNode = fromEl.children[fromIndex] || null;
                fromEl.insertBefore(item, refNode);
            }
            _pendingDrag = null;
        }
        closeModal('kanban-proof-modal');
        closeModal('kanban-reason-modal');
        clearProofFile();
        document.getElementById('reason-textarea').value = '';
        document.getElementById('proof-reason-textarea').value = '';
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
                
                const optReason = document.getElementById('proof-reason-textarea').value.trim();
                if (optReason) {
                    fd.append('alasan', optReason);
                }
                
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
                
                const optReason = document.getElementById('proof-reason-textarea').value.trim();
                if (optReason) {
                    fd.append('alasan', optReason);
                }
                
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
        const textarea = document.getElementById('reason-textarea');
        const alasan = textarea.value.trim();
        const isRequired = textarea.getAttribute('data-required') !== 'false';
        
        if (isRequired && !alasan) {
            alert('Alasan wajib diisi.');
            return;
        }
        const btn = document.getElementById('reason-submit-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-4-line"></i> Menyimpan...';

        const fd = buildFormData();
        if (alasan) {
            fd.append('alasan', alasan);
        }
        sendMoveRequest(fd, function() {
            closeModal('kanban-reason-modal');
            textarea.value = '';
            btn.disabled = false;
        });
    }

    // =============================================
    // MOVE TASK MODAL LOGIC
    // =============================================
    function openMoveTaskModal(taskId, currentColumnId) {
        const card = document.querySelector('.kanban-card[data-task-id="' + taskId + '"]');
        const taskJudul = card ? card.querySelector('h4').textContent.trim() : '';

        document.getElementById('move-task-id').value = taskId;
        document.getElementById('move-current-column-id').value = currentColumnId;
        document.getElementById('move-task-modal-subtitle').textContent = 'Pindahkan tugas "' + taskJudul + '" ke kolom lain.';

        // Populate column dropdown
        const select = document.getElementById('move-target-column');
        select.innerHTML = '<option value="">-- Pilih Kolom --</option>';
        
        KANBAN_COLUMNS.forEach(col => {
            if (col.id != currentColumnId) {
                const opt = document.createElement('option');
                opt.value = col.id;
                opt.textContent = col.nama;
                select.appendChild(opt);
            }
        });

        openModal('kanban-move-task-modal');
    }

    function submitMoveTaskModal() {
        const taskId = document.getElementById('move-task-id').value;
        const columnId = document.getElementById('move-target-column').value;
        const currentColumnId = document.getElementById('move-current-column-id').value;
        if (!columnId) {
            alert('Silakan pilih kolom tujuan terlebih dahulu.');
            return;
        }

        const colData = KANBAN_COLUMNS.find(c => c.id == columnId);
        const fromColData = KANBAN_COLUMNS.find(c => c.id == currentColumnId);
        const isMovingBackward = fromColData && colData && (colData.progress < fromColData.progress);
        
        // Find destination column task IDs in DOM
        const toEl = document.querySelector('.kanban-column-body[data-column-id="' + columnId + '"]');
        const taskIds = toEl ? Array.from(toEl.querySelectorAll('.kanban-card'))
            .map(c => c.getAttribute('data-task-id'))
            .filter(id => id !== null && id !== undefined && id !== '') : [];
        
        if (!taskIds.includes(taskId)) {
            taskIds.push(taskId);
        }

        // Simpan state drag
        _pendingDrag = {
            taskId, columnId, taskIds,
            fromEl: null,
            fromIndex: null,
            item: null
        };

        // Close move task modal first
        closeModal('kanban-move-task-modal');

        if (colData && colData.requiresProof) {
            document.getElementById('proof-modal-subtitle').textContent =
                'Upload 1 foto bukti untuk memindahkan tugas ke kolom "' + colData.nama + '".';
            openModal('kanban-proof-modal');

        } else if (colData && colData.requiresReason) {
            document.getElementById('reason-modal-subtitle').textContent =
                'Kenapa tugas ini dipindahkan ke kolom "' + colData.nama + '"?';
            document.getElementById('reason-textarea').setAttribute('data-required', 'true');
            document.getElementById('reason-label-text').innerHTML = 'Alasan <span class="text-danger" id="reason-required-star">*</span>';
            openModal('kanban-reason-modal');

        } else if (isMovingBackward) {
            document.getElementById('reason-modal-subtitle').textContent =
                'Anda memindahkan tugas ini kembali ke kolom "' + colData.nama + '". Anda dapat memberikan penjelasan opsional.';
            document.getElementById('reason-textarea').setAttribute('data-required', 'false');
            document.getElementById('reason-label-text').innerHTML = 'Alasan / Catatan Tambahan <span class="text-xs text-muted">(Opsional)</span>';
            openModal('kanban-reason-modal');

        } else {
            const fd = buildFormData();
            sendMoveRequest(fd, null);
        }
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
                    const fromColumnId = evt.from.getAttribute('data-column-id');
                    const taskIds  = Array.from(evt.to.querySelectorAll('.kanban-card'))
                        .map(c => c.getAttribute('data-task-id'))
                        .filter(id => id !== null && id !== undefined && id !== '');

                    const colData = KANBAN_COLUMNS.find(c => c.id == columnId);
                    const fromColData = KANBAN_COLUMNS.find(c => c.id == fromColumnId);
                    const isMovingBackward = fromColData && colData && (colData.progress < fromColData.progress);

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
                        document.getElementById('reason-textarea').setAttribute('data-required', 'true');
                        document.getElementById('reason-label-text').innerHTML = 'Alasan <span class="text-danger" id="reason-required-star">*</span>';
                        openModal('kanban-reason-modal');

                    } else if (isMovingBackward) {
                        document.getElementById('reason-modal-subtitle').textContent =
                            'Anda memindahkan tugas ini kembali ke kolom "' + colData.nama + '". Anda dapat memberikan penjelasan opsional.';
                        document.getElementById('reason-textarea').setAttribute('data-required', 'false');
                        document.getElementById('reason-label-text').innerHTML = 'Alasan / Catatan Tambahan <span class="text-xs text-muted">(Opsional)</span>';
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
