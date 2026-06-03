<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $modulData
 * @var array $taskReasons
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Monitor');
?>

<style>
    .monitor-modules-grid {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -0.75rem;
    }
    .monitor-card-wrapper {
        width: 100%;
        padding: 0 0.75rem;
        margin-bottom: 1.5rem;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
    }
    @media (min-width: 768px) {
        .monitor-card-wrapper {
            width: 50%;
        }
    }
    @media (min-width: 1366px) {
        .monitor-card-wrapper {
            width: 25%;
            margin-bottom: 0;
        }
    }
    .monitor-card-wrapper .module-card {
        margin: 0;
        height: 100%;
        box-sizing: border-box;
    }
</style>

<div class="monitor-container">
    <!-- Header -->
    <div class="monitor-header">
        <div class="monitor-title">
            <h1><i class="ri-dashboard-line text-primary"></i> Monitor</h1>
            <p>Memantau tugas berstatus <b>To Do</b>, <b>On Progress</b>, <b>Done</b>, serta status <b>Pending</b> dan <b>Rejected</b> di seluruh entitas.</p>
        </div>
    </div>

    <!-- Section 1: Progress Monitor -->
    <div style="margin-top: 1rem; margin-bottom: 0.5rem;">
        <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
            <i class="ri-slideshow-line text-primary"></i> Progress Monitor
        </h2>
    </div>

    <!-- Modules Grid for Progress Monitor -->
    <div class="monitor-modules-grid">
        <?php foreach ($modulData as $modulId => $data): ?>
            <?php
                $modulName      = $data['modul']->nama;
                $modulNameLower = strtolower($modulName);

                $colorClass = 'module-default';
                if (str_contains($modulNameLower, 'fungsionaris')) {
                    $colorClass = 'module-fungsionaris';
                } elseif (str_contains($modulNameLower, 'panitia') || str_contains($modulNameLower, 'kepanitiaan')) {
                    $colorClass = 'module-kepanitiaan';
                } elseif (str_contains($modulNameLower, 'empower') || str_contains($modulNameLower, 'empowering')) {
                    $colorClass = 'module-empowering';
                } elseif (str_contains($modulNameLower, 'koordinator')) {
                    $colorClass = 'module-koordinator';
                }

                $todoTasks       = $data['todoTasks'];
                $progressTasks   = $data['progressTasks'];
                $doneTasks       = $data['doneTasks'];
                $totalActiveTasks = count($todoTasks) + count($progressTasks);
            ?>

            <div class="monitor-card-wrapper">
                <div class="module-card">
                <div class="module-card-header <?= $colorClass ?>">
                    <span><?= Html::encode(ucwords($modulName)) ?></span>
                    <span class="badge-on-dark"><?= $totalActiveTasks ?> Tugas Aktif</span>
                </div>

                <div class="module-card-body">
                    <!-- TO DO SECTION -->
                    <div>
                        <div class="section-title">
                            <span><i class="ri-list-check"></i> To Do</span>
                            <span class="count-badge"><?= count($todoTasks) ?></span>
                        </div>
                        <div class="tasks-list">
                            <?php if (empty($todoTasks)): ?>
                                <div class="monitor-empty-state">Tidak ada tugas To Do.</div>
                            <?php else: ?>
                                <?php foreach ($todoTasks as $task): ?>
                                    <div class="task-item-card">
                                        <div class="task-meta" style="flex-wrap: wrap; gap: 0.25rem 0.75rem; text-transform: none;">
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; color: var(--text-muted); font-size: 0.7rem; font-weight: 600;">
                                                <i class="ri-organization-chart" style="color: var(--primary);"></i>
                                                <?= Html::encode($task->program->entitas?->nama ?? 'Entitas') ?>
                                            </span>
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 700;">
                                                <i class="ri-folder-open-line" style="color: var(--primary);"></i>
                                                <a href="<?= $urlGenerator->generate('kanban/board', ['program_id' => $task->program->id]) ?>">
                                                    <?= Html::encode($task->program->namaProgram) ?>
                                                </a>
                                            </span>
                                            <?php if ($task->kodeKampus): ?>
                                                <span class="badge-campus text-xs" style="font-size: 0.65rem; padding: 1px 4px;"><?= Html::encode($task->kodeKampus) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="task-title"><?= Html::encode($task->judul) ?></h4>

                                        <div class="task-progress-mini">
                                            <div class="task-progress-label-row">
                                                <span>Progres</span>
                                                <span><?= $task->progress ?>%</span>
                                            </div>
                                            <div class="task-progress-bar-container">
                                                <div class="task-progress-bar bar-todo" style="width:<?= $task->progress ?>%"></div>
                                            </div>
                                        </div>

                                        <div class="task-footer">
                                            <span class="task-pic">
                                                <i class="ri-user-star-line"></i> <?= Html::encode($task->assignedUser?->namaAnggota ?? 'Unassigned') ?>
                                            </span>
                                            <?php if ($task->deadline): ?>
                                                <span class="task-deadline <?= (new \DateTime() > $task->deadline) ? 'deadline-overdue' : 'deadline-ok' ?>">
                                                    <i class="ri-calendar-line"></i> <?= $task->deadline->format('d M Y') ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ON PROGRESS SECTION -->
                    <div>
                        <div class="section-title">
                            <span><i class="ri-refresh-line"></i> On Progress</span>
                            <span class="count-badge"><?= count($progressTasks) ?></span>
                        </div>
                        <div class="tasks-list">
                            <?php if (empty($progressTasks)): ?>
                                <div class="monitor-empty-state">Tidak ada tugas On Progress.</div>
                            <?php else: ?>
                                <?php foreach ($progressTasks as $task): ?>
                                    <div class="task-item-card">
                                        <div class="task-meta" style="flex-wrap: wrap; gap: 0.25rem 0.75rem; text-transform: none;">
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; color: var(--text-muted); font-size: 0.7rem; font-weight: 600;">
                                                <i class="ri-organization-chart" style="color: var(--primary);"></i>
                                                <?= Html::encode($task->program->entitas?->nama ?? 'Entitas') ?>
                                            </span>
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 700;">
                                                <i class="ri-folder-open-line" style="color: var(--primary);"></i>
                                                <a href="<?= $urlGenerator->generate('kanban/board', ['program_id' => $task->program->id]) ?>">
                                                    <?= Html::encode($task->program->namaProgram) ?>
                                                </a>
                                            </span>
                                            <?php if ($task->kodeKampus): ?>
                                                <span class="badge-campus text-xs" style="font-size: 0.65rem; padding: 1px 4px;"><?= Html::encode($task->kodeKampus) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="task-title"><?= Html::encode($task->judul) ?></h4>

                                        <div class="task-progress-mini">
                                            <div class="task-progress-label-row">
                                                <span>Progres</span>
                                                <span><?= $task->progress ?>%</span>
                                            </div>
                                            <div class="task-progress-bar-container">
                                                <div class="task-progress-bar bar-progress" style="width:<?= $task->progress ?>%"></div>
                                            </div>
                                        </div>

                                        <div class="task-footer">
                                            <span class="task-pic">
                                                <i class="ri-user-star-line"></i> <?= Html::encode($task->assignedUser?->namaAnggota ?? 'Unassigned') ?>
                                            </span>
                                            <?php if ($task->deadline): ?>
                                                <span class="task-deadline <?= (new \DateTime() > $task->deadline) ? 'deadline-overdue' : 'deadline-ok' ?>">
                                                    <i class="ri-calendar-line"></i> <?= $task->deadline->format('d M Y') ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- DONE SECTION -->
                    <div>
                        <div class="section-title">
                            <span><i class="ri-checkbox-circle-line"></i> Done</span>
                            <span class="count-badge"><?= count($doneTasks) ?></span>
                        </div>
                        <div class="tasks-list">
                            <?php if (empty($doneTasks)): ?>
                                <div class="monitor-empty-state">Tidak ada tugas Done.</div>
                            <?php else: ?>
                                <?php foreach ($doneTasks as $task): ?>
                                    <div class="task-item-card">
                                        <div class="task-meta" style="flex-wrap: wrap; gap: 0.25rem 0.75rem; text-transform: none;">
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; color: var(--text-muted); font-size: 0.7rem; font-weight: 600;">
                                                <i class="ri-organization-chart" style="color: var(--primary);"></i>
                                                <?= Html::encode($task->program->entitas?->nama ?? 'Entitas') ?>
                                            </span>
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 700;">
                                                <i class="ri-folder-open-line" style="color: var(--primary);"></i>
                                                <a href="<?= $urlGenerator->generate('kanban/board', ['program_id' => $task->program->id]) ?>">
                                                    <?= Html::encode($task->program->namaProgram) ?>
                                                </a>
                                            </span>
                                            <?php if ($task->kodeKampus): ?>
                                                <span class="badge-campus text-xs" style="font-size: 0.65rem; padding: 1px 4px;"><?= Html::encode($task->kodeKampus) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="task-title"><?= Html::encode($task->judul) ?></h4>

                                        <div class="task-progress-mini">
                                            <div class="task-progress-label-row">
                                                <span>Progres</span>
                                                <span><?= $task->progress ?>%</span>
                                            </div>
                                            <div class="task-progress-bar-container">
                                                <div class="task-progress-bar bar-done" style="width:<?= $task->progress ?>%"></div>
                                            </div>
                                        </div>

                                        <div class="task-footer">
                                            <span class="task-pic">
                                                <i class="ri-user-star-line"></i> <?= Html::encode($task->assignedUser?->namaAnggota ?? 'Unassigned') ?>
                                            </span>
                                            <?php if ($task->deadline): ?>
                                                <span class="task-deadline <?= (new \DateTime() > $task->deadline) ? 'deadline-overdue' : 'deadline-ok' ?>">
                                                    <i class="ri-calendar-line"></i> <?= $task->deadline->format('d M Y') ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Section 2: Pending & Rejected Monitor -->
    <div style="margin-top: 2.5rem; margin-bottom: 0.5rem;">
        <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
            <i class="ri-error-warning-line text-primary"></i> Pending &amp; Rejected Monitor
        </h2>
    </div>

    <!-- Modules Grid for Pending/Rejected -->
    <div class="monitor-modules-grid">
        <?php foreach ($modulData as $modulId => $data): ?>
            <?php
                $modulName      = $data['modul']->nama;
                $modulNameLower = strtolower($modulName);

                $colorClass = 'module-default';
                if (str_contains($modulNameLower, 'fungsionaris')) {
                    $colorClass = 'module-fungsionaris';
                } elseif (str_contains($modulNameLower, 'panitia') || str_contains($modulNameLower, 'kepanitiaan')) {
                    $colorClass = 'module-kepanitiaan';
                } elseif (str_contains($modulNameLower, 'empower') || str_contains($modulNameLower, 'empowering')) {
                    $colorClass = 'module-empowering';
                } elseif (str_contains($modulNameLower, 'koordinator')) {
                    $colorClass = 'module-koordinator';
                }

                $pendingTasks     = $data['pendingTasks'];
                $rejectedTasks    = $data['rejectedTasks'];
                $totalSpecialTasks = count($pendingTasks) + count($rejectedTasks);
            ?>

            <div class="monitor-card-wrapper">
                <div class="module-card">
                <div class="module-card-header <?= $colorClass ?>">
                    <span><?= Html::encode(ucwords($modulName)) ?></span>
                    <span class="badge-on-dark"><?= $totalSpecialTasks ?> Tugas Terkendala</span>
                </div>

                <div class="module-card-body">
                    <!-- PENDING SECTION -->
                    <div>
                        <div class="section-title">
                            <span><i class="ri-time-line"></i> Pending</span>
                            <span class="count-badge"><?= count($pendingTasks) ?></span>
                        </div>
                        <div class="tasks-list">
                            <?php if (empty($pendingTasks)): ?>
                                <div class="monitor-empty-state">Tidak ada tugas Pending.</div>
                            <?php else: ?>
                                <?php foreach ($pendingTasks as $task): ?>
                                    <div class="task-item-card">
                                        <div class="task-meta" style="flex-wrap: wrap; gap: 0.25rem 0.75rem; text-transform: none;">
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; color: var(--text-muted); font-size: 0.7rem; font-weight: 600;">
                                                <i class="ri-organization-chart" style="color: var(--primary);"></i>
                                                <?= Html::encode($task->program->entitas?->nama ?? 'Entitas') ?>
                                            </span>
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 700;">
                                                <i class="ri-folder-open-line" style="color: var(--primary);"></i>
                                                <a href="<?= $urlGenerator->generate('kanban/board', ['program_id' => $task->program->id]) ?>">
                                                    <?= Html::encode($task->program->namaProgram) ?>
                                                </a>
                                            </span>
                                            <?php if ($task->kodeKampus): ?>
                                                <span class="badge-campus text-xs" style="font-size: 0.65rem; padding: 1px 4px;"><?= Html::encode($task->kodeKampus) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="task-title"><?= Html::encode($task->judul) ?></h4>

                                        <div class="task-progress-mini">
                                            <div class="task-progress-label-row">
                                                <span>Progres</span>
                                                <span><?= $task->progress ?>%</span>
                                            </div>
                                            <div class="task-progress-bar-container">
                                                <div class="task-progress-bar bar-pending" style="width:<?= $task->progress ?>%"></div>
                                            </div>
                                        </div>

                                        <?php if (isset($taskReasons[$task->id])): ?>
                                            <div class="task-log-reason" style="margin-top: 0.6rem; margin-bottom: 0.2rem; font-size: 0.75rem; border-left: 3px solid #fbbf24; background: rgba(251, 191, 36, 0.08); padding: 6px 10px; border-radius: 6px; display: flex; align-items: flex-start; gap: 6px;">
                                                <i class="ri-chat-history-line text-warning" style="font-size: 0.85rem; margin-top: 1px;"></i>
                                                <span class="text-xs text-muted" style="word-break: break-word; line-height: 1.3;">
                                                    <strong style="color: #d97706;">Alasan Ditunda:</strong> <?= Html::encode($taskReasons[$task->id]) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="task-footer">
                                            <span class="task-pic">
                                                <i class="ri-user-star-line"></i> <?= Html::encode($task->assignedUser?->namaAnggota ?? 'Unassigned') ?>
                                            </span>
                                            <?php if ($task->deadline): ?>
                                                <span class="task-deadline <?= (new \DateTime() > $task->deadline) ? 'deadline-overdue' : 'deadline-ok' ?>">
                                                    <i class="ri-calendar-line"></i> <?= $task->deadline->format('d M Y') ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- REJECTED SECTION -->
                    <div>
                        <div class="section-title">
                            <span><i class="ri-close-circle-line"></i> Rejected</span>
                            <span class="count-badge"><?= count($rejectedTasks) ?></span>
                        </div>
                        <div class="tasks-list">
                            <?php if (empty($rejectedTasks)): ?>
                                <div class="monitor-empty-state">Tidak ada tugas Rejected.</div>
                            <?php else: ?>
                                <?php foreach ($rejectedTasks as $task): ?>
                                    <div class="task-item-card">
                                        <div class="task-meta" style="flex-wrap: wrap; gap: 0.25rem 0.75rem; text-transform: none;">
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; color: var(--text-muted); font-size: 0.7rem; font-weight: 600;">
                                                <i class="ri-organization-chart" style="color: var(--primary);"></i>
                                                <?= Html::encode($task->program->entitas?->nama ?? 'Entitas') ?>
                                            </span>
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 700;">
                                                <i class="ri-folder-open-line" style="color: var(--primary);"></i>
                                                <a href="<?= $urlGenerator->generate('kanban/board', ['program_id' => $task->program->id]) ?>">
                                                    <?= Html::encode($task->program->namaProgram) ?>
                                                </a>
                                            </span>
                                            <?php if ($task->kodeKampus): ?>
                                                <span class="badge-campus text-xs" style="font-size: 0.65rem; padding: 1px 4px;"><?= Html::encode($task->kodeKampus) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="task-title"><?= Html::encode($task->judul) ?></h4>

                                        <div class="task-progress-mini">
                                            <div class="task-progress-label-row">
                                                <span>Progres</span>
                                                <span><?= $task->progress ?>%</span>
                                            </div>
                                            <div class="task-progress-bar-container">
                                                <div class="task-progress-bar bar-rejected" style="width:<?= $task->progress ?>%"></div>
                                            </div>
                                        </div>

                                        <?php if (isset($taskReasons[$task->id])): ?>
                                            <div class="task-log-reason" style="margin-top: 0.6rem; margin-bottom: 0.2rem; font-size: 0.75rem; border-left: 3px solid var(--danger); background: rgba(239, 68, 68, 0.08); padding: 6px 10px; border-radius: 6px; display: flex; align-items: flex-start; gap: 6px;">
                                                <i class="ri-chat-delete-line text-danger" style="font-size: 0.85rem; margin-top: 1px;"></i>
                                                <span class="text-xs text-muted" style="word-break: break-word; line-height: 1.3;">
                                                    <strong class="text-danger">Alasan Ditolak:</strong> <?= Html::encode($taskReasons[$task->id]) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="task-footer">
                                            <span class="task-pic">
                                                <i class="ri-user-star-line"></i> <?= Html::encode($task->assignedUser?->namaAnggota ?? 'Unassigned') ?>
                                            </span>
                                            <?php if ($task->deadline): ?>
                                                <span class="task-deadline <?= (new \DateTime() > $task->deadline) ? 'deadline-overdue' : 'deadline-ok' ?>">
                                                    <i class="ri-calendar-line"></i> <?= $task->deadline->format('d M Y') ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
