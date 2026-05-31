<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var App\Web\Entitas\Model\Entitas $model
 * @var App\Web\Entitas\Model\AnggotaEntitas[] $members
 * @var App\Web\Program\Model\Program[] $programs
 * @var App\Web\Auth\Model\User[] $users
 * @var UrlGeneratorInterface $urlGenerator
 * @var \App\Web\Auth\Model\UserSession $userSession
 * @var string $prefix
 * @var string $modulTitle
 * @var string $indexRoute
 * @var string|null $successMsg
 * @var array $errorMsgs
 */

$this->setTitle("Detail Entitas - {$model->nama}");
$addMemberRoute = "{$prefix}/add-member";
$deleteMemberRoute = "{$prefix}/delete-member";
?>

<div class="crud-container">
    <!-- Breadcrumbs & Navigation -->
    <div class="mb-4">
        <a href="<?= $urlGenerator->generate($indexRoute) ?>" class="btn btn-sm btn-secondary" style="display: inline-flex; align-items: center; gap: 0.25rem;">
            <i class="ri-arrow-left-line"></i> Kembali ke Daftar <?= Html::encode($modulTitle) ?>
        </a>
    </div>

    <!-- Header Info Card -->
    <div class="card mb-4 overflow-hidden" style="position: relative; border-left: 5px solid var(--primary);">
        <div class="d-flex justify-content-between align-items-start" style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span class="badge badge-primary-lightmb-2" style="background: var(--primary-light); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 50px; font-weight: 600; font-size: 0.75rem; margin-bottom: 0.5rem; display: inline-block;">
                    <?= Html::encode($modulTitle) ?>
                </span>
                <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--text-color);">
                    <?= Html::encode($model->nama) ?>
                </h1>
                <p class="text-muted" style="font-size: 1rem; margin-bottom: 0; max-width: 800px;">
                    <?= Html::encode($model->deskripsi ?? 'Tidak ada deskripsi.') ?>
                </p>
            </div>
            
            <?php if ($userSession->hasPermission("update_{$prefix}")): ?>
                <a href="<?= $urlGenerator->generate("{$prefix}/update", ['id' => $model->id]) ?>" class="btn btn-outline-primary" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                    <i class="ri-pencil-line"></i> Edit Entitas
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($successMsg): ?>
        <div class="alert alert-success mb-4">
            <i class="ri-checkbox-circle-fill alert-icon"></i>
            <div><?= Html::encode($successMsg) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMsgs)): ?>
        <div class="alert alert-danger mb-4">
            <i class="ri-error-warning-fill alert-icon"></i>
            <div>
                <?php foreach ($errorMsgs as $errorMsg): ?>
                    <p><?= Html::encode($errorMsg) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Split Layout: Left Programs, Right Members -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
        
        <!-- SECTION 1: PROGRAM KERJA -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="card" style="padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.75rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                        <i class="ri-calendar-todo-line text-primary" style="font-size: 1.5rem;"></i> Program Kerja
                    </h2>
                    
                    <a href="<?= $urlGenerator->generate('program/create', ['entitas_id' => $model->id]) ?>" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                        <i class="ri-add-line"></i> Tambah Program
                    </a>
                </div>

                <?php if (empty($programs)): ?>
                    <div class="text-center py-5" style="padding: 3rem 0;">
                        <i class="ri-draft-line text-muted" style="font-size: 3rem; display: block; margin-bottom: 0.5rem;"></i>
                        <p class="text-muted">Belum ada program kerja yang ditambahkan ke entitas ini.</p>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($programs as $prog): ?>
                            <div class="hover-glow" style="border: 1px solid rgba(0,0,0,0.05); border-radius: 8px; padding: 1rem; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s;">
                                <div>
                                    <h4 style="font-size: 1rem; font-weight: 700; margin: 0 0 0.25rem 0;">
                                        <?= Html::encode($prog->namaProgram) ?>
                                    </h4>
                                    <div style="display: flex; gap: 0.75rem; align-items: center; font-size: 0.8rem; color: var(--text-muted);">
                                        <span style="display: inline-flex; align-items: center; gap: 0.2rem;">
                                            <i class="ri-time-line"></i> <?= Html::encode(ucfirst($prog->periode ?? 'tidak ditentukan')) ?>
                                        </span>
                                        <span style="height: 4px; width: 4px; background: #ccc; border-radius: 50%;"></span>
                                        <span style="display: inline-flex; align-items: center; gap: 0.2rem;">
                                            <i class="ri-user-star-line"></i> PIC: <?= Html::encode($prog->penanggungJawab?->namaAnggota ?? 'Belum ditentukan') ?>
                                        </span>
                                        <span style="height: 4px; width: 4px; background: #ccc; border-radius: 50%;"></span>
                                        <span>
                                            Status: <span class="badge badge-<?= $prog->status === 'active' ? 'success' : 'secondary' ?>" style="font-size: 0.7rem; padding: 0.1rem 0.4rem;"><?= Html::encode($prog->status) ?></span>
                                        </span>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <a href="<?= $urlGenerator->generate('kanban/board', ['program_id' => $prog->id]) ?>" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <i class="ri-kanban-view"></i> Papan Kanban
                                    </a>
                                    <a href="<?= $urlGenerator->generate('program/view', ['id' => $prog->id]) ?>" class="btn btn-sm btn-outline-secondary" title="Detail Program">
                                        <i class="ri-arrow-right-s-line"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECTION 2: ANGGOTA ENTITAS / KEPENGURUSAN -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Member List Card -->
            <div class="card" style="padding: 1.5rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.75rem;">
                    <i class="ri-group-line text-primary" style="font-size: 1.5rem;"></i> Kepengurusan / Anggota
                </h2>

                <?php if (empty($members)): ?>
                    <p class="text-muted text-center py-4">Belum ada anggota yang dialokasikan.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 400px; overflow-y: auto; padding-right: 0.25rem;">
                        <?php foreach ($members as $mb): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.02); border-radius: 6px; padding: 0.75rem; border: 1px solid rgba(0,0,0,0.02);">
                                <div>
                                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-color);"><?= Html::encode($mb->namaAnggota) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">
                                        <?= Html::encode($mb->jabatan ?? 'Anggota') ?>
                                    </div>
                                </div>
                                
                                <?php if ($userSession->hasPermission("update_{$prefix}")): ?>
                                    <form action="<?= $urlGenerator->generate($deleteMemberRoute, ['id' => $model->id, 'memberId' => $mb->id]) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin melepas anggota ini?');" style="margin:0;">
                                        <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                                        <button type="submit" class="btn btn-sm btn-icon text-danger" style="background:none; border:none; padding:0.25rem; cursor:pointer;" title="Hapus Anggota">
                                            <i class="ri-close-circle-line" style="font-size: 1.25rem;"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Add Member Form -->
                <?php if ($userSession->hasPermission("update_{$prefix}")): ?>
                    <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px dashed rgba(0,0,0,0.1);">
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 1rem;"><i class="ri-user-add-line"></i> Alokasikan Anggota Baru</h4>
                        
                        <form action="<?= $urlGenerator->generate($addMemberRoute, ['id' => $model->id]) ?>" method="POST">
                            <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">
                            
                            <div class="form-group mb-3">
                                <label class="form-label" style="font-size: 0.8rem;" for="user_id">Link Akun Pengguna (Opsional)</label>
                                <select id="user_id" name="user_id" class="form-control" style="font-size: 0.85rem; padding: 0.375rem 0.75rem;" onchange="document.getElementById('nama_anggota').value = this.options[this.selectedIndex].text;">
                                    <option value="">-- Pilih Akun Pengguna --</option>
                                    <?php foreach ($users as $usr): ?>
                                        <option value="<?= $usr->id ?>"><?= Html::encode($usr->username) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" style="font-size: 0.8rem;" for="nama_anggota">Nama Anggota</label>
                                <input type="text" id="nama_anggota" name="nama_anggota" class="form-control" style="font-size: 0.85rem; padding: 0.375rem 0.75rem;" placeholder="Nama Lengkap Anggota" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" style="font-size: 0.8rem;" for="jabatan">Jabatan</label>
                                <input type="text" id="jabatan" name="jabatan" class="form-control" style="font-size: 0.85rem; padding: 0.375rem 0.75rem;" placeholder="Contoh: Ketua, Anggota, Sekretaris" required>
                            </div>

                            <button type="submit" class="btn btn-sm btn-primary w-100" style="width: 100%; justify-content: center; padding: 0.5rem; margin-top: 0.5rem;">
                                <i class="ri-user-add-line"></i> Tambahkan ke Entitas
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
