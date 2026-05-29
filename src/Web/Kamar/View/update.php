<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Kamar\Model\Kamar $kamar
 * @var array $errors
 * @var array $data
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Edit Kamar');
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Edit Kamar</h1>
            <p class="crud-subtitle">Perbarui informasi untuk kamar <strong><?= Html::encode($kamar->namaKamar) ?></strong>.</p>
        </div>
        <a href="<?= $urlGenerator->generate('kamar/index') ?>" class="btn btn-secondary">
            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
            </svg>
            Kembali
        </a>
    </div>

    <?= $this->render('./_form', [
        'errors' => $errors,
        'data' => $data,
        'csrf' => $csrf,
        'formAction' => $urlGenerator->generate('kamar/update', ['id' => $kamar->id]),
        'submitLabel' => 'Simpan Perubahan',
        'showReset' => false,
        'cancelUrl' => null,
    ]) ?>
</div>
