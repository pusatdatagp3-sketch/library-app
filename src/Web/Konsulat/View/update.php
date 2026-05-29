<?php

declare(strict_types=1);

use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Konsulat\Model\Konsulat $model
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Edit Konsulat: ' . $model->konsulat);
$csrf = $this->getParameter('csrf');
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Edit Konsulat</h1>
            <p class="crud-subtitle">Perbarui data daerah konsulat beserta pembagian kampusnya.</p>
        </div>
        <a href="<?= $urlGenerator->generate('konsulat/index') ?>" class="btn btn-secondary">
            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
            </svg>
            Kembali
        </a>
    </div>

    <?= $this->render('./_form', [
        'model' => $model,
        'csrf' => $csrf,
        'formAction' => $urlGenerator->generate('konsulat/update', ['id' => $model->id]),
        'submitLabel' => 'Perbarui Konsulat',
        'showReset' => false,
        'cancelUrl' => $urlGenerator->generate('konsulat/index'),
    ]) ?>
</div>
