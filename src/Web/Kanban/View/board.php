<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Program\Model\Program $program
 * @var App\Web\Entitas\Model\Entitas $entitas
 * @var App\Web\Kanban\Model\KanbanColumn[] $columns
 * @var App\Web\Task\Model\Task[] $tasks
 * @var App\Web\Entitas\Model\AnggotaEntitas[] $members
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $successMsg
 * @var array $errorMsgs
 */

$this->setTitle("Papan Kanban - {$program->namaProgram}");
?>

<meta name="csrf-token" content="<?= Html::encode($this->getParameter('csrf')) ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css">

<style>
    /* Premium Glassmorphism Board Styling */
    .kanban-board-wrapper {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 120px);
        margin: -1.5rem;
        padding: 1.5rem;
    }
    .kanban-columns-container {
        display: flex;
        gap: 1.25rem;
        flex-grow: 1;
        overflow-x: auto;
        padding: 0.5rem 0.25rem;
        align-items: flex-start;
    }
    .kanban-col {
        flex: 1;
        min-width: 300px;
        max-width: 380px;
        background: rgba(255, 255, 255, 0.45);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        max-height: 100%;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.04);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kanban-col-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.6);
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
    .kanban-column-body {
        padding: 1rem;
        overflow-y: auto;
        flex-grow: 1;
        min-height: 400px;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .kanban-card {
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.04);
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        cursor: grab;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kanban-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
    }
    .kanban-card:active {
        cursor: grabbing;
    }
    .kanban-ghost {
        opacity: 0.4;
        border: 2px dashed var(--primary);
        background: var(--primary-light);
    }
    /* Modal Styling */
    .kanban-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(4px);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }
    .kanban-modal-content {
        background: #fff;
        padding: 2rem;
        border-radius: 12px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        position: relative;
    }
    .kanban-modal-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        cursor: pointer;
        font-size: 1.5rem;
        color: var(--text-muted);
    }
</style>

<div class="kanban-board-wrapper">
    <!-- Top Action Row -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                <a href="<?= $urlGenerator->generate('program/view', ['id' => $program->id]) ?>" style="text-decoration: underline;">
                    <?= Html::encode($program->namaProgram) ?>
                </a>
                <span>/</span>
                <span>Papan Kanban</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <i class="ri-kanban-view text-primary"></i> Papan Kanban: <?= Html::encode($program->namaProgram) ?>
            </h1>
        </div>

        <div style="display: flex; gap: 0.75rem;">
            <button onclick="openModal('add-task-modal')" class="btn btn-primary">
                <i class="ri-add-line"></i> Tambah Tugas
            </button>
            <a href="<?= $urlGenerator->generate('program/view', ['id' => $program->id]) ?>" class="btn btn-secondary">
                <i class="ri-arrow-go-back-line"></i> Detail Program
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    <?php if ($successMsg): ?>
        <div class="alert alert-success mb-3">
            <i class="ri-checkbox-circle-fill alert-icon"></i>
            <div><?= Html::encode($successMsg) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMsgs)): ?>
        <div class="alert alert-danger mb-3">
            <i class="ri-error-warning-fill alert-icon"></i>
            <div>
                <?php foreach ($errorMsgs as $err): ?>
                    <p><?= Html::encode($err) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Kanban board container -->
    <div class="kanban-columns-container">
        <?php foreach ($columns as $col): ?>
            <?php 
                $colTasks = array_filter($tasks, fn($t) => $t->kanbanColumnId === $col->id);
                usort($colTasks, fn($a, $b) => $a->urutan <=> $b->urutan);
            ?>
            <div class="kanban-col">
                <div class="kanban-col-header">
                    <span><?= Html::encode($col->nama) ?></span>
                    <span class="badge badge-primary-light" style="font-size: 0.75rem; font-weight: 600; padding: 0.15rem 0.5rem; border-radius: 20px;">
                        <?= count($colTasks) ?>
                    </span>
                </div>

                <div class="kanban-column-body" data-column-id="<?= $col->id ?>">
                    <?php foreach ($colTasks as $task): ?>
                        <div class="kanban-card" data-task-id="<?= $task->id ?>">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; color: var(--text-color);"><?= Html::encode($task->judul) ?></h4>
                                <div style="position: relative;">
                                    <button onclick="toggleDropdown(event, 'card-actions-<?= $task->id ?>')" style="background: none; border: none; padding: 0.15rem; cursor: pointer; color: var(--text-muted);">
                                        <i class="ri-more-2-line"></i>
                                    </button>
                                    <div id="card-actions-<?= $task->id ?>" class="dropdown-menu" style="display: none; position: absolute; right: 0; background: #fff; border: 1px solid rgba(0,0,0,0.1); border-radius: 6px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); z-index: 100; min-width: 100px;">
                                        <button onclick="openModal('edit-task-modal-<?= $task->id ?>')" style="display: block; width: 100%; text-align: left; padding: 0.5rem 1rem; border: none; background: none; font-size: 0.8rem; cursor: pointer;">
                                            <i class="ri-pencil-line"></i> Edit
                                        </button>
                                        <form action="<?= $urlGenerator->generate('kanban/delete-task', ['program_id' => $program->id, 'id' => $task->id]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?');" style="margin:0;">
                                            <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                            <button type="submit" style="display: block; width: 100%; text-align: left; padding: 0.5rem 1rem; border: none; background: none; font-size: 0.8rem; cursor: pointer; color: var(--danger);">
                                                <i class="ri-delete-bin-line"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= Html::encode($task->deskripsi ?? 'Tidak ada deskripsi.') ?>
                            </p>

                            <!-- Progress Bar -->
                            <div style="margin-bottom: 0.75rem;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.15rem;">
                                    <span>Progres</span>
                                    <span><?= $task->progress ?>%</span>
                                </div>
                                <div style="height: 6px; background: rgba(0,0,0,0.05); border-radius: 3px; overflow: hidden;">
                                    <div style="height: 100%; width: <?= $task->progress ?>%; background: var(--primary); border-radius: 3px; transition: width 0.3s;"></div>
                                </div>
                            </div>

                            <!-- Footer of Card -->
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem;">
                                <span class="badge badge-primary-light" style="display: inline-flex; align-items: center; gap: 0.2rem; padding: 0.15rem 0.4rem; border-radius: 4px;">
                                    <i class="ri-user-star-line" style="font-size: 0.8rem;"></i> <?= Html::encode($task->assignedUser?->namaAnggota ?? 'Unassigned') ?>
                                </span>
                                
                                <?php if ($task->deadline): ?>
                                    <span style="color: <?= (new \DateTime() > $task->deadline) ? 'var(--danger)' : 'var(--text-muted)' ?>; display: inline-flex; align-items: center; gap: 0.15rem; font-weight: 600;">
                                        <i class="ri-calendar-line"></i> <?= $task->deadline->format('d M') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- MODAL EDIT TUGAS (Masing-masing tugas punya modal sendiri agar mudah) -->
                        <div id="edit-task-modal-<?= $task->id ?>" class="kanban-modal">
                            <div class="kanban-modal-content">
                                <span onclick="closeModal('edit-task-modal-<?= $task->id ?>')" class="kanban-modal-close">&times;</span>
                                <h3 style="margin-top: 0; margin-bottom: 1.5rem; font-weight: 800;"><i class="ri-edit-box-line text-primary"></i> Edit Tugas</h3>
                                <form action="<?= $urlGenerator->generate('kanban/edit-task', ['program_id' => $program->id, 'id' => $task->id]) ?>" method="POST">
                                    <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label" style="font-size:0.85rem;" for="judul-<?= $task->id ?>">Judul Tugas</label>
                                        <input type="text" id="judul-<?= $task->id ?>" name="judul" class="form-control" value="<?= Html::encode($task->judul) ?>" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label" style="font-size:0.85rem;" for="deskripsi-<?= $task->id ?>">Deskripsi</label>
                                        <textarea id="deskripsi-<?= $task->id ?>" name="deskripsi" class="form-control" rows="3"><?= Html::encode($task->deskripsi ?? '') ?></textarea>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label" style="font-size:0.85rem;" for="assigned_to-<?= $task->id ?>">PIC</label>
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
                                        <label class="form-label" style="font-size:0.85rem;" for="progress-<?= $task->id ?>">Progress (%)</label>
                                        <input type="number" id="progress-<?= $task->id ?>" name="progress" class="form-control" min="0" max="100" value="<?= $task->progress ?>" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label" style="font-size:0.85rem;" for="deadline-<?= $task->id ?>">Deadline</label>
                                        <input type="date" id="deadline-<?= $task->id ?>" name="deadline" class="form-control" value="<?= $task->deadline ? $task->deadline->format('Y-m-d') : '' ?>">
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100" style="width:100%; justify-content:center; padding:0.6rem;">
                                        Simpan Perubahan
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- MODAL TAMBAH TUGAS -->
<div id="add-task-modal" class="kanban-modal">
    <div class="kanban-modal-content">
        <span onclick="closeModal('add-task-modal')" class="kanban-modal-close">&times;</span>
        <h3 style="margin-top: 0; margin-bottom: 1.5rem; font-weight: 800;"><i class="ri-add-circle-line text-primary"></i> Tambah Tugas Baru</h3>
        <form action="<?= $urlGenerator->generate('kanban/add-task', ['program_id' => $program->id]) ?>" method="POST">
            <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
            
            <div class="form-group mb-3">
                <label class="form-label" style="font-size:0.85rem;" for="add-judul">Judul Tugas</label>
                <input type="text" id="add-judul" name="judul" class="form-control" placeholder="Tugas baru..." required>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" style="font-size:0.85rem;" for="add-deskripsi">Deskripsi</label>
                <textarea id="add-deskripsi" name="deskripsi" class="form-control" placeholder="Detail rincian tugas..." rows="3"></textarea>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" style="font-size:0.85rem;" for="add-assigned_to">PIC</label>
                <select id="add-assigned_to" name="assigned_to" class="form-control">
                    <option value="">-- Pilih PIC --</option>
                    <?php foreach ($members as $mb): ?>
                        <option value="<?= $mb->id ?>"><?= Html::encode($mb->namaAnggota) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" style="font-size:0.85rem;" for="add-deadline">Deadline</label>
                <input type="date" id="add-deadline" name="deadline" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary w-100" style="width:100%; justify-content:center; padding:0.6rem;">
                Tambahkan Tugas
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    function toggleDropdown(evt, id) {
        evt.stopPropagation();
        const el = document.getElementById(id);
        const allDropdowns = document.querySelectorAll('.dropdown-menu');
        allDropdowns.forEach(dd => {
            if (dd.id !== id) dd.style.display = 'none';
        });
        if (el.style.display === 'none') {
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    }

    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(dd => {
            dd.style.display = 'none';
        });
    });

    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    // Close modal when clicking outside content
    window.addEventListener('click', function(e) {
        document.querySelectorAll('.kanban-modal').forEach(modal => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Initialize SortableJS
    document.addEventListener('DOMContentLoaded', function() {
        const columns = document.querySelectorAll('.kanban-column-body');
        columns.forEach(col => {
            new Sortable(col, {
                group: 'kanban',
                animation: 150,
                draggable: '.kanban-card',
                ghostClass: 'kanban-ghost',
                onEnd: function(evt) {
                    const taskId = evt.item.getAttribute('data-task-id');
                    const columnId = evt.to.getAttribute('data-column-id');
                    const taskIds = Array.from(evt.to.querySelectorAll('.kanban-card'))
                        .map(child => child.getAttribute('data-task-id'))
                        .filter(id => id !== null && id !== undefined && id !== '');
                    const moveUrl = "<?= $urlGenerator->generate('kanban/move-task') ?>";

                    const params = new URLSearchParams();
                    params.append('taskId', taskId);
                    params.append('columnId', columnId);
                    params.append('_csrf', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                    taskIds.forEach(id => {
                        params.append('taskIds[]', id);
                    });

                    fetch(moveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: params
                    })
                    .then(res => {
                        if (!res.ok) {
                            throw new Error('Response error');
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Gagal memindahkan tugas.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Terjadi kesalahan jaringan.');
                    });
                }
            });
        });
    });
</script>
