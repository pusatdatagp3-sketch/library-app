<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/**
 * @var App\Web\Program\Model\Program $program
 * @var App\Web\Entitas\Model\AnggotaEntitas[] $members
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\View\WebView $this
 */
?>
<!-- MODAL TAMBAH TUGAS -->
<div id="add-task-modal" class="kanban-modal">
    <div class="kanban-modal-content">
        <span onclick="closeModal('add-task-modal')" class="kanban-modal-close">&times;</span>
        <h3 class="m-0 mb-4 fw-extrabold"><i class="ri-add-circle-line text-primary"></i> Tambah Tugas Baru</h3>
        <form action="<?= $urlGenerator->generate('kanban/add-task', ['program_id' => $program->id]) ?>" method="POST" class="form-grid" onsubmit="const btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.innerHTML = '<i class=\'ri-loader-4-line ri-spin\'></i> Menyimpan...';">
            <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
            
            <div class="form-group mb-3">
                <label class="form-label text-sm" for="add-judul">Judul Tugas</label>
                <input type="text" id="add-judul" name="judul" class="form-control" placeholder="Tugas baru..." required>
            </div>

            <div class="form-group mb-3">
                <label class="form-label text-sm" for="add-deskripsi">Deskripsi</label>
                <textarea id="add-deskripsi" name="deskripsi" class="form-control" placeholder="Detail rincian tugas..." rows="3"></textarea>
            </div>

            <div class="form-group mb-3">
                <label class="form-label text-sm" for="add-assigned_to">PIC</label>
                <select id="add-assigned_to" name="assigned_to" class="form-control">
                    <option value="">-- Pilih PIC --</option>
                    <?php foreach ($members as $mb): ?>
                        <option value="<?= $mb->id ?>"><?= Html::encode($mb->namaAnggota) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <label class="form-label text-sm" for="add-deadline">Deadline</label>
                <input type="date" id="add-deadline" name="deadline" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary btn-w-full py-2 mt-3">
                Tambahkan Tugas
            </button>
        </form>
    </div>
</div>
