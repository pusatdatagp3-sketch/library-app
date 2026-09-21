<?php
declare(strict_types=1);

use App\Web\Shared\Layout\Main\MainAsset;
use Yiisoft\Html\Html;

/**
 * @var \App\Shared\ApplicationParams $applicationParams
 * @var Yiisoft\Aliases\Aliases $aliases
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $content
 * @var Yiisoft\View\WebView $this
 */

$assetManager->register(MainAsset::class);

$this->addCssFiles($assetManager->getCssFiles());
$this->addCssStrings($assetManager->getCssStrings());
$this->addJsFiles($assetManager->getJsFiles());
$this->addJsStrings($assetManager->getJsStrings());
$this->addJsVars($assetManager->getJsVars());

$this->beginPage()
?>
<!DOCTYPE html>
<html lang="<?= Html::encode($applicationParams->locale) ?>">
<head>
    <?php
    $faviconFilePath = $aliases->get('@public/images/kutubia_favicon.svg');
    if (!file_exists($faviconFilePath)) {
        $faviconFilePath = $aliases->get('@public/images/kutubia_logo.svg');
    }
    $faviconData = file_exists($faviconFilePath)
        ? 'data:image/svg+xml;base64,' . base64_encode((string) file_get_contents($faviconFilePath))
        : '/librarytest/public/images/kutubia_logo.svg';
    ?>
    <meta charset="<?= Html::encode($applicationParams->charset) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?= $faviconData ?>" type="image/svg+xml">
    <link rel="shortcut icon" href="<?= $faviconData ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= $faviconData ?>">
    <title><?= Html::encode($this->getTitle() ? $this->getTitle() . ' - KUTUBIA' : 'KUTUBIA') ?></title>
    <?php $this->head() ?>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body data-theme="light">
    <script>
    (function() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.body.setAttribute('data-theme', savedTheme);
    })();
    </script>
<?php $this->beginBody() ?>

<main class="login-layout-wrapper">
    <?= $content ?>
</main>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
