<?php

declare(strict_types=1);

use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Perizinan\Model\Perizinan $perizinan
 * @var array $errors
 * @var array $data
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Edit Data Perizinan');
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Edit Data Perizinan</h1>
            <p class="crud-subtitle">Perbarui data perizinan terpilih.</p>
        </div>
        <a href="<?= $urlGenerator->generate('perizinan/index') ?>" class="btn btn-secondary">
            Kembali
        </a>
    </div>

    <?= $this->render('./_form', [
        'errors' => $errors,
        'data' => $data,
        'formAction' => $urlGenerator->generate('perizinan/update', ['id' => $perizinan->id]),
        'submitLabel' => 'Simpan Perubahan',
        'showReset' => false,
        'cancelUrl' => $urlGenerator->generate('perizinan/index'),
    ]) ?>
</div>
