<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var string $message
 */

$this->setTitle('Hello');
?>

<div class="text-center">
    <h1>Hello!</h1>
    <p class="lead"><?= Html::encode($message) ?></p>
</div>
