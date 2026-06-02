<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/**
 * @var App\Web\Task\Model\Task $task
 * @var App\Web\Task\Model\TaskProgressLog[] $logs
 * @var Yiisoft\Aliases\Aliases $aliases
 */

$logs = $logs ?? [];
?>

<div id="task-log-modal-<?= $task->id ?>" class="kanban-modal">
    <div class="kanban-modal-content task-log-modal-inner">
        <span onclick="closeModal('task-log-modal-<?= $task->id ?>')" class="kanban-modal-close">&times;</span>

        <h3 class="m-0 mb-1 fw-extrabold">
            <i class="ri-history-line text-primary"></i> Log Aktivitas
        </h3>
        <p class="text-xs text-muted mb-4">
            <i class="ri-task-line"></i> <?= Html::encode($task->judul) ?>
            <?php if ($task->kodeKampus): ?>
                <span class="badge-campus text-xs" style="margin-left: 6px; font-size: 0.65rem; padding: 1px 4px;"><?= Html::encode($task->kodeKampus) ?></span>
            <?php endif; ?>
        </p>

        <?php if (empty($logs)): ?>
            <div class="task-log-empty">
                <i class="ri-inbox-2-line" style="font-size:2rem; opacity:.4;"></i>
                <p class="text-sm text-muted mt-2">Belum ada riwayat aktivitas untuk tugas ini.</p>
            </div>
        <?php else: ?>
            <div class="task-log-timeline">
                <?php foreach ($logs as $log): ?>
                    <?php
                        $hasFoto  = !empty($log->buktiFoto);
                        $hasAlasan = !empty($log->alasan);
                        $progBaru = $log->progressBaru ?? 0;
                        // Badge color based on progress
                        if ($progBaru >= 100)      { $badgeClass = 'badge-success-light'; $icon = 'ri-check-double-line'; }
                        elseif ($progBaru >= 50)   { $badgeClass = 'badge-primary-light'; $icon = 'ri-loader-3-line'; }
                        elseif ($progBaru > 0)     { $badgeClass = 'badge-warning-light'; $icon = 'ri-time-line'; }
                        else                       { $badgeClass = 'badge-danger-light';  $icon = 'ri-close-circle-line'; }
                    ?>
                    <div class="task-log-item">
                        <div class="task-log-dot">
                            <i class="<?= $icon ?>"></i>
                        </div>
                        <div class="task-log-body">
                            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                <p class="text-sm fw-semibold m-0 text-color">
                                    <?= Html::encode($log->keterangan ?? 'Perubahan status') ?>
                                </p>
                                <span class="badge <?= $badgeClass ?>" style="white-space:nowrap; font-size:10px;">
                                    <?= ($log->progressSebelumnya ?? 0) ?>% → <?= $progBaru ?>%
                                </span>
                            </div>

                            <?php if ($hasAlasan): ?>
                                <div class="task-log-reason">
                                    <i class="ri-chat-quote-line text-warning"></i>
                                    <span class="text-xs text-muted"><?= Html::encode($log->alasan) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($hasFoto): ?>
                                <div class="task-log-photo-wrap">
                                    <img
                                        src="<?= Html::encode($aliases->get('@baseUrl') . '/' . $log->buktiFoto) ?>"
                                        alt="Bukti foto"
                                        class="task-log-photo"
                                        onclick="openPhotoLightbox(this.src)"
                                    >
                                    <span class="text-xs text-muted"><i class="ri-image-line"></i> Foto bukti</span>
                                </div>
                            <?php endif; ?>

                            <p class="text-xs text-muted mt-1 m-0">
                                <i class="ri-calendar-event-line"></i>
                                <?= $log->createdAt ? $log->createdAt->format('d M Y, H:i') : '-' ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
