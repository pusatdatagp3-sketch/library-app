<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Entitas\Model\Entitas[] $models
 * @var UrlGeneratorInterface $urlGenerator
 * @var \App\Web\Auth\Model\UserSession $userSession
 * @var string $title
 * @var string $prefix
 * @var string|null $successMsg
 * @var array $errorMsgs
 */

$this->setTitle($title);
$createRoute = "{$prefix}/create";
$updateRoute = "{$prefix}/update";
$deleteRoute = "{$prefix}/delete";
$viewRoute   = "{$prefix}/view";
?>

<div class="crud-container">
    <div class="crud-header">
        <div>
            <h1 class="crud-title"><i class="ri-organization-chart text-primary"></i> <?= Html::encode($title) ?></h1>
            <p class="crud-subtitle">Kelola struktur organisasi dan entitas pembina KMI.</p>
        </div>

        <?php if ($userSession->hasPermission("create_{$prefix}")): ?>
            <a href="<?= $urlGenerator->generate($createRoute) ?>" class="btn btn-primary">
                <i class="ri-add-line btn-icon"></i> Tambah Entitas
            </a>
        <?php endif; ?>
    </div>

    <?php if ($successMsg): ?>
        <div class="alert alert-success">
            <i class="ri-checkbox-circle-fill alert-icon"></i>
            <div><?= Html::encode($successMsg) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMsgs)): ?>
        <div class="alert alert-danger">
            <i class="ri-error-warning-fill alert-icon"></i>
            <div>
                <?php foreach ($errorMsgs as $errorMsg): ?>
                    <p><?= Html::encode($errorMsg) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($models)): ?>
        <div class="card empty-state text-center">
            <i class="ri-folder-open-line empty-icon text-muted text-2xl"></i>
            <h3>Belum Ada Entitas</h3>
            <p class="text-muted">Tambahkan entitas pertama untuk memulai pengorganisasian.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-auto-fill gap-4 mt-3">
            <?php foreach ($models as $model): ?>
                <div class="card entitas-card hover-glow">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge badge-primary-light">ID: #<?= Html::encode((string)$model->id) ?></span>
                            <?php if ($model->kodeKampus): ?>
                                <span class="badge-campus"><?= Html::encode($model->kodeKampus) ?></span>
                            <?php endif; ?>
                        </div>
                        <h3 class="text-lg fw-bold mb-2 text-color"><?= Html::encode((string)$model->nama) ?></h3>
                        <p class="text-muted text-sm mb-4 line-clamp-3">
                            <?= Html::encode((string)($model->deskripsi ?? 'Tidak ada deskripsi.')) ?>
                        </p>
                    </div>

                    <div class="entitas-card-footer">
                        <a href="<?= $urlGenerator->generate($viewRoute, ['id' => $model->id]) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="ri-eye-line"></i> Detail &amp; Anggota
                        </a>

                        <div class="action-buttons">
                            <?php if ($userSession->hasPermission("update_{$prefix}")): ?>
                                <a href="<?= $urlGenerator->generate($updateRoute, ['id' => $model->id]) ?>" class="btn-action btn-edit" title="Edit">
                                    <i class="ri-pencil-line"></i>
                                </a>
                            <?php endif; ?>

                            <?php if ($userSession->hasPermission("delete_{$prefix}")): ?>
                                <form action="<?= $urlGenerator->generate($deleteRoute, ['id' => $model->id]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus entitas ini beserta seluruh data program di dalamnya?');" class="form-inline">
                                    <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                    <button type="submit" class="btn-action btn-delete" title="Hapus">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
