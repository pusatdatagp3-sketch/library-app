<?php

    declare (strict_types = 1);

    use App\Web\Shared\Layout\Main\MainAsset;
    use Yiisoft\Html\Html;

    $sidebarMenu = require dirname(__DIR__) . '/Main/sidebar-menu.php';
    /**
 * @var \App\Shared\ApplicationParams $applicationParams
 * @var Yiisoft\Aliases\Aliases $aliases
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $content
 * @var string|null $csrf
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Router\CurrentRoute $currentRoute
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var \App\Web\Auth\Model\UserSession $userSession
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
<html lang="<?php echo Html::encode($applicationParams->locale) ?>">

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
    <meta charset="<?php echo Html::encode($applicationParams->charset) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?= $faviconData ?>" type="image/svg+xml">
    <link rel="shortcut icon" href="<?= $faviconData ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= $faviconData ?>">
    <title><?php echo Html::encode($this->getTitle() ? $this->getTitle() . ' - KUTUBIA' : 'KUTUBIA') ?></title>
    <?php $this->head()?>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css" rel="stylesheet">
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
    <?php $this->beginBody()?>

    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <div class="admin-wrapper">
        <!-- Sidebar Left -->
        <aside class="admin-sidebar">
            <!-- Brand / Logo -->
            <?php
            $sidebarLogoFilePath = $aliases->get('@public/images/kutubia_logo.svg');
            $sidebarLogoSrc = file_exists($sidebarLogoFilePath)
                ? 'data:image/svg+xml;base64,' . base64_encode((string) file_get_contents($sidebarLogoFilePath))
                : $aliases->get('@baseUrl/images/kutubia_logo.svg');
            ?>
            <div class="sidebar-brand">
                <img src="<?= $sidebarLogoSrc ?>" alt="Logo KUTUBIA" width="38" height="38" style="width: 38px; height: 38px; object-fit: contain; background: #ffffff; border-radius: 8px; padding: 4px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                <span class="brand-text fw-bold">KUTUBIA</span>
            </div>

            <!-- Sidebar Navigation -->
            <div class="sidebar-nav">

                <?php foreach ($sidebarMenu as $group): ?>

                <div class="nav-group">

                    <span class="nav-group-title">
                        <?php echo Html::encode($group['group']) ?>
                    </span>

                    <div class="sidebar-menu">

                        <?php foreach ($group['items'] as $item): ?>



                        <?php

                            if (
                                isset($item['permission']) &&
                                !$userSession->hasPermission($item['permission'])
                            ) {
                                continue;
                            }

                            $hasChildren = isset($item['children']);

                        ?>

                        <?php if (!$hasChildren): ?>

                        <a href="<?php echo $urlGenerator->generate($item['route']) ?>"
                            class="<?php echo str_starts_with((string) ($currentRoute->getName() ?? ''), explode('/', $item['route'])[0]) ? 'active' : '' ?>">

                            <i class="<?php echo $item['icon'] ?>-fill"></i>

                            <span><?php echo Html::encode($item['label']) ?></span>
                        </a>

                        <?php else: ?>
                        <?php

                            $dropdownActive = false;

                            foreach ($item['children'] as $child) {

                                $prefix = explode('/', $child['route'])[0];

                                if (
                                    str_starts_with(
                                        (string) ($currentRoute->getName() ?? ''),
                                        $prefix
                                    )
                                ) {
                                    $dropdownActive = true;
                                    break;
                                }
                            }
                        ?>
                        <div class="sidebar-dropdown <?php echo $dropdownActive ? 'open' : '' ?>">

                            <button class="sidebar-dropdown-toggle <?php echo $dropdownActive ? 'active' : '' ?>">

                                <div class="sidebar-dropdown-left">
                                    <i class="<?php echo $item['icon'] ?>-fill"></i>
                                    <span><?php echo Html::encode($item['label']) ?></span>
                                </div>

                                <i class="ri-arrow-down-s-line dropdown-arrow"></i>

                            </button>

                            <div class="sidebar-submenu">

                                <?php foreach ($item['children'] as $child): ?>

                                <?php

                                    if (
                                        isset($child['permission']) &&
                                        !$userSession->hasPermission($child['permission'])
                                    ) {
                                        continue;
                                    }

                                ?>

                                <?php
                                    $childActive = str_starts_with(
                                        (string) ($currentRoute->getName() ?? ''),
                                        explode('/', $child['route'])[0]
                                    );
                                ?>

                                <a href="<?php echo $urlGenerator->generate($child['route']) ?>"
                                    class="<?php echo $childActive ? 'active' : '' ?>">

                                    <?php if (!empty($child['icon'])): ?>
                                    <i class="<?php echo Html::encode($child['icon']) ?>-line"></i>
                                    <?php endif; ?>

                                    <span><?php echo Html::encode($child['label']) ?></span>

                                </a>

                                <?php endforeach; ?>

                            </div>

                        </div>

                        <?php endif; ?>

                        <?php endforeach; ?>

                    </div>

                </div>

                <?php endforeach; ?>

            </div>
        </aside>

        <!-- Main Container -->
        <div class="admin-main">
            <!-- Top Header -->
            <header class="admin-header">
                <div class="header-left-actions">
                    <button class="sidebar-toggle" id="sidebar-toggle" title="Toggle Sidebar">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <h2 class="page-title"><?php echo Html::encode($this->getTitle() ?: 'Dashboard') ?></h2>
                </div>

                <div class="header-right-actions">
                    <!-- Theme Toggle Button -->
                    <button class="theme-toggle" id="theme-toggle" title="Ubah Tema">
                        <!-- Sun Icon -->
                        <svg class="sun-icon" style="display: none;" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v2.25m0 13.5V21M4.5 12H2.25m19.5 0H19.5M18.364 5.636l-1.591 1.591M8.228 15.772l-1.591 1.591m0-10.182l1.591 1.591m10.136 10.136l1.591 1.591M12 8.25a3.75 3.75 0 100 7.5 3.75 3.75 0 000-7.5z" />
                        </svg>
                        <!-- Moon Icon -->
                        <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                    </button>

                    <!-- Profile Info / Login -->
                    <?php if ($userSession->isLoggedIn()): ?>
                    <div class="user-profile-badge">
                        <div class="avatar-circle">
                            <?php echo strtoupper(substr($userSession->getUsername() ?? 'U', 0, 2)) ?>
                        </div>
                        <div class="user-meta">
                            <span class="username"><?php echo Html::encode($userSession->getUsername()) ?></span>
                            <span class="role"><?php echo Html::encode($userSession->getUserRole()) ?></span>
                        </div>
                        <form action="<?php echo $urlGenerator->generate('logout') ?>" method="POST" style="margin: 0;">
                            <input type="hidden" name="_csrf" value="<?php echo Html::encode($csrf) ?>">
                            <button type="submit" class="logout-btn-nav" title="Keluar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                </svg>
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <a href="<?php echo $urlGenerator->generate('login') ?>" class="btn btn-primary btn-login-nav">
                        Masuk
                    </a>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Page Content -->
            <main class="admin-content">
                <?php echo $content ?>
            </main>

            <!-- Footer -->
            <footer class="admin-footer">
                <div class="footer_copyright">
                    <a href="https://www.yiiframework.com/" target="_blank" rel="noopener">
                        © <?php echo date('Y') ?> <?php echo Html::encode($applicationParams->name) ?>
                    </a>
                </div>
                <div class="footer_crafted text-sm text-muted fw-medium">
                    Crafted with ❤️ by PUSAT DATA GP3 🦊
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

    document.querySelectorAll('.sidebar-dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            this.parentElement.classList.toggle('open');
        });
    });
    </script>

    <?php $this->endBody()?>
</body>

</html>
<?php $this->endPage()?>