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
 * @var App\Web\Task\Model\TaskProgressLog[][] $taskLogs
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
            <div class="mb-2">
                <a href="<?= $urlGenerator->generate('program/view', ['id' => $program->id]) ?>" class="btn btn-sm btn-secondary">
                    <i class="ri-arrow-left-line"></i> Kembali ke Program (<?= Html::encode($program->namaProgram) ?>)
                </a>
            </div>
            <h1 class="text-xl fw-extrabold m-0 d-flex align-items-center gap-2">
                <i class="ri-kanban-view text-primary"></i> Papan Kanban: <?= Html::encode($program->namaProgram) ?>
            </h1>
        </div>

        <div class="d-flex gap-2">
            <button onclick="openModal('add-task-modal')" class="btn btn-primary">
                <i class="ri-add-line"></i> Tambah Tugas
            </button>
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
                                    <button onclick="openModal('task-log-modal-<?= $task->id ?>')" class="kanban-card-more-btn" title="Lihat Log">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    <button onclick="toggleDropdown(event, 'card-actions-<?= $task->id ?>')" class="kanban-card-more-btn">
                                        <i class="ri-more-2-line"></i>
                                    </button>
                                    <div id="card-actions-<?= $task->id ?>" class="kanban-dropdown-menu">
                                        <button type="button" onclick="openModal('edit-task-modal-<?= $task->id ?>')" class="kanban-dropdown-item">
                                            <i class="ri-pencil-line"></i> Edit
                                        </button>
                                        <button type="button" onclick="openMoveTaskModal('<?= $task->id ?>', '<?= $task->kanbanColumnId ?>')" class="kanban-dropdown-item">
                                            <i class="ri-arrow-left-right-line"></i> Pindahkan
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
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- TASK LOG & EDIT MODALS (dikumpulkan di luar board agar tidak terjebak overflow/stacking context) -->
<?php foreach ($tasks as $task): ?>
    <?= $this->render('./_task_log', [
        'task' => $task,
        'logs' => $taskLogs[$task->id] ?? [],
    ]) ?>
    <?= $this->render('./_edit_task_modal', [
        'task' => $task,
        'members' => $members,
        'urlGenerator' => $urlGenerator,
        'program' => $program,
    ]) ?>
<?php endforeach; ?>

<!-- MODAL TAMBAH TUGAS -->
<?= $this->render('./_add_task_modal', [
    'program' => $program,
    'members' => $members,
    'urlGenerator' => $urlGenerator,
]) ?>

<!-- SHARED KANBAN ACTION MODALS (Proof, Reason, Move) -->
<?= $this->render('./_kanban_action_modals') ?>

<!-- JAVASCRIPT KANBAN -->
<?= $this->render('./_board_js', [
    'columns' => $columns,
    'urlGenerator' => $urlGenerator,
]) ?>

