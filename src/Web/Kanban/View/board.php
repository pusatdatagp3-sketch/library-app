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

<div class="kanban-board-wrapper">
    <!-- Top Action Row -->
    <div class="sticky-page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 text-sm text-muted mb-1">
                <a href="<?= $urlGenerator->generate('program/view', ['id' => $program->id]) ?>" class="text-muted">
                    <?= Html::encode($program->namaProgram) ?>
                </a>
                <span>/</span>
                <span>Papan Kanban</span>
            </div>
            <h1 class="text-xl fw-extrabold m-0 d-flex align-items-center gap-2">
                <i class="ri-kanban-view text-primary"></i> Papan Kanban: <?= Html::encode($program->namaProgram) ?>
            </h1>
        </div>

        <div class="d-flex gap-2">
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
                    <span class="badge badge-primary-light">
                        <?= count($colTasks) ?>
                    </span>
                </div>

                <div class="kanban-column-body" data-column-id="<?= $col->id ?>">
                    <?php foreach ($colTasks as $task): ?>
                        <div class="kanban-card hover-glow" data-task-id="<?= $task->id ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h4 class="text-sm fw-bold m-0 text-color"><?= Html::encode($task->judul) ?></h4>
                                <div class="position-relative">
                                    <button onclick="toggleDropdown(event, 'card-actions-<?= $task->id ?>')" class="kanban-card-more-btn">
                                        <i class="ri-more-2-line"></i>
                                    </button>
                                    <div id="card-actions-<?= $task->id ?>" class="kanban-dropdown-menu">
                                        <button onclick="openModal('edit-task-modal-<?= $task->id ?>')" class="kanban-dropdown-item">
                                            <i class="ri-pencil-line"></i> Edit
                                        </button>
                                        <form action="<?= $urlGenerator->generate('kanban/delete-task', ['program_id' => $program->id, 'id' => $task->id]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?');" class="m-0">
                                            <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                            <button type="submit" class="kanban-dropdown-item text-danger">
                                                <i class="ri-delete-bin-line"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-muted mb-2 text-ellipsis">
                                <?= Html::encode($task->deskripsi ?? 'Tidak ada deskripsi.') ?>
                            </p>

                            <!-- Progress Bar -->
                            <div class="mb-2">
                                <div class="d-flex justify-content-between text-xs text-muted mb-1">
                                    <span>Progres</span>
                                    <span><?= $task->progress ?>%</span>
                                </div>
                                <div class="task-progress-bar-container">
                                    <div class="task-progress-bar bar-todo" style="width: <?= $task->progress ?>%;"></div>
                                </div>
                            </div>

                            <!-- Footer of Card -->
                            <div class="d-flex justify-content-between align-items-center text-xs mt-2">
                                <span class="badge badge-primary-light">
                                    <i class="ri-user-star-line text-xs"></i> <?= Html::encode($task->assignedUser?->namaAnggota ?? 'Unassigned') ?>
                                </span>
                                
                                <?php if ($task->deadline): ?>
                                    <span class="inline-flex align-items-center gap-1 fw-semibold <?= (new \DateTime() > $task->deadline) ? 'text-danger' : 'text-muted' ?>">
                                        <i class="ri-calendar-line"></i> <?= $task->deadline->format('d M') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

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
        <h3 class="m-0 mb-4 fw-extrabold"><i class="ri-add-circle-line text-primary"></i> Tambah Tugas Baru</h3>
        <form action="<?= $urlGenerator->generate('kanban/add-task', ['program_id' => $program->id]) ?>" method="POST" class="form-grid">
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

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    function toggleDropdown(evt, id) {
        evt.stopPropagation();
        const el = document.getElementById(id);
        const allDropdowns = document.querySelectorAll('.kanban-dropdown-menu');
        allDropdowns.forEach(dd => {
            if (dd.id !== id) dd.style.display = 'none';
        });
        if (el.style.display === 'none' || el.style.display === '') {
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    }

    document.addEventListener('click', function() {
        document.querySelectorAll('.kanban-dropdown-menu').forEach(dd => {
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
