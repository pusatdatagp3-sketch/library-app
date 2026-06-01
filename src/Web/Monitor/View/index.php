<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $modulData
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Monitor');
?>

<div class="monitor-container">
    <!-- Header -->
    <div class="monitor-header">
        <div class="monitor-title">
            <h1><i class="ri-dashboard-line text-primary"></i> Monitor</h1>
            <p>Memantau tugas berstatus <b>To Do</b> dan <b>On Progress</b> di seluruh entitas berdasarkan masing-masing modul.</p>
        </div>
    </div>

    <!-- Modules Grid -->
    <div class="modules-grid">
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
                $totalActiveTasks = count($todoTasks) + count($progressTasks);
            ?>

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
                                        <div class="task-meta">
                                            <i class="ri-folder-open-line"></i>
                                            <a href="<?= $urlGenerator->generate('kanban/board', ['program_id' => $task->program->id]) ?>">
                                                <?= Html::encode($task->program->namaProgram) ?>
                                            </a>
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
                                        <div class="task-meta">
                                            <i class="ri-folder-open-line"></i>
                                            <a href="<?= $urlGenerator->generate('kanban/board', ['program_id' => $task->program->id]) ?>">
                                                <?= Html::encode($task->program->namaProgram) ?>
                                            </a>
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
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
