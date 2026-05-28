<?php

declare(strict_types=1);

use App\Web\Shared\Layout\Main\MainAsset;
use Yiisoft\Html\Html;

/**
 * @var \App\Shared\ApplicationParams $applicationParams
 * @var Yiisoft\Aliases\Aliases $aliases
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $content
 * @var string|null $csrf
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Router\CurrentRoute $currentRoute
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var \App\Web\Auth\UserSession $userSession
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
    <meta charset="<?= Html::encode($applicationParams->charset) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?= $aliases->get('@baseUrl/favicon.svg') ?>" type="image/svg+xml">
    <title><?= Html::encode($this->getTitle()) ?></title>
    <?php $this->head() ?>
</head>
<body data-theme="light">
<script>
    (function() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.body.setAttribute('data-theme', savedTheme);
        
        const sidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        if (sidebarCollapsed && window.innerWidth > 1024) {
            document.body.classList.add('sidebar-collapsed');
        }
    })();
</script>
<?php $this->beginBody() ?>

<div class="sidebar-overlay" id="sidebar-overlay"></div>

<div class="admin-wrapper">
    <!-- Sidebar Left -->
    <aside class="admin-sidebar">
        <!-- Brand / Logo -->
        <div class="sidebar-brand">
            <!-- Logo Full (shown when expanded) -->
            <svg class="logo-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 216.495" style="height: 28px; width: auto; flex-shrink: 0;">
                <style>.st0 { fill: #40b3d8 }</style>
                <g>
                    <path class="st0" d="M130.275 137.619c-1.461-19.284-7.681-31.784-10.71-38.869-3.027-7.083-7.682-13.714-7.685-13.703-.009.03-4.191 22.566-11.71 38.871-1.255 2.723-2.922 6.123-4.604 8.861-5.191 9.165-12.723 17.933-17.212 26.82-4.451 8.808-5.277 17.531-4.863 27.448.417 9.972 2.721 19.753 4.933 29.448 8.338-1.799 15.595-4.877 21.873-8.812 16.521-10.358 26.504-26.924 29.341-44.772 0 0 .138-.725.2-1.609 1.279-13.794.856-18.146.437-23.683z"/>
                    <path d="M130.275 137.619c-1.461-19.284-7.681-31.784-10.71-38.869-3.027-7.083-7.682-13.714-7.685-13.703l-.001.005c0-.003.001-.006.001-.006l-1.111-1.652C86.405 49.193 39.68 32.117.202 47.944c-1.901 23.973 9.213 65.22 49.76 76.725 16.392 5.043 29.518 3.735 45.605 8.109-.001 0-.001.001-.001.001s16.352 5.699 25.865 14.245c4.279 3.843 8.564 8.901 8.349 14.917 1.355-14.317.921-18.697.495-24.322z" style="fill:#83c933"/>
                    <path d="M156.139-316.254c-.1-.618-.403-1.121-.759-1.621-.023-.032-.044-.067-.065-.1-.016-.019-.032-.041-.048-.061-.8-1.04-1.73-1.985-2.572-2.995-.801-.948-1.601-1.901-2.401-2.852-.961-1.142-1.922-2.283-2.884-3.427-1.073-1.277-2.149-2.558-3.227-3.836-1.144-1.36-2.287-2.716-3.429-4.077-1.165-1.383-2.33-2.767-3.491-4.149l-3.413-4.056c-1.066-1.268-2.13-2.533-3.192-3.798-.947-1.121-1.888-2.244-2.834-3.365-.777-.923-1.552-1.847-2.328-2.772-.564-.667-1.127-1.333-1.687-2.002l-.903-1.076c-.006-.007-.119-.134-.118-.134.018-.02.036-.039.055-.058l.148-.161c.083-.086.163-.176.246-.265.11-.121.223-.241.334-.362.141-.15.282-.301.423-.452.166-.179.331-.359.497-.538.192-.206.385-.412.577-.619.214-.233.432-.464.644-.695l.711-.768c.259-.276.518-.554.775-.832.275-.297.553-.593.826-.89.294-.317.586-.633.88-.948l.925-.996c.324-.348.647-.695.967-1.041.336-.361.669-.72 1.005-1.08.343-.371.688-.743 1.035-1.115.353-.381.707-.759 1.059-1.143.362-.39.723-.777 1.083-1.166.368-.395.734-.791 1.101-1.185.374-.4.744-.8 1.115-1.197.372-.404.745-.806 1.118-1.21.375-.401.75-.804 1.125-1.208.371-.401.745-.804 1.118-1.207.371-.398.745-.797 1.115-1.198.368-.394.734-.791 1.102-1.186.361-.39.723-.777 1.085-1.168.353-.382.707-.762 1.06-1.146.345-.372.694-.745 1.037-1.117l1.005-1.082c.323-.348.649-.694.969-1.044.31-.331.619-.666.929-.999l.884-.948c.275-.299.551-.599.832-.897.259-.277.515-.554.774-.836.238-.256.477-.511.715-.769.218-.233.433-.465.651-.701.192-.208.385-.416.58-.624.167-.182.334-.362.506-.544.491-.534 1.043-1.026 1.437-1.647.445-.701.598-1.585.15-2.327-.298-.493-.794-.82-1.344-.968-.285-.074-.58-.106-.871-.106h-9.304c-1.237 0-1.86 0-3.097 1.243l-29.142 32.86v-60.144c0-1.858-1.242-3.102-3.1-3.102h-6.823c-1.86 0-3.099 1.243-3.099 3.102v104.169c0 1.858 1.239 3.097 3.099 3.097h6.823c1.858 0 3.1-1.239 3.1-3.097v-34.723l29.142 35.96c1.238 1.243 1.86 1.86 3.097 1.86h9.658c.769 0 1.57-.283 2.136-.814.452-.428.575-1.049.478-1.647m-65.59-70.705v-6.818c0-1.863-1.242-2.484-3.1-2.484-12.404 0-21.834.302-29.894 4.023-2.024.964-2.971 2.178-2.971 3.42v70.683c0 1.86 1.242 3.1 2.479 3.1h7.441c1.863 0 2.481-1.2 2.481-3.1v-65.102c5.58-1.243 10.541-1.243 20.464-1.243 3.1 0 3.1-.619 3.1-2.479m-58.905 37.823c0 14.88-6.202 24.183-19.843 24.183s-19.84-9.304-19.84-24.183v-11.162c0-14.878 6.199-24.182 19.84-24.182s19.843 9.303 19.843 24.182v11.162zm13.02 0v-11.162c0-21.701-11.163-35.342-32.865-35.342-21.703 0-32.86 14.262-32.86 35.342v11.162c0 21.702 11.158 35.341 32.86 35.341 21.702.621 32.865-13.638 32.865-35.341m-75.028 3.102v-45.885c0-1.858-1.238-3.102-3.1-3.102h-6.408c-1.858 0-3.1 1.243-3.1 3.102v45.885c0 16.119-6.613 21.698-18.392 21.698-3.723 0-7.855-.617-12.194-1.858v-65.725c0-1.858-1.239-3.102-3.102-3.102h-6.616c-1.86 0-3.097 1.243-3.097 3.102v65.725c-4.342 1.242-8.475 1.858-12.193 1.858-12.401 0-18.396-5.579-18.396-21.698v-45.885c0-1.858-1.238-3.102-3.097-3.102h-6.411c-1.86 0-3.097 1.243-3.097 3.102v45.885c0 26.04 14.258 32.86 29.759 32.86 7.444 0 13.642-1.238 19.222-3.718 6.203 2.481 11.783 3.718 19.223 3.718 16.74 0 30.999-6.82 30.999-32.86m-119.668-14.264h-39.064v-1.24c0-15.501 6.82-22.942 19.843-22.942 13.022 0 19.22 6.202 19.22 22.942v1.24zm13.02 6.82v-8.06c0-20.461-11.159-34.102-32.241-34.102-21.083 0-32.862 13.02-32.862 34.102v13.021c0 27.282 16.121 35.342 33.48 35.342 9.924 0 17.983-.621 26.042-2.481 2.484-.619 3.1-1.237 3.1-3.1v-4.96c0-1.237-1.238-1.86-2.479-1.86h-.621c-6.82 1.239-17.361 1.86-26.042 1.86-13.641 0-20.461-6.2-20.461-23.561v-3.1h48.987c1.859.001 3.097-1.238 3.097-3.101m-74.404 35.965v-50.583c0-7.348-.336-16.488-6.347-21.681-1.479-1.277-3.182-2.279-4.975-3.058-2.194-.954-4.519-1.591-6.865-2.046-1.777-.348-3.573-.587-5.376-.759h-3.1c-8.678 0-14.882 1.24-19.219 4.96-4.343-4.341-10.541-4.96-19.222-4.96h-3.102c-17.359 0-26.04 10.541-26.04 25.424v52.703c0 1.858 1.237 3.102 2.478 3.102h7.441c1.861 0 2.481-1.243 2.481-3.102v-52.703c0-12.404 5.58-14.264 13.02-14.264h3.1c9.304 0 12.404 1.86 12.404 9.922v57.045c0 1.858 1.238 3.102 2.481 3.102h7.439c1.858 0 2.481-1.243 2.481-3.102v-57.045c0-8.062 3.1-9.922 12.399-9.922h3.1c8.062 0 13.02 1.86 13.02 14.264v52.703c0 1.858 1.243 3.102 2.481 3.102h4.96c4.342-.001 4.961-1.244 4.961-3.102m-116.57-8.062c-4.341.622-11.159 1.239-17.359 1.239-14.264 0-19.223-9.92-19.223-25.421v-9.92c0-15.499 4.342-25.422 19.223-25.422 6.2 0 13.019.62 17.359 1.24v58.284zm13.02 5.581v-68.823c0-1.863-.617-2.481-3.097-3.102-8.06-1.858-17.985-3.102-27.282-3.102-21.704 0-32.244 14.264-32.244 35.963v9.922c0 21.702 9.92 35.962 32.244 35.962 8.681 0 19.222-1.238 27.282-3.1 2.481-1.239 3.097-1.86 3.097-3.72m-63.862-66.965v-6.818c0-1.863-1.242-2.484-3.1-2.484-12.404 0-22.323.621-30.384 4.342-1.858.621-2.481 1.86-2.481 3.102v70.683c0 1.86 1.242 3.1 2.481 3.1h7.439c1.863 0 2.481-1.2 2.481-3.1v-65.102c5.581-1.243 10.541-1.243 20.464-1.243 3.1-.001 3.1-.62 3.1-2.48m-46.505-29.763v-4.96c0-1.238-.532-1.965-3.1-2.481-2.481-.62-8.06-.62-13.02-.62-13.641 0-22.319 4.96-22.319 20.463v87.427c0 1.71 1.387 3.097 3.097 3.097h6.823c1.713 0 3.1-1.387 3.1-3.097v-67.587h22.94c1.86 0 2.479-1.239 2.479-2.479v-4.96c0-1.858-1.237-2.481-2.479-2.481h-23.561v-9.92c0-8.681 1.858-9.922 9.92-9.922h11.688c1.022 0 2.212-.047 3.03-.378.952-.383 1.402-.933 1.402-2.102" style="fill:#062730" transform="translate(843.83 480.501)"/>
                    <path class="st0" d="M-47.14-535.193c5.58 0 9.919 4.341 9.919 9.922 0 5.579-4.339 9.921-9.919 9.921-5.582 0-9.922-4.342-9.922-9.921 0-4.962 4.339-9.922 9.922-9.922m-35.964 0c5.578 0 9.922 4.341 9.922 9.922 0 5.579-4.343 9.921-9.922 9.921-5.579 0-9.923-4.342-9.923-9.921 0-4.962 4.344-9.922 9.923-9.922m9.922 121.53v-92.387c0-1.858-1.243-3.1-3.103-3.1h-13.641c-1.858 0-3.1 1.242-3.1 3.1v92.387c0 1.86 1.242 3.1 3.1 3.1h13.641c1.859-.621 3.103-1.858 3.103-3.1m-35.346 2.479v-97.346c-7.439 0-19.222 4.96-19.222 15.501v66.344c-4.338.621-10.541 1.243-14.258 1.243-12.404 0-15.501-8.683-15.501-20.464v-47.745c-.621-11.78-13.643-14.88-19.222-14.88v62.006c0 22.319 11.161 36.581 34.723 36.581 3.717 0 11.161-1.242 14.258-1.861l.164 1.608c.858 19.313-20.625 11.415-33.024 10.173-9.302 0-15.501 7.441-17.364 16.122 5.581 1.86 22.944 3.721 35.344 4.96 22.324.621 34.102-11.778 34.102-32.242m70.688-2.479v-92.387c0-1.858-1.24-3.1-3.1-3.1h-13.641c-1.86 0-3.102 1.242-3.102 3.1v92.387c0 1.86 1.242 3.1 3.102 3.1h13.641c1.86-.621 3.1-1.858 3.1-3.1" transform="translate(396.196 576.03)"/>
                    <path d="M-120.995-431.898c-5.761-16.855-3.32-28.149 7.22-43.866 5.027-7.499 13.706-16.197 21.313-21.111 30.691 19.232 42.039 55.054 30.752 88.323-8.214 24.207-15.921 34.357-35.408 59.263 2.271-26.674-7.117-45.167-16.334-65.715-2.346-5.228-5.59-11.179-7.543-16.894" style="fill-rule:evenodd;clip-rule:evenodd;fill:#f18a2a" transform="translate(233.564 496.875)"/>
                    <path d="M-115.623-336.617c.215-6.016-4.07-11.074-8.349-14.917-9.513-8.546-25.864-14.245-25.864-14.245 1.682-2.738 3.349-6.138 4.604-8.861 7.519-16.305 11.701-38.841 11.71-38.871.003-.01 4.657 6.62 7.685 13.703 3.029 7.085 9.248 19.584 10.71 38.869.425 5.625.859 10.005-.496 24.322z" style="fill:#7fb93c" transform="translate(245.403 498.558)"/>
                </g>
            </svg>
            <!-- Logo Icon (shown when collapsed) -->
            <svg class="logo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 216.495" style="height: 28px; width: auto; flex-shrink: 0; display: none;">
                <style>.st0 { fill: #40b3d8 }</style>
                <g>
                    <path class="st0" d="M130.275 137.619c-1.461-19.284-7.681-31.784-10.71-38.869-3.027-7.083-7.682-13.714-7.685-13.703-.009.03-4.191 22.566-11.71 38.871-1.255 2.723-2.922 6.123-4.604 8.861-5.191 9.165-12.723 17.933-17.212 26.82-4.451 8.808-5.277 17.531-4.863 27.448.417 9.972 2.721 19.753 4.933 29.448 8.338-1.799 15.595-4.877 21.873-8.812 16.521-10.358 26.504-26.924 29.341-44.772 0 0 .138-.725.2-1.609 1.279-13.794.856-18.146.437-23.683z"/>
                    <path d="M130.275 137.619c-1.461-19.284-7.681-31.784-10.71-38.869-3.027-7.083-7.682-13.714-7.685-13.703l-.001.005c0-.003.001-.006.001-.006l-1.111-1.652C86.405 49.193 39.68 32.117.202 47.944c-1.901 23.973 9.213 65.22 49.76 76.725 16.392 5.043 29.518 3.735 45.605 8.109-.001 0-.001.001-.001.001s16.352 5.699 25.865 14.245c4.279 3.843 8.564 8.901 8.349 14.917 1.355-14.317.921-18.697.495-24.322z" style="fill:#83c933"/>
                    <path d="M-120.995-431.898c-5.761-16.855-3.32-28.149 7.22-43.866 5.027-7.499 13.706-16.197 21.313-21.111 30.691 19.232 42.039 55.054 30.752 88.323-8.214 24.207-15.921 34.357-35.408 59.263 2.271-26.674-7.117-45.167-16.334-65.715-2.346-5.228-5.59-11.179-7.543-16.894" style="fill-rule:evenodd;clip-rule:evenodd;fill:#f18a2a" transform="translate(233.564 496.875)"/>
                    <path d="M-115.623-336.617c.215-6.016-4.07-11.074-8.349-14.917-9.513-8.546-25.864-14.245-25.864-14.245 1.682-2.738 3.349-6.138 4.604-8.861 7.519-16.305 11.701-38.841 11.71-38.871.003-.01 4.657 6.62 7.685 13.703 3.029 7.085 9.248 19.584 10.71 38.869.425 5.625.859 10.005-.496 24.322z" style="fill:#7fb93c" transform="translate(245.403 498.558)"/>
                </g>
            </svg>
            <span class="brand-text" style="font-weight: 700; font-size: 1.1rem; letter-spacing: -0.02em;">TEQIC Admin</span>
        </div>

        <!-- Sidebar Navigation -->
        <div class="sidebar-nav">
            <!-- Group 1: Utama -->
            <div class="nav-group">
                <span class="nav-group-title">Utama</span>
                <div class="sidebar-menu">
                    <a href="<?= $urlGenerator->generate('home') ?>" class="<?= $currentRoute->getName() === 'home' ? 'active' : '' ?>" title="Home">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Home</span>
                    </a>
                    <a href="<?= $urlGenerator->generate('hello') ?>" class="<?= $currentRoute->getName() === 'hello' ? 'active' : '' ?>" title="Hello">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.625.625 0 11-1.25 0 .625.625 0 011.25 0zm4.5 0a.625.625 0 11-1.25 0 .625.625 0 011.25 0zm4.5 0a.625.625 0 11-1.25 0 .625.625 0 011.25 0zM12 3c5.385 0 9.75 3.62 9.75 8.082 0 2.2-1.077 4.195-2.817 5.568-.135.107-.218.271-.218.445v2.87a.3.3 0 01-.482.24l-3.47-2.603a.75.75 0 00-.45-.148H12c-5.385 0-9.75-3.62-9.75-8.082C2.25 6.62 6.615 3 12 3z" />
                        </svg>
                        <span>Hello</span>
                    </a>
                </div>
            </div>

            <!-- Group 2: Data Master -->
            <div class="nav-group">
                <span class="nav-group-title">Data Master</span>
                <div class="sidebar-menu">
                    <a href="<?= $urlGenerator->generate('guru/index') ?>" class="<?= str_starts_with((string)($currentRoute->getName() ?? ''), 'guru') ? 'active' : '' ?>" title="Data Guru">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 019.918 5.84 50.45 50.45 0 00-2.658.813m-15.482 0A50.703 50.703 0 0112 12.056c1.777 0 3.513-.198 5.178-.577m-15.482 0a50.56 50.56 0 00-2.91 4.594M21.75 10.147a50.56 50.56 0 012.91 4.594m-2.91-4.594a50.703 50.703 0 01-5.178 1.332m-2.909 6.275a50.4 50.4 0 01-5.178-1.332m5.178 1.332l-.001.002a24.272 24.272 0 01-3.447-.894m3.448.892a24.277 24.277 0 003.448-.892m0 0a23.953 23.953 0 005.178-1.332m-5.178 1.332v1.54c0 .641-.31 1.24-.826 1.603a11.506 11.506 0 01-6.7 1.63 11.506 11.506 0 01-6.7-1.63 2.002 2.002 0 01-.826-1.603v-1.54z" />
                        </svg>
                        <span>Data Guru</span>
                    </a>

                    <?php if ($userSession->isLoggedIn() && $userSession->hasPermission('view_kamar')): ?>
                        <a href="<?= $urlGenerator->generate('kamar/index') ?>" class="<?= str_starts_with((string)($currentRoute->getName() ?? ''), 'kamar') ? 'active' : '' ?>" title="Data Kamar">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M2.25 21h19.5M3 10h18M3 7h18M3 4h18" />
                            </svg>
                            <span>Data Kamar</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($userSession->isLoggedIn() && $userSession->hasPermission('view_konsulat')): ?>
                        <a href="<?= $urlGenerator->generate('konsulat/index') ?>" class="<?= str_starts_with((string)($currentRoute->getName() ?? ''), 'konsulat') ? 'active' : '' ?>" title="Data Konsulat">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-.778.099-1.533.284-2.253" />
                            </svg>
                            <span>Data Konsulat</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($userSession->isLoggedIn() && $userSession->hasPermission('view_santri')): ?>
                        <a href="<?= $urlGenerator->generate('santri/index') ?>" class="<?= str_starts_with((string)($currentRoute->getName() ?? ''), 'santri') ? 'active' : '' ?>" title="Data Santri">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            <span>Data Santri</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($userSession->isLoggedIn() && $userSession->hasPermission('view_pelanggaran')): ?>
                        <a href="<?= $urlGenerator->generate('pelanggaran/index') ?>" class="<?= str_starts_with((string)($currentRoute->getName() ?? ''), 'pelanggaran') ? 'active' : '' ?>" title="Data Pelanggaran">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <span>Data Pelanggaran</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Group 3: Pengaturan -->
            <?php if ($userSession->isLoggedIn() && ($userSession->hasPermission('manage_gii') || $userSession->hasPermission('manage_rbac'))): ?>
                <div class="nav-group">
                    <span class="nav-group-title">Sistem</span>
                    <div class="sidebar-menu">
                        <?php if ($userSession->hasPermission('manage_gii')): ?>
                            <a href="<?= $urlGenerator->generate('gii/index') ?>" class="<?= str_starts_with((string)($currentRoute->getName() ?? ''), 'gii') ? 'active' : '' ?>" title="Gii Generator">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                                </svg>
                                <span>Gii Generator</span>
                            </a>
                        <?php endif; ?>

                        <?php if ($userSession->hasPermission('manage_rbac')): ?>
                            <a href="<?= $urlGenerator->generate('rbac/index') ?>" class="<?= str_starts_with((string)($currentRoute->getName() ?? ''), 'rbac') ? 'active' : '' ?>" title="Manajemen RBAC">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                                <span>Manajemen RBAC</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Main Container -->
    <div class="admin-main">
        <!-- Top Header -->
        <header class="admin-header">
            <div class="header-left-actions">
                <button class="sidebar-toggle" id="sidebar-toggle" title="Toggle Sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <h2 class="page-title"><?= Html::encode($this->getTitle() ?: 'Dashboard') ?></h2>
            </div>

            <div class="header-right-actions">
                <!-- Theme Toggle Button -->
                <button class="theme-toggle" id="theme-toggle" title="Ubah Tema">
                    <!-- Sun Icon -->
                    <svg class="sun-icon" style="display: none;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21M4.5 12H2.25m19.5 0H19.5M18.364 5.636l-1.591 1.591M8.228 15.772l-1.591 1.591m0-10.182l1.591 1.591m10.136 10.136l1.591 1.591M12 8.25a3.75 3.75 0 100 7.5 3.75 3.75 0 000-7.5z" />
                    </svg>
                    <!-- Moon Icon -->
                    <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </button>

                <!-- Profile Info / Login -->
                <?php if ($userSession->isLoggedIn()): ?>
                    <div class="user-profile-badge">
                        <div class="avatar-circle">
                            <?= strtoupper(substr($userSession->getUsername() ?? 'U', 0, 2)) ?>
                        </div>
                        <div class="user-meta">
                            <span class="username"><?= Html::encode($userSession->getUsername()) ?></span>
                            <span class="role"><?= Html::encode($userSession->getUserRole()) ?></span>
                        </div>
                        <form action="<?= $urlGenerator->generate('logout') ?>" method="POST" style="margin: 0;">
                            <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">
                            <button type="submit" class="logout-btn-nav" title="Keluar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                </svg>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <a href="<?= $urlGenerator->generate('login') ?>" class="btn btn-primary btn-login-nav">
                        Masuk
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Page Content -->
        <main class="admin-content">
            <?= $content ?>
        </main>

        <!-- Footer -->
        <footer class="admin-footer">
            <div class="footer_copyright">
                <a href="https://www.yiiframework.com/" target="_blank" rel="noopener">
                    © <?= date('Y') ?>  <?= Html::encode($applicationParams->name) ?>
                </a>
            </div>
            <div class="footer_icons">
                <a href="https://github.com/yiisoft" target="_blank" rel="noopener" title="GitHub">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 291.32 291.32">
                        <path d="M145.66 0C65.219 0 0 65.219 0 145.66c0 80.45 65.219 145.66 145.66 145.66s145.66-65.21 145.66-145.66C291.319 65.219 226.1 0 145.66 0zm40.802 256.625c-.838-11.398-1.775-25.518-1.83-31.235-.364-4.388-.838-15.549-11.434-22.677 42.068-3.523 62.087-26.774 63.526-57.499 1.202-17.497-5.754-32.883-18.107-45.3.628-13.282-.401-29.023-1.256-35.941-9.486-2.731-31.608 8.949-37.79 13.947-13.037-5.062-44.945-6.837-64.336 0-13.747-9.668-29.396-15.64-37.926-13.974-7.875 17.452-2.813 33.948-1.275 35.914-10.142 9.268-24.289 20.675-20.447 44.572 6.163 35.04 30.816 53.94 70.508 58.564-8.466 1.73-9.896 8.048-10.606 10.788-26.656 10.997-34.275-6.791-37.644-11.425-11.188-13.847-21.23-9.832-21.849-9.614-.601.218-1.056 1.092-.992 1.511.564 2.986 6.655 6.018 6.955 6.263 8.257 6.154 11.316 17.27 13.2 20.438 11.844 19.473 39.374 11.398 39.638 11.562.018 1.702-.191 16.032-.355 27.184C64.245 245.992 27.311 200.2 27.311 145.66c0-65.365 52.984-118.348 118.348-118.348S264.008 80.295 264.008 145.66c0 51.008-32.318 94.332-77.546 110.965z"/>
                    </svg>
                </a>
                <a href="https://join.slack.com/t/yii/shared_invite/enQtMzQ4MDExMDcyNTk2LTc0NDQ2ZTZhNjkzZDgwYjE4YjZlNGQxZjFmZDBjZTU3NjViMDE4ZTMxNDRkZjVlNmM1ZTA1ODVmZGUwY2U3NDA" target="_blank" rel="noopener" title="Slack">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M6.194 14.644c0 1.16-.943 2.107-2.103 2.107a2.11 2.11 0 0 1-2.104-2.107 2.11 2.11 0 0 1 2.104-2.106h2.103v2.106zm1.061 0c0-1.16.944-2.106 2.104-2.106a2.11 2.11 0 0 1 2.103 2.106v5.274a2.11 2.11 0 0 1-2.103 2.106 2.108 2.108 0 0 1-2.104-2.106v-5.274zm2.104-8.455c-1.16 0-2.104-.948-2.104-2.107s.944-2.106 2.104-2.106a2.11 2.11 0 0 1 2.103 2.106v2.107H9.359zm0 1.06a2.11 2.11 0 0 1 2.103 2.107 2.11 2.11 0 0 1-2.103 2.106H4.092a2.11 2.11 0 0 1-2.104-2.106 2.11 2.11 0 0 1 2.104-2.107h5.267zm8.447 2.107c0-1.16.943-2.107 2.103-2.107a2.11 2.11 0 0 1 2.104 2.107 2.11 2.11 0 0 1-2.104 2.106h-2.103V9.356zm-1.061 0c0 1.16-.944 2.106-2.104 2.106a2.11 2.11 0 0 1-2.103-2.106V4.082a2.11 2.11 0 0 1 2.103-2.106c1.16 0 2.104.946 2.104 2.106v5.274zm-2.104 8.455c1.16 0 2.104.948 2.104 2.107s-.944 2.106-2.104 2.106a2.11 2.11 0 0 1-2.103-2.106v-2.107h2.103zm0-1.06a2.11 2.11 0 0 1-2.103-2.107 2.11 2.11 0 0 1 2.103-2.106h5.268a2.11 2.11 0 0 1 2.104 2.106 2.11 2.11 0 0 1-2.104 2.107h-5.268z"/>
                    </svg>
                </a>
            </div>
        </footer>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('theme-toggle');
        const sunIcon = themeToggle.querySelector('.sun-icon');
        const moonIcon = themeToggle.querySelector('.moon-icon');

        function updateToggleIcons(theme) {
            if (theme === 'dark') {
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            } else {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            }
        }

        // Set initial toggle icons based on current applied theme
        const currentTheme = document.body.getAttribute('data-theme') || 'light';
        updateToggleIcons(currentTheme);

        themeToggle.addEventListener('click', function() {
            const activeTheme = document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.body.setAttribute('data-theme', activeTheme);
            localStorage.setItem('theme', activeTheme);
            updateToggleIcons(activeTheme);
        });

        // Sidebar toggling (Mobile Overlay vs Desktop Collapse)
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                if (window.innerWidth > 1024) {
                    document.body.classList.toggle('sidebar-collapsed');
                    const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                    localStorage.setItem('sidebar-collapsed', isCollapsed ? 'true' : 'false');
                } else {
                    document.body.classList.toggle('sidebar-open');
                }
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                document.body.classList.remove('sidebar-open');
            });
        }
    });
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
