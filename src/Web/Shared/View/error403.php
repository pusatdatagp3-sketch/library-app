<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 */

$this->setTitle('Akses Ditolak (403)');
?>

<div class="error-wrapper">
    <div class="error-card card text-center">
        <div class="error-icon-container">
            <svg class="error-illustration" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>
        <h1 class="error-code">403</h1>
        <h2 class="error-title">Akses Ditolak / Terbatas</h2>
        <p class="error-description">
            Maaf, akun Anda tidak memiliki izin yang diperlukan untuk mengakses halaman ini.
            Hubungi administrator sistem jika Anda memerlukan hak akses tambahan.
        </p>
        <div class="error-actions">
            <a href="<?= $urlGenerator->generate('home') ?>" class="btn btn-primary">
                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>
</div>
