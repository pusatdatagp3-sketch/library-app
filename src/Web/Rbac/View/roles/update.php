<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $role
 * @var array $errors
 * @var array $data
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Edit Peran: ' . $role['name']);
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Edit Peran</h1>
            <p class="crud-subtitle">Ubah informasi penjelasan untuk peran: <strong><?= Html::encode($role['name']) ?></strong></p>
        </div>
        <div>
            <a href="<?= $urlGenerator->generate('roles/index') ?>" class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </div>

    <?= $this->render('./_form', [
        'errors' => $errors,
        'data' => $data,
        'csrf' => $csrf,
        'role' => $role,
        'formAction' => $urlGenerator->generate('roles/update/post', ['name' => $role['name']]),
        'submitLabel' => 'Perbarui Peran',
        'isUpdate' => true,
    ]) ?>
</div>
