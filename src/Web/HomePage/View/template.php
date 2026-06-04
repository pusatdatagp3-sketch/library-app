<?php

declare(strict_types=1);

use App\Shared\ApplicationParams;
use Yiisoft\View\WebView;
use Yiisoft\Html\Html;

/**
 * @var WebView $this
 * @var ApplicationParams $applicationParams
 * @var \Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var string|null $successMsg
 * @var array $errorMsgs
 */

$this->setTitle($applicationParams->name);
$urlGenerator = $this->getParameter('urlGenerator');
?>

<div class="dashboard-container" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.5rem;">
    
    <?php if ($successMsg): ?>
        <div class="alert alert-success">
            <i class="ri-checkbox-circle-fill alert-icon"></i>
            <div><?= Html::encode($successMsg) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMsgs)): ?>
        <div class="alert alert-danger">
            <i class="ri-error-warning-fill alert-icon"></i>
            <div>
                <?php foreach ($errorMsgs as $errorMsg): ?>
                    <p><?= Html::encode($errorMsg) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Welcome Card -->
    <div class="card mb-5 text-center overflow-hidden" style="position: relative; border: none; background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%); color: #fff; padding: 3rem 2rem;">
        <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -30px; left: -30px; width: 120px; height: 120px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
        
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem; color: #fff;">
            Selamat Datang di TEQIC
        </h1>
        <p style="font-size: 1.1rem; opacity: 0.9; max-width: 600px; margin: 0 auto 2rem auto;">
            Sistem Total Quality Control Kinerja Akademis KMI Pondok Modern Darussalam Gontor.
        </p>
        <div style="display: inline-flex; gap: 1rem; justify-content: center;">
            <span class="badge" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.25); padding: 0.4rem 1rem; border-radius: 50px; font-weight: 600; font-size: 0.85rem;">
                <i class="ri-git-branch-line"></i> Framework: Yii3
            </span>
            <span class="badge" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.25); padding: 0.4rem 1rem; border-radius: 50px; font-weight: 600; font-size: 0.85rem;">
                <i class="ri-database-2-line"></i> ORM: Cycle
            </span>
        </div>
    </div>

    <!-- Active Modules Grid -->
    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-color); display: flex; align-items: center; gap: 0.5rem;">
        <i class="ri-layout-grid-line text-primary"></i> Modul Sistem Aktif
    </h2>
    
    <style>
        .system-modules-grid {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.75rem;
        }
        .system-card-wrapper {
            width: 100%;
            padding: 0 0.75rem;
            margin-bottom: 1.5rem;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }
        @media (min-width: 640px) {
            .system-card-wrapper {
                width: 50%;
            }
        }
        .system-card-wrapper .card {
            margin: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }
    </style>

    <div class="system-modules-grid">
        <!-- Card 1: Gii Generator -->
        <div class="system-card-wrapper">
            <div class="card hover-glow" style="border: 1px solid rgba(0,0,0,0.05); transition: all 0.3s ease;">
                <div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(79, 70, 229, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1.25rem;">
                        <i class="ri-code-box-line"></i>
                    </div>
                    <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-color);">Gii Generator</h3>
                    <p class="text-muted" style="font-size: 0.875rem; line-height: 1.5; margin-bottom: 1.5rem;">
                        Buat modul CRUD (ActiveRecord, Controller, Views, Routes & RBAC) secara instan berdasarkan skema database Anda.
                    </p>
                </div>
                <a href="<?= $urlGenerator->generate('gii/index') ?>" class="btn btn-primary w-100" style="width: 100%; justify-content: center; box-sizing: border-box;">
                    Buka Generator <i class="ri-arrow-right-line" style="margin-left: 0.25rem;"></i>
                </a>
            </div>
        </div>

        <!-- Card 2: RBAC Management -->
        <div class="system-card-wrapper">
            <div class="card hover-glow" style="border: 1px solid rgba(0,0,0,0.05); transition: all 0.3s ease;">
                <div>
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1.25rem;">
                        <i class="ri-shield-user-line"></i>
                    </div>
                    <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-color);">Manajemen RBAC</h3>
                    <p class="text-muted" style="font-size: 0.875rem; line-height: 1.5; margin-bottom: 1.5rem;">
                        Kelola hak akses pengguna, peran (roles), izin (permissions), dan pemetaan rute secara dinamis dan aman.
                    </p>
                </div>
                <a href="<?= $urlGenerator->generate('users/index') ?>" class="btn btn-success w-100" style="width: 100%; justify-content: center; background: #10b981; border-color: #10b981; color: #fff; box-sizing: border-box;">
                    Kelola Pengguna <i class="ri-arrow-right-line" style="margin-left: 0.25rem;"></i>
                </a>
            </div>
        </div>
    </div>
</div>
