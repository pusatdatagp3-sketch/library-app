<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Guru\Model\GuruEntity $guru
 * @var array $errors
 * @var array $data
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 * @var App\Web\Kamar\Model\Kamar[] $kamarList
 * @var App\Web\Konsulat\Model\Konsulat[] $konsulatList
 */

$this->setTitle('Edit Data Guru: ' . $guru->nama);
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Edit Data Guru</h1>
            <p class="crud-subtitle">Perbarui data guru di bawah ini.</p>
        </div>
        <a href="<?= $urlGenerator->generate('guru/index') ?>" class="btn btn-secondary">
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
        'kamarList' => $kamarList,
        'konsulatList' => $konsulatList,
        'formAction' => $urlGenerator->generate('guru/update', ['kdg' => $guru->kdg]),
        'submitLabel' => 'Perbarui Data',
        'showReset' => false,
        'cancelUrl' => $urlGenerator->generate('guru/index'),
    ]) ?>
</div>
