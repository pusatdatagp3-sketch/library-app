<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var string|null $error
 * @var string $username
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $csrf
 */

$this->setTitle('Masuk ke Sistem');
?>

<div class="login-wrapper" style="position: relative; min-height: 80vh; display: flex; align-items: center; justify-content: center;">
    <!-- Floating Dark Mode Toggle -->
    <button id="theme-toggle" class="theme-toggle" style="position: absolute; top: 20px; right: 20px; z-index: 10; border-radius: 50%; width: 40px; height: 40px; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; background: var(--bg-card); transition: all 0.3s ease;">
        <i class="ri-sun-line sun-icon" style="display: none; font-size: 1.25rem;"></i>
        <i class="ri-moon-line moon-icon" style="display: none; font-size: 1.25rem;"></i>
    </button>

    <div class="login-card card" style="width: 100%; max-width: 420px; padding: 2.5rem; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); background: var(--bg-card); border: 1px solid var(--border);">
        <div class="login-header" style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 60px; height: 60px; border-radius: 16px; background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1.25rem auto; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
                <i class="ri-shield-user-line"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0 0 0.5rem 0; letter-spacing: -0.025em;">Sign In</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem; line-height: 1.5; margin: 0;">Masukkan kredensial Anda untuk mengakses sistem TEQIC.</p>
        </div>

        <?php if ($error !== null): ?>
            <div class="alert alert-danger" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; border-radius: 8px; padding: 12px 16px;">
                <i class="ri-error-warning-line" style="font-size: 1.25rem; flex-shrink: 0;"></i>
                <div class="alert-content" style="font-size: 0.875rem; font-weight: 500;"><?= Html::encode($error) ?></div>
            </div>
        <?php endif; ?>

        <form action="<?= $urlGenerator->generate('login') ?>" method="POST" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="username" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: var(--text-main); margin-bottom: 0.5rem; display: block;">Username</label>
                <div class="input-wrapper" style="position: relative;">
                    <i class="ri-user-line" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.1rem;"></i>
                    <input type="text" id="username" name="username" class="form-control" value="<?= Html::encode($username) ?>" placeholder="Masukkan username" style="padding-left: 40px; box-sizing: border-box;" required autofocus>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.75rem;">
                <label for="password" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: var(--text-main); margin-bottom: 0.5rem; display: block;">Password</label>
                <div class="input-wrapper" style="position: relative;">
                    <i class="ri-lock-line" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.1rem;"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" style="padding-left: 40px; box-sizing: border-box;" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary login-btn" style="padding: 12px; font-size: 0.95rem; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s ease; width: 100%; box-sizing: border-box;">
                <span>Masuk Aplikasi</span>
                <i class="ri-login-box-line" style="font-size: 1.1rem;"></i>
            </button>
        </form>
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
});
</script>
