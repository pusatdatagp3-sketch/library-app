<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $permission
 * @var array $errors
 * @var array $data
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Edit Izin: ' . $permission['name']);
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Edit Izin (Permission)</h1>
            <p class="crud-subtitle">Ubah informasi kegunaan untuk kunci izin: <strong><?= Html::encode($permission['name']) ?></strong></p>
        </div>
        <div>
            <a href="<?= $urlGenerator->generate('permissions/index') ?>" class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </div>

    <?= $this->render('./_form', [
        'errors' => $errors,
        'data' => $data,
        'csrf' => $csrf,
        'permission' => $permission,
        'formAction' => $urlGenerator->generate('permissions/update/post', ['name' => $permission['name']]),
        'submitLabel' => 'Perbarui Izin',
        'isUpdate' => true,
    ]) ?>
</div>
