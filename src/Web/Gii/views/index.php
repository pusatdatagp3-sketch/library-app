<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var array $tables
 * @var UrlGeneratorInterface $urlGenerator
 * @var string|null $successMsg
 * @var string|null $errorMsg
 * @var array $logs
 */

$this->setTitle('Gii Module Generator');
$csrf = $this->getParameter('csrf');
?>

<div class="crud-container max-w-lg">
    <div class="crud-header">
        <div>
            <h1 class="crud-title">Gii Module Generator</h1>
            <p class="crud-subtitle">Buat modul CRUD (ActiveRecord, Controller, Views, Routes & RBAC) secara instan berdasarkan skema database.</p>
        </div>
    </div>

    <?php if ($successMsg): ?>
        <div class="alert alert-success">
            <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <strong><?= Html::encode($successMsg) ?></strong>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger">
            <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <div><?= Html::encode($errorMsg) ?></div>
        </div>
    <?php endif; ?>

    <div class="card">
        <form action="<?= $urlGenerator->generate('gii/generate') ?>" method="POST" class="form-grid">
            <input type="hidden" name="_csrf" value="<?= Html::encode((string)$csrf) ?>">

            <div class="form-group">
                <label for="table" class="form-label">Pilih Tabel Database</label>
                <select id="table" name="table" class="form-control" required>
                    <option value="">-- Pilih Tabel --</option>
                    <?php foreach ($tables as $t): ?>
                        <option value="<?= Html::encode($t) ?>"><?= Html::encode($t) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted" style="display:block; margin-top: 4px;">Pilih tabel database yang akan dibuatkan modul CRUD-nya.</small>
            </div>

            <div class="form-group">
                <label for="modelName" class="form-label">Nama Class Model (PascalCase)</label>
                <input type="text" id="modelName" name="modelName" class="form-control" placeholder="Contoh: Santri, Pelanggaran, Keuangan" required>
                <small class="text-muted" style="display:block; margin-top: 4px;">Nama ini akan digunakan sebagai nama Model, Controller, nama Folder, dan Namespace.</small>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin: 8px 0; font-size: 0.85rem; color: #475569;">
                <h5 style="margin: 0 0 6px 0; color: #1e293b; font-weight: 600;">Opsi Auto-Generate:</h5>
                <ul style="margin: 0; padding-left: 20px; line-height: 1.4;">
                    <li>Membuat Model `src/Web/{ModelName}/{ModelName}.php`</li>
                    <li>Membuat Controller `src/Web/{ModelName}/{ModelName}Controller.php`</li>
                    <li>Membuat subfolder `views/` beserta file `index`, `create`, dan `update`</li>
                    <li>Menambahkan route otomatis di `config/common/routes.php`</li>
                    <li>Seeding dynamic RBAC permissions & route mapping</li>
                    <li>Membuat link menu di navbar layout secara dinamis</li>
                </ul>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 21l8.982-2.133a1.75 1.75 0 001.29-1.29L21 9m-11.187 6.904L3 13.682l8.982-2.133a1.75 1.75 0 001.29-1.29L15 3.5m-5.187 12.404L15 9" />
                    </svg>
                    Generate Modul Sekarang
                </button>
            </div>
        </form>
    </div>

    <?php if (!empty($logs)): ?>
        <div class="card" style="margin-top: 20px; border-left: 4px solid #0f766e;">
            <h4 style="margin: 0 0 10px 0; display:flex; align-items:center; gap: 8px; color: #0f766e;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 20px; height: 20px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.03 0 1.9.693 2.166 1.638m-7.377 12.408l9-9M12 20.25a8.25 8.25 0 110-16.5 8.25 8.25 0 010 16.5z" />
                </svg>
                Log File & Proses
            </h4>
            <div style="background: #1e293b; color: #f1f5f9; font-family: monospace; font-size: 0.8rem; padding: 12px 16px; border-radius: 6px; max-height: 250px; overflow-y: auto;">
                <?php foreach ($logs as $log): ?>
                    <div style="margin-bottom: 4px; line-height: 1.4;">
                        <span style="color: #4ade80;">[INFO]</span> <?= Html::encode($log) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
