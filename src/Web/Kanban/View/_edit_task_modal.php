<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/**
 * @var App\Web\Task\Model\Task $task
 * @var App\Web\Entitas\Model\AnggotaEntitas[] $members
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var App\Web\Program\Model\Program $program
 * @var Yiisoft\View\WebView $this
 */
?>
<!-- MODAL EDIT TUGAS -->
<div id="edit-task-modal-<?= $task->id ?>" class="kanban-modal">
    <div class="kanban-modal-content">
        <span onclick="closeModal('edit-task-modal-<?= $task->id ?>')" class="kanban-modal-close">&times;</span>
        <h3 class="m-0 mb-4 fw-extrabold"><i class="ri-edit-box-line text-primary"></i> Edit Tugas</h3>
        <form action="<?= $urlGenerator->generate('kanban/edit-task', ['program_id' => $program->id, 'id' => $task->id]) ?>" method="POST" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
            
            <div class="form-group mb-3">
                <label class="form-label text-sm" for="judul-<?= $task->id ?>">Judul Tugas</label>
                <input type="text" id="judul-<?= $task->id ?>" name="judul" class="form-control" value="<?= Html::encode($task->judul) ?>" required>
            </div>

            <div class="form-group mb-3">
                <label class="form-label text-sm" for="deskripsi-<?= $task->id ?>">Deskripsi</label>
                <textarea id="deskripsi-<?= $task->id ?>" name="deskripsi" class="form-control" rows="3"><?= Html::encode($task->deskripsi ?? '') ?></textarea>
            </div>

            <div class="form-group mb-3">
                <label class="form-label text-sm" for="assigned_to-<?= $task->id ?>">PIC</label>
                <select id="assigned_to-<?= $task->id ?>" name="assigned_to" class="form-control">
                    <option value="">-- Pilih PIC --</option>
                    <?php foreach ($members as $mb): ?>
                        <option value="<?= $mb->id ?>" <?= $task->assignedTo === $mb->id ? 'selected' : '' ?>>
                            <?= Html::encode($mb->namaAnggota) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <label class="form-label text-sm" for="progress-<?= $task->id ?>">Progress (%)</label>
                <input type="number" id="progress-<?= $task->id ?>" name="progress" class="form-control" min="0" max="100" value="<?= $task->progress ?>" required>
            </div>

            <div class="form-group mb-3">
                <label class="form-label text-sm" for="deadline-<?= $task->id ?>">Deadline</label>
                <input type="date" id="deadline-<?= $task->id ?>" name="deadline" class="form-control" value="<?= $task->deadline ? $task->deadline->format('Y-m-d') : '' ?>">
            </div>

            <button type="submit" class="btn btn-primary btn-w-full py-2 mt-3">
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>
