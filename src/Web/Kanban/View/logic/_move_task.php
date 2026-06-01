<?php
declare(strict_types=1);
?>
<script>
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
</script>
