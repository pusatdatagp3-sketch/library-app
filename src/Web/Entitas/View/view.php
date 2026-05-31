<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Entitas\Model\Entitas $model
 * @var App\Web\Entitas\Model\AnggotaEntitas[] $members
 * @var App\Web\Program\Model\Program[] $programs
 * @var App\Web\Auth\Model\User[] $users
 * @var UrlGeneratorInterface $urlGenerator
 * @var \App\Web\Auth\Model\UserSession $userSession
 * @var string $prefix
 * @var string $modulTitle
 * @var string $indexRoute
 * @var string|null $successMsg
 * @var array $errorMsgs
 */

$this->setTitle("Detail Entitas - {$model->nama}");
$addMemberRoute    = "{$prefix}/add-member";
$deleteMemberRoute = "{$prefix}/delete-member";
?>

<div class="crud-container">
    <div class="sticky-page-header">
        <a href="<?= $urlGenerator->generate($indexRoute) ?>" class="btn btn-sm btn-secondary">
            <i class="ri-arrow-left-line"></i> Kembali ke Daftar <?= Html::encode($modulTitle) ?>
        </a>
    </div>

    <!-- Header Info Card -->
    <div class="card mb-4 overflow-hidden card-accent">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <span class="badge badge-primary-light mb-2">
                    <?= Html::encode($modulTitle) ?>
                </span>
                <h1 class="text-2xl fw-extrabold mb-2 text-color"><?= Html::encode($model->nama) ?></h1>
                <p class="text-muted mb-0"><?= Html::encode($model->deskripsi ?? 'Tidak ada deskripsi.') ?></p>
            </div>

            <?php if ($userSession->hasPermission("update_{$prefix}")): ?>
                <a href="<?= $urlGenerator->generate("{$prefix}/update", ['id' => $model->id]) ?>" class="btn btn-outline-primary">
                    <i class="ri-pencil-line"></i> Edit Entitas
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($successMsg): ?>
        <div class="alert alert-success mb-4">
            <i class="ri-checkbox-circle-fill alert-icon"></i>
            <div><?= Html::encode($successMsg) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMsgs)): ?>
        <div class="alert alert-danger mb-4">
            <i class="ri-error-warning-fill alert-icon"></i>
            <div>
                <?php foreach ($errorMsgs as $errorMsg): ?>
                    <p><?= Html::encode($errorMsg) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Split Layout: Programs (left) + Members (right) -->
    <div class="grid grid-2col-left gap-4">

        <!-- SECTION 1: PROGRAM KERJA -->
        <div class="d-flex flex-col gap-4">
            <div class="card p-5">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 border-bottom pb-3">
                    <h2 class="text-lg fw-bold d-flex align-items-center gap-2 m-0">
                        <i class="ri-calendar-todo-line text-primary text-xl"></i> Program Kerja
                    </h2>
                    <a href="<?= $urlGenerator->generate('program/create', ['entitas_id' => $model->id]) ?>" class="btn btn-sm btn-primary">
                        <i class="ri-add-line"></i> Tambah Program
                    </a>
                </div>

                <?php if (empty($programs)): ?>
                    <div class="text-center py-5">
                        <i class="ri-draft-line text-muted text-2xl d-block mb-2"></i>
                        <p class="text-muted">Belum ada program kerja yang ditambahkan ke entitas ini.</p>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-col gap-3">
                        <?php foreach ($programs as $prog): ?>
                            <div class="program-row hover-glow">
                                <div>
                                    <h4 class="fw-bold mb-1 m-0"><?= Html::encode($prog->namaProgram) ?></h4>
                                    <div class="d-flex gap-3 align-items-center text-sm text-muted flex-wrap">
                                        <span class="inline-flex align-items-center gap-1">
                                            <i class="ri-time-line"></i> <?= Html::encode(ucfirst($prog->periode ?? 'tidak ditentukan')) ?>
                                        </span>
                                        <span class="sep-dot"></span>
                                        <span class="inline-flex align-items-center gap-1">
                                            <i class="ri-user-star-line"></i> PIC: <?= Html::encode($prog->penanggungJawab?->namaAnggota ?? 'Belum ditentukan') ?>
                                        </span>
                                        <span class="sep-dot"></span>
                                        <span>Status:
                                            <span class="badge <?= $prog->status === 'active' ? 'badge-success' : 'badge-secondary' ?> text-xs">
                                                <?= Html::encode($prog->status) ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    <a href="<?= $urlGenerator->generate('kanban/board', ['program_id' => $prog->id]) ?>" class="btn btn-sm btn-primary">
                                        <i class="ri-kanban-view"></i> Papan Kanban
                                    </a>
                                    <a href="<?= $urlGenerator->generate('program/view', ['id' => $prog->id]) ?>" class="btn btn-sm btn-outline-secondary" title="Detail Program">
                                        <i class="ri-arrow-right-s-line"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECTION 2: ANGGOTA ENTITAS -->
        <div class="d-flex flex-col gap-4">
            <div class="card p-5">
                <h2 class="text-lg fw-bold d-flex align-items-center gap-2 mb-4 border-bottom pb-3">
                    <i class="ri-group-line text-primary text-xl"></i> Kepengurusan / Anggota
                </h2>

                <?php if (empty($members)): ?>
                    <p class="text-muted text-center py-4">Belum ada anggota yang dialokasikan.</p>
                <?php else: ?>
                    <div class="d-flex flex-col gap-2 max-h-400 overflow-y-auto pe-1">
                        <?php foreach ($members as $mb): ?>
                            <div class="list-item-row">
                                <div>
                                    <div class="fw-semibold text-sm text-color"><?= Html::encode($mb->namaAnggota) ?></div>
                                    <div class="text-xs text-muted text-uppercase letter-spacing fw-semibold">
                                        <?= Html::encode($mb->jabatan ?? 'Anggota') ?>
                                    </div>
                                </div>

                                <?php if ($userSession->hasPermission("update_{$prefix}")): ?>
                                    <div class="d-flex gap-2 align-items-center">
                                        <button type="button" class="btn-inline-icon text-primary" title="Edit Anggota"
                                                data-nama="<?= Html::encode($mb->namaAnggota) ?>"
                                                data-jabatan="<?= Html::encode($mb->jabatan ?? '') ?>"
                                                data-user-id="<?= $mb->userId ?? '' ?>"
                                                data-action="<?= $urlGenerator->generate($prefix . '/update-member', ['id' => $model->id, 'memberId' => $mb->id]) ?>"
                                                onclick="openEditMemberModal(this)">
                                            <i class="ri-edit-line text-lg"></i>
                                        </button>

                                        <form action="<?= $urlGenerator->generate($deleteMemberRoute, ['id' => $model->id, 'memberId' => $mb->id]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin melepas anggota ini?');" class="form-inline">
                                            <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                            <button type="submit" class="btn-inline-delete" title="Hapus Anggota">
                                                <i class="ri-close-circle-line text-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($userSession->hasPermission("update_{$prefix}")): ?>
                    <div class="mini-form-section">
                        <h4 class="text-sm fw-bold mb-3"><i class="ri-user-add-line"></i> Alokasikan Anggota Baru</h4>

                        <form action="<?= $urlGenerator->generate($addMemberRoute, ['id' => $model->id]) ?>" method="POST">
                            <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">

                            <div class="form-group mb-3">
                                <label class="form-label text-xs" for="nama_anggota">Nama Anggota</label>
                                <input type="text" id="nama_anggota" name="nama_anggota" class="form-control form-control-sm" placeholder="Nama Lengkap Anggota" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label text-xs" for="jabatan">Jabatan</label>
                                <input type="text" id="jabatan" name="jabatan" class="form-control form-control-sm" placeholder="Contoh: Ketua, Anggota, Sekretaris" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label text-xs" for="user_id">Link Akun Pengguna (Opsional)</label>
                                <select id="user_id" name="user_id" class="form-control form-control-sm" onchange="document.getElementById('nama_anggota').value = this.options[this.selectedIndex].text;">
                                    <option value="">-- Pilih Akun Pengguna --</option>
                                    <?php foreach ($users as $usr): ?>
                                        <option value="<?= $usr->id ?>"><?= Html::encode($usr->username) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-sm btn-primary btn-w-full mt-2">
                                <i class="ri-user-add-line"></i> Tambahkan ke Entitas
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Edit Member Modal -->
<div id="edit-member-modal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title"><i class="ri-edit-line text-primary"></i> Edit Anggota</h3>
            <button type="button" class="modal-close" onclick="closeEditMemberModal()">&times;</button>
        </div>
        <form id="edit-member-form" method="POST" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">

            <div class="form-group mb-3">
                <label class="form-label text-xs" for="edit_nama_anggota">Nama Anggota</label>
                <input type="text" id="edit_nama_anggota" name="nama_anggota" class="form-control form-control-sm" required>
            </div>

            <div class="form-group mb-3">
                <label class="form-label text-xs" for="edit_jabatan">Jabatan</label>
                <input type="text" id="edit_jabatan" name="jabatan" class="form-control form-control-sm" required>
            </div>

            <div class="form-group mb-3">
                <label class="form-label text-xs" for="edit_user_id">Link Akun Pengguna (Opsional)</label>
                <select id="edit_user_id" name="user_id" class="form-control form-control-sm" onchange="if(this.value) { document.getElementById('edit_nama_anggota').value = this.options[this.selectedIndex].text; }">
                    <option value="">-- Pilih Akun Pengguna --</option>
                    <?php foreach ($users as $usr): ?>
                        <option value="<?= $usr->id ?>"><?= Html::encode($usr->username) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-secondary" onclick="closeEditMemberModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditMemberModal(btn) {
    const modal = document.getElementById('edit-member-modal');
    const form  = document.getElementById('edit-member-form');
    form.action = btn.getAttribute('data-action');
    document.getElementById('edit_nama_anggota').value = btn.getAttribute('data-nama');
    document.getElementById('edit_jabatan').value      = btn.getAttribute('data-jabatan');
    document.getElementById('edit_user_id').value      = btn.getAttribute('data-user-id') || '';
    modal.classList.add('open');
}

function closeEditMemberModal() {
    document.getElementById('edit-member-modal').classList.remove('open');
}
</script>
