<?php
declare(strict_types=1);
?>
<script>
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
</script>
