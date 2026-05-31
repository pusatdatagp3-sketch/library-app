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
$viewRoute = "{$prefix}/view";
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
            <i class="ri-folder-open-line empty-icon" style="font-size: 4rem; color: var(--text-muted);"></i>
            <h3>Belum Ada Entitas</h3>
            <p class="text-muted">Tambahkan entitas pertama untuk memulai pengorganisasian.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
            <?php foreach ($models as $model): ?>
                <div class="card entitas-card hover-glow" style="display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease; border: 1px solid rgba(0,0,0,0.05);">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <span class="badge badge-primary-light" style="background: var(--primary-light); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 50px; font-weight: 600; font-size: 0.75rem;">
                                ID: #<?= Html::encode((string)$model->id) ?>
                            </span>
                        </div>
                        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-color);">
                            <?= Html::encode((string)$model->nama) ?>
                        </h3>
                        <p class="text-muted" style="font-size: 0.875rem; margin-bottom: 1.5rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; height: 3.8em; line-height: 1.3;">
                            <?= Html::encode((string)($model->deskripsi ?? 'Tidak ada deskripsi.')) ?>
                        </p>
                    </div>

                    <div style="border-top: 1px solid rgba(0,0,0,0.05); padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                        <a href="<?= $urlGenerator->generate($viewRoute, ['id' => $model->id]) ?>" class="btn btn-sm btn-outline-primary" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                            <i class="ri-eye-line"></i> Detail & Anggota
                        </a>
                        
                        <div class="action-buttons" style="display: flex; gap: 0.5rem;">
                            <?php if ($userSession->hasPermission("update_{$prefix}")): ?>
                                <a href="<?= $urlGenerator->generate($updateRoute, ['id' => $model->id]) ?>" class="btn-action btn-edit" title="Edit">
                                    <i class="ri-pencil-line"></i>
                                </a>
                            <?php endif; ?>

                            <?php if ($userSession->hasPermission("delete_{$prefix}")): ?>
                                <form action="<?= $urlGenerator->generate($deleteRoute, ['id' => $model->id]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus entitas ini beserta seluruh data program di dalamnya?');" style="display:inline;">
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
