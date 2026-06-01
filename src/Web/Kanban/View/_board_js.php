<?php

declare(strict_types=1);

/**
 * @var App\Web\Kanban\Model\KanbanColumn[] $columns
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\View\WebView $this
 */
?>
<!-- DATA KOLOM & STATE UTAMA JAVASCRIPT -->
<script>
const KANBAN_COLUMNS = <?= json_encode(array_map(fn($c) => [
    'id'             => $c->id,
    'nama'           => $c->nama,
    'progress'       => (int)$c->progress,
    'requiresProof'  => (bool)$c->requiresProof,
    'requiresReason' => (bool)$c->requiresReason,
], $columns), JSON_UNESCAPED_UNICODE) ?>;

// Global state
let _pendingDrag = null; // { taskId, columnId, taskIds, fromEl, fromIndex, item }
</script>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<!-- Decomposed View Logics -->
<?= $this->render('./logic/_modal_utils') ?>
<?= $this->render('./logic/_photo_lightbox') ?>
<?= $this->render('./logic/_proof_modal') ?>
<?= $this->render('./logic/_reason_modal') ?>
<?= $this->render('./logic/_move_task') ?>
<?= $this->render('./logic/_sortable_init', ['urlGenerator' => $urlGenerator]) ?>
