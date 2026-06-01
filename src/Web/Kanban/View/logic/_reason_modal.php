<?php
declare(strict_types=1);
?>
<script>
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
</script>
