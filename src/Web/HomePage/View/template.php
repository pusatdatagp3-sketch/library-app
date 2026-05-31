<?php

declare(strict_types=1);

use App\Shared\ApplicationParams;
use Yiisoft\View\WebView;
use Yiisoft\Html\Html;

/**
 * @var WebView $this
 * @var ApplicationParams $applicationParams
 * @var \Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 */

$this->setTitle($applicationParams->name);
$urlGenerator = $this->getParameter('urlGenerator');
?>

<div class="dashboard-container" style="max-width: 1000px; margin: 0 auto; padding: 2rem 0;">
    
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

    <!-- TQC Modules Grid -->
    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-color); display: flex; align-items: center; gap: 0.5rem;">
        <i class="ri-layout-grid-line text-primary"></i> Modul KMI (TQC)
    </h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
        <!-- Card 1: Fungsionaris -->
        <div class="card hover-glow" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%; border: 1px solid rgba(0,0,0,0.05); transition: all 0.3s ease;">
            <div>
                <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1.25rem;">
                    <i class="ri-group-3-line"></i>
                </div>
                <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-color);">Fungsionaris KMI</h3>
                <p class="text-muted" style="font-size: 0.875rem; line-height: 1.5; margin-bottom: 1.5rem;">
                    Kelola struktur kepengurusan fungsionaris utama, alokasi penanggung jawab bagian, dan tupoksi resmi.
                </p>
            </div>
            <a href="<?= $urlGenerator->generate('fungsionaris/index') ?>" class="btn btn-primary w-100" style="width: 100%; justify-content: center; box-sizing: border-box;">
                Buka Modul <i class="ri-arrow-right-line" style="margin-left: 0.25rem;"></i>
            </a>
        </div>

        <!-- Card 2: Kepanitiaan -->
        <div class="card hover-glow" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%; border: 1px solid rgba(0,0,0,0.05); transition: all 0.3s ease;">
            <div>
                <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1.25rem;">
                    <i class="ri-calendar-event-line"></i>
                </div>
                <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-color);">Kepanitiaan KMI</h3>
                <p class="text-muted" style="font-size: 0.875rem; line-height: 1.5; margin-bottom: 1.5rem;">
                    Organisasi kepanitiaan ad-hoc untuk program formal, event tahunan, dan kepanitiaan berkala KMI.
                </p>
            </div>
            <a href="<?= $urlGenerator->generate('kepanitiaan/index') ?>" class="btn btn-success w-100" style="width: 100%; justify-content: center; background: #10b981; border-color: #10b981; color: #fff; box-sizing: border-box;">
                Buka Modul <i class="ri-arrow-right-line" style="margin-left: 0.25rem;"></i>
            </a>
        </div>

        <!-- Card 3: Empowering -->
        <div class="card hover-glow" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%; border: 1px solid rgba(0,0,0,0.05); transition: all 0.3s ease;">
            <div>
                <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1.25rem;">
                    <i class="ri-sparkling-line"></i>
                </div>
                <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-color);">Empowering KMI</h3>
                <p class="text-muted" style="font-size: 0.875rem; line-height: 1.5; margin-bottom: 1.5rem;">
                    Program penguatan, pembinaan karakter pembina, dan peningkatan mutu tata kelola pengasuhan.
                </p>
            </div>
            <a href="<?= $urlGenerator->generate('empowering/index') ?>" class="btn btn-warning w-100" style="width: 100%; justify-content: center; background: #f59e0b; border-color: #f59e0b; color: #fff; box-sizing: border-box;">
                Buka Modul <i class="ri-arrow-right-line" style="margin-left: 0.25rem;"></i>
            </a>
        </div>
    </div>
</div>
