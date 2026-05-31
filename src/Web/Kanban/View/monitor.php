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

$this->setTitle('Monitor Kanban Tugas');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css">

<style>
    .monitor-container {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .monitor-header {
        background: rgba(255, 255, 255, 0.45);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.04);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .monitor-title h1 {
        font-size: 1.75rem;
        font-weight: 800;
        margin: 0 0 0.25rem 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .monitor-title p {
        margin: 0;
        color: var(--text-muted);
        font-size: 0.9rem;
    }

    .modules-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }

    @media (max-width: 1200px) {
        .modules-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .modules-grid {
            grid-template-columns: 1fr;
        }
    }

    .module-card {
        background: rgba(255, 255, 255, 0.45);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.04);
        overflow: hidden;
        height: fit-content;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .module-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.06);
    }

    .module-card-header {
        padding: 1.25rem;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 700;
        font-size: 1.1rem;
    }

    /* Modul-specific Header Colors */
    .module-fungsionaris {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }

    .module-kepanitiaan {
        background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%);
    }

    .module-empowering {
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
    }

    /* Standard fallback header */
    .module-default {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    }

    .module-card-body {
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .section-title {
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.05em;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2px solid rgba(0, 0, 0, 0.03);
        padding-bottom: 0.25rem;
    }

    .section-title span.count-badge {
        background: rgba(0, 0, 0, 0.05);
        color: var(--text-color);
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.1rem 0.4rem;
        border-radius: 10px;
    }

    .tasks-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        min-height: 50px;
    }

    .empty-state {
        font-size: 0.8rem;
        color: var(--text-muted);
        text-align: center;
        padding: 1rem 0.5rem;
        border: 1px dashed rgba(0, 0, 0, 0.08);
        border-radius: 8px;
        background: rgba(0, 0, 0, 0.01);
    }

    .task-item-card {
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.04);
        border-radius: 8px;
        padding: 0.85rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .task-item-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 12px -3px rgba(0, 0, 0, 0.06);
    }

    .task-meta {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--primary);
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .task-meta a {
        color: var(--primary);
        text-decoration: none;
    }

    .task-meta a:hover {
        text-decoration: underline;
    }

    .task-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-color);
        margin: 0;
    }

    .task-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .task-pic {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        color: var(--text-muted);
        background: rgba(0, 0, 0, 0.03);
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        font-weight: 600;
    }

    .task-deadline {
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.15rem;
    }

    /* Small progress bar inside task card */
    .task-progress-mini {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .task-progress-bar-container {
        height: 4px;
        background: rgba(0,0,0,0.05);
        border-radius: 2px;
        overflow: hidden;
    }

    .task-progress-bar {
        height: 100%;
        border-radius: 2px;
        transition: width 0.3s;
    }
</style>

<div class="monitor-container">
    <!-- Header -->
    <div class="monitor-header">
        <div class="monitor-title">
            <h1><i class="ri-dashboard-line text-primary"></i> Monitor Kanban Tugas</h1>
            <p>Memantau tugas berstatus <b>To Do</b> dan <b>On Progress</b> di seluruh entitas berdasarkan masing-masing modul.</p>
        </div>
    </div>

    <!-- Modules Grid -->
    <div class="modules-grid">
        <?php foreach ($modulData as $modulId => $data): ?>
            <?php
                $modulName = $data['modul']->nama;
                $modulNameLower = strtolower($modulName);
                
                // Assign a color class
                $colorClass = 'module-default';
                if (str_contains($modulNameLower, 'fungsionaris')) {
                    $colorClass = 'module-fungsionaris';
                } elseif (str_contains($modulNameLower, 'panitia') || str_contains($modulNameLower, 'kepanitiaan')) {
                    $colorClass = 'module-kepanitiaan';
                } elseif (str_contains($modulNameLower, 'empower') || str_contains($modulNameLower, 'empowering')) {
                    $colorClass = 'module-empowering';
                }

                $todoTasks = $data['todoTasks'];
                $progressTasks = $data['progressTasks'];
                $totalActiveTasks = count($todoTasks) + count($progressTasks);
            ?>

            <div class="module-card">
                <div class="module-card-header <?= $colorClass ?>">
                    <span><?= Html::encode(ucwords($modulName)) ?></span>
                    <span class="badge" style="background: rgba(255,255,255,0.25); color: #fff; font-size: 0.8rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        <?= $totalActiveTasks ?> Tugas Aktif
                    </span>
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
                                <div class="empty-state">Tidak ada tugas To Do.</div>
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
                                        
                                        <!-- Progress Bar -->
                                        <div class="task-progress-mini">
                                            <div style="display: flex; justify-content: space-between; font-size: 0.65rem; color: var(--text-muted);">
                                                <span>Progres</span>
                                                <span><?= $task->progress ?>%</span>
                                            </div>
                                            <div class="task-progress-bar-container">
                                                <div class="task-progress-bar" style="width: <?= $task->progress ?>%; background: #3b82f6;"></div>
                                            </div>
                                        </div>

                                        <div class="task-footer">
                                            <span class="task-pic">
                                                <i class="ri-user-star-line"></i> <?= Html::encode($task->assignedUser?->namaAnggota ?? 'Unassigned') ?>
                                            </span>
                                            
                                            <?php if ($task->deadline): ?>
                                                <span class="task-deadline" style="color: <?= (new \DateTime() > $task->deadline) ? 'var(--danger)' : 'var(--text-muted)' ?>;">
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
                                <div class="empty-state">Tidak ada tugas On Progress.</div>
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

                                        <!-- Progress Bar -->
                                        <div class="task-progress-mini">
                                            <div style="display: flex; justify-content: space-between; font-size: 0.65rem; color: var(--text-muted);">
                                                <span>Progres</span>
                                                <span><?= $task->progress ?>%</span>
                                            </div>
                                            <div class="task-progress-bar-container">
                                                <div class="task-progress-bar" style="width: <?= $task->progress ?>%; background: #a855f7;"></div>
                                            </div>
                                        </div>

                                        <div class="task-footer">
                                            <span class="task-pic">
                                                <i class="ri-user-star-line"></i> <?= Html::encode($task->assignedUser?->namaAnggota ?? 'Unassigned') ?>
                                            </span>

                                            <?php if ($task->deadline): ?>
                                                <span class="task-deadline" style="color: <?= (new \DateTime() > $task->deadline) ? 'var(--danger)' : 'var(--text-muted)' ?>;">
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
