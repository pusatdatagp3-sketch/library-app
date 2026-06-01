<?php
declare(strict_types=1);
?>
<script>
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
</script>
