<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $user
 * @var array $roles
 * @var array $errors
 * @var array $data
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Edit Pengguna: ' . $user['username']);
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Edit Pengguna</h1>
            <p class="crud-subtitle">Perbarui informasi untuk pengguna: <strong><?= Html::encode($user['username']) ?></strong></p>
        </div>
        <div>
            <a href="<?= $urlGenerator->generate('users/index') ?>" class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </div>

    <?= $this->render('./_form', [
        'user' => $user,
        'roles' => $roles,
        'errors' => $errors,
        'data' => $data,
        'csrf' => $csrf,
        'formAction' => $urlGenerator->generate('users/update/post', ['id' => $user['id']]),
        'submitLabel' => 'Perbarui Pengguna',
        'isUpdate' => true,
    ]) ?>
</div>
