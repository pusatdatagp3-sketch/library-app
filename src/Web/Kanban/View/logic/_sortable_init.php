<?php
declare(strict_types=1);
/**
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 */
?>
<script>
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
        .then(res => {
            if (!res.ok) {
                return res.json().catch(() => {
                    return { error: 'Gagal memproses request (Status: ' + res.status + ')' };
                });
            }
            return res.json();
        })
        .then(data => {
            if (data && data.success) {
                location.reload();
            } else {
                alert('Gagal: ' + ((data && data.error) || 'Terjadi kesalahan.'));
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
