<?php

declare(strict_types=1);

/**
 * Shared action modals for Kanban board operations (Proof, Reason, Move).
 * Operated dynamically via JavaScript.
 */
?>
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
        <input type="file" id="proof-file-input" accept="image/*" style="display:none">
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

<!-- MODAL PINDAHKAN TUGAS (Shared) -->
<div id="kanban-move-task-modal" class="kanban-modal">
    <div class="kanban-modal-content" style="max-width:400px;">
        <span onclick="closeModal('kanban-move-task-modal')" class="kanban-modal-close">&times;</span>
        <h3 class="m-0 mb-1 fw-extrabold"><i class="ri-arrow-left-right-line text-primary"></i> Pindahkan Tugas</h3>
        <p class="text-xs text-muted mb-4" id="move-task-modal-subtitle">Pilih kolom tujuan pemindahan tugas ini.</p>
        
        <input type="hidden" id="move-task-id">
        <input type="hidden" id="move-current-column-id">
        
        <div class="form-group mb-3">
            <label class="form-label text-sm" for="move-target-column">Kolom Tujuan <span class="text-danger">*</span></label>
            <select id="move-target-column" class="form-control">
                <!-- Populated dynamically via JS -->
            </select>
        </div>
        
        <div class="d-flex gap-2 mt-4">
            <button type="button" class="btn btn-secondary flex-1" onclick="closeModal('kanban-move-task-modal')">Batal</button>
            <button type="button" id="move-task-submit-btn" class="btn btn-primary flex-1" onclick="submitMoveTaskModal()">
                <i class="ri-check-line"></i> Pindahkan
            </button>
        </div>
    </div>
</div>
