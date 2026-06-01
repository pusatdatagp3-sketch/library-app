<?php
declare(strict_types=1);
?>
<script>
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
}
</script>
