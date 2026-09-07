<?php

declare(strict_types=1);

use App\Web\Perpustakaan\Model\KunjunganEntity;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var KunjunganEntity[] $kunjunganHariIni
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Scan Barcode Kunjungan Perpustakaan');
$totalHariIni = count($kunjunganHariIni);
?>

<div class="container-fluid py-3">
    <!-- Header Title & Navigasi -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">
                <i class="ri-barcode-box-line me-2 text-primary"></i>Scan Barcode Kunjungan
            </h1>
            <p class="text-muted mb-0 small">
                Arahkan scanner USB ke kartu santri. Sistem akan merekam kunjungan secara instan tanpa reload halaman.
            </p>
        </div>
        <div>
            <a href="<?= $urlGenerator->generate('perpustakaan/dashboard') ?>" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="ri-dashboard-line"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Scanner Box Area -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7 mx-auto">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%); border-top: 5px solid #2563eb !important;">
                <div class="card-body p-4 text-center">
                    
                    <!-- Scanner Icon & Status -->
                    <div class="mb-3 d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 72px; height: 72px;">
                        <i class="ri-qr-scan-2-line fs-1"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">Siap Memindai Barcode</h4>
                    <p class="text-muted small mb-3">
                        Gunakan USB Scanner atau ketik nomor stambuk lalu tekan <kbd class="bg-secondary text-white px-2 py-1 rounded">Enter</kbd>
                    </p>

                    <!-- Form Scan -->
                    <form id="barcode-scan-form" autocomplete="off" class="mb-3">
                        <?php if (!empty($csrf)): ?>
                            <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>" id="csrf-token">
                        <?php endif; ?>

                        <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden border">
                            <span class="input-group-text bg-white border-0 text-muted ps-3">
                                <i class="ri-barcode-line fs-3"></i>
                            </span>
                            <input 
                                type="text" 
                                id="stambuk-input" 
                                name="stambuk" 
                                class="form-control form-control-lg border-0 ps-2 fs-4 fw-bold font-monospace" 
                                placeholder="Scan / ketik stambuk..." 
                                autofocus 
                                required
                                spellcheck="false"
                                style="letter-spacing: 1px;"
                            >
                            <button type="submit" class="btn btn-primary px-4 fw-semibold" id="btn-submit-scan">
                                <span class="spinner-border spinner-border-sm d-none me-1" id="scan-spinner" role="status"></span>
                                Catat <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 px-1 text-muted small">
                            <span><i class="ri-focus-3-line text-success me-1"></i>Input otomatis auto-focus</span>
                            <span>Contoh stambuk test: <a href="javascript:void(0)" class="text-decoration-none badge bg-light text-primary border" onclick="quickFill('12345')">12345</a>, <a href="javascript:void(0)" class="text-decoration-none badge bg-light text-primary border" onclick="quickFill('18001')">18001</a>, <a href="javascript:void(0)" class="text-decoration-none badge bg-light text-primary border" onclick="quickFill('35412')">35412</a></span>
                        </div>
                    </form>

                    <!-- Alert Container untuk Feedback Visual -->
                    <div id="scan-alert-container" class="mt-3 text-start" style="min-height: 48px;">
                        <!-- Placeholder notifikasi akan muncul di sini via JS -->
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Live Kunjungan Hari Ini -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-transparent border-0 pt-3 px-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title fw-bold mb-0 text-dark">
                    <i class="ri-list-check-2 me-2 text-success"></i>Kunjungan Terdata Hari Ini
                </h5>
                <span class="text-muted small">Data terupdate langsung saat barcode berhasil dipindai</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success bg-opacity-10 text-success fs-6 fw-bold px-3 py-2" id="badge-total-kunjungan">
                    Total: <?= $totalHariIni ?> Santri
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="table-kunjungan-live">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 100px;">Waktu</th>
                            <th style="width: 130px;">Stambuk</th>
                            <th>Nama Santri</th>
                            <th style="width: 110px;">Kelas</th>
                            <th>Rayon</th>
                            <th>Konsulat</th>
                            <th class="pe-3">Penginput</th>
                        </tr>
                    </thead>
                    <tbody id="kunjungan-tbody">
                        <?php if (empty($kunjunganHariIni)): ?>
                            <tr id="empty-row">
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="ri-inbox-line fs-2 d-block mb-1 text-secondary"></i>
                                    Belum ada santri yang melakukan scan hari ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($kunjunganHariIni as $item): ?>
                                <tr>
                                    <td class="ps-3">
                                        <span class="badge bg-light text-dark border">
                                            <?= $item->waktu_kunjungan ? $item->waktu_kunjungan->format('H:i:s') : '-' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-monospace fw-semibold text-primary">
                                            <?= Html::encode((string)$item->stambuk) ?>
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <?= Html::encode((string)($item->nama_santri ?? '-')) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2">
                                            <?= Html::encode((string)($item->kelas ?? '-')) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= Html::encode((string)($item->rayon ?? '-')) ?></td>
                                    <td class="text-muted small"><?= Html::encode((string)($item->konsulat ?? '-')) ?></td>
                                    <td class="pe-3 text-muted small"><?= Html::encode((string)$item->penginput) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* Animasi kedip hijau untuk baris yang baru saja discan */
@keyframes highlightNewRow {
    0% { background-color: rgba(34, 197, 94, 0.25); }
    100% { background-color: transparent; }
}
.row-newly-scanned {
    animation: highlightNewRow 2.5s ease-out;
}
</style>

<script>
// Synthesizer Web Audio API untuk suara BEEP feedback tanpa butuh file MP3 eksternal
const AudioFeedback = {
    ctx: null,
    init() {
        if (!this.ctx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) {
                this.ctx = new AudioContext();
            }
        }
    },
    playSuccess() {
        this.init();
        if (!this.ctx) return;
        const now = this.ctx.currentTime;
        const osc = this.ctx.createOscillator();
        const gain = this.ctx.createGain();
        osc.connect(gain);
        gain.connect(this.ctx.destination);

        // Nada dua frekuensi harmonis (880Hz -> 1174Hz)
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, now);
        osc.frequency.exponentialRampToValueAtTime(1174.66, now + 0.12);
        gain.gain.setValueAtTime(0.3, now);
        gain.gain.exponentialRampToValueAtTime(0.01, now + 0.25);
        osc.start(now);
        osc.stop(now + 0.25);
    },
    playError() {
        this.init();
        if (!this.ctx) return;
        const now = this.ctx.currentTime;
        const osc = this.ctx.createOscillator();
        const gain = this.ctx.createGain();
        osc.connect(gain);
        gain.connect(this.ctx.destination);

        // Nada rendah peringatan (220Hz)
        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(220, now);
        gain.gain.setValueAtTime(0.4, now);
        gain.gain.exponentialRampToValueAtTime(0.01, now + 0.35);
        osc.start(now);
        osc.stop(now + 0.35);
    }
};

function quickFill(stambuk) {
    const input = document.getElementById('stambuk-input');
    if (input) {
        input.value = stambuk;
        input.focus();
        document.getElementById('barcode-scan-form').dispatchEvent(new Event('submit', { cancelable: true }));
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const scanForm = document.getElementById('barcode-scan-form');
    const stambukInput = document.getElementById('stambuk-input');
    const alertContainer = document.getElementById('scan-alert-container');
    const kunjunganTbody = document.getElementById('kunjungan-tbody');
    const badgeTotal = document.getElementById('badge-total-kunjungan');
    const scanSpinner = document.getElementById('scan-spinner');
    const btnSubmit = document.getElementById('btn-submit-scan');
    const csrfInput = document.getElementById('csrf-token');

    let totalKunjungan = <?= $totalHariIni ?>;

    // Pastikan input selalu fokus saat halaman dibuka
    stambukInput.focus();

    // Auto re-focus ketika user klik di sembarang tempat (memudahkan operasional scanner)
    document.addEventListener('click', function(e) {
        if (!e.target.closest('a') && !e.target.closest('button') && !e.target.closest('input')) {
            stambukInput.focus();
        }
    });

    scanForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const stambuk = stambukInput.value.trim();
        if (!stambuk) return;

        // UI loading state
        btnSubmit.disabled = true;
        scanSpinner.classList.remove('d-none');

        // Siapkan payload form data
        const formData = new URLSearchParams();
        formData.append('stambuk', stambuk);
        if (csrfInput && csrfInput.value) {
            formData.append('_csrf', csrfInput.value);
        }

        fetch('<?= $urlGenerator->generate('perpustakaan/scan') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData.toString()
        })
        .then(async response => {
            const data = await response.json().catch(() => ({
                success: false,
                message: 'Format respon server tidak valid.'
            }));
            return { ok: response.ok, data };
        })
        .then(({ ok, data }) => {
            if (data.success && data.data) {
                // Suara Beep Sukses
                AudioFeedback.playSuccess();

                const santri = data.data;

                // Tampilkan Card Notifikasi Sukses
                alertContainer.innerHTML = `
                    <div class="alert alert-success border-0 shadow-sm rounded-3 p-3 mb-0 d-flex align-items-center gap-3">
                        <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; flex-shrink: 0;">
                            <i class="ri-check-line fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold text-success">${escapeHtml(santri.nama)}</h6>
                                <span class="badge bg-success">${escapeHtml(santri.waktu)}</span>
                            </div>
                            <div class="small text-dark mt-1">
                                <span class="fw-semibold">Stambuk:</span> <span class="font-monospace">${escapeHtml(santri.stambuk)}</span> &bull; 
                                <span class="fw-semibold">Kelas:</span> <span class="badge bg-info bg-opacity-10 text-info border">${escapeHtml(santri.kelas)}</span> &bull; 
                                <span class="fw-semibold">Rayon:</span> ${escapeHtml(santri.rayon || '-')} &bull; 
                                <span class="fw-semibold">Konsulat:</span> ${escapeHtml(santri.konsulat || '-')}
                            </div>
                        </div>
                    </div>
                `;

                // Hilangkan empty placeholder jika ada
                const emptyRow = document.getElementById('empty-row');
                if (emptyRow) {
                    emptyRow.remove();
                }

                // Tambahkan baris baru ke tabel live (paling atas)
                const newTr = document.createElement('tr');
                newTr.className = 'row-newly-scanned';
                newTr.innerHTML = `
                    <td class="ps-3">
                        <span class="badge bg-light text-dark border">${escapeHtml(santri.waktu)}</span>
                    </td>
                    <td>
                        <span class="font-monospace fw-semibold text-primary">${escapeHtml(santri.stambuk)}</span>
                    </td>
                    <td class="fw-semibold text-dark">${escapeHtml(santri.nama)}</td>
                    <td>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2">${escapeHtml(santri.kelas)}</span>
                    </td>
                    <td class="text-muted small">${escapeHtml(santri.rayon || '-')}</td>
                    <td class="text-muted small">${escapeHtml(santri.konsulat || '-')}</td>
                    <td class="pe-3 text-muted small">${escapeHtml(santri.penginput)}</td>
                `;
                kunjunganTbody.insertBefore(newTr, kunjunganTbody.firstChild);

                // Update Total Badge
                totalKunjungan++;
                badgeTotal.textContent = `Total: ${totalKunjungan} Santri`;

            } else {
                // Suara Peringatan Gagal
                AudioFeedback.playError();

                // Tampilkan Card Notifikasi Gagal
                alertContainer.innerHTML = `
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 p-3 mb-0 d-flex align-items-center gap-3">
                        <div class="bg-danger text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; flex-shrink: 0;">
                            <i class="ri-close-line fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-danger">Gagal Memproses Scan</h6>
                            <div class="small text-dark mt-1">${escapeHtml(data.message || 'Santri tidak ditemukan.')}</div>
                        </div>
                    </div>
                `;
            }
        })
        .catch(err => {
            AudioFeedback.playError();
            alertContainer.innerHTML = `
                <div class="alert alert-danger border-0 shadow-sm rounded-3 p-3 mb-0">
                    <i class="ri-error-warning-line me-1"></i> Terjadi kegagalan jaringan atau server: ${escapeHtml(err.message)}
                </div>
            `;
        })
        .finally(() => {
            btnSubmit.disabled = false;
            scanSpinner.classList.add('d-none');
            stambukInput.value = '';
            stambukInput.focus();
        });
    });

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }
});
</script>
