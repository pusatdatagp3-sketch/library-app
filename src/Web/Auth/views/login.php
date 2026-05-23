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

<div class="login-wrapper">
    <div class="login-card card">
        <div class="login-header">
            <h2>Sign In</h2>
            <p>Silakan masuk untuk mengakses data guru dan fitur sistem.</p>
        </div>

        <?php if ($error !== null): ?>
            <div class="alert alert-danger">
                <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="alert-content"><?= Html::encode($error) ?></div>
            </div>
        <?php endif; ?>

        <form action="<?= $urlGenerator->generate('login') ?>" method="POST" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= Html::encode($csrf) ?>">

            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" class="form-control" value="<?= Html::encode($username) ?>" placeholder="Masukkan username" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary login-btn">
                <span>Masuk Aplikasi</span>
                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </button>
        </form>

        <div class="login-footer">
            <p>Gunakan akun demo berikut untuk masuk:</p>
            <div class="demo-accounts">
                <span class="demo-badge">admin / admin123</span>
                <span class="demo-badge">operator / operator123</span>
                <span class="demo-badge">guru / guru123</span>
            </div>
        </div>
    </div>
</div>
