<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var App\Web\Program\Model\Program $model
 * @var App\Web\Entitas\Model\Entitas[] $entitasList
 * @var App\Web\Entitas\Model\AnggotaEntitas[] $members
 * @var string $backUrl
 */

$this->setTitle('Buat Program Kerja Baru');
?>

<div class="crud-container max-w-2xl">
    <div class="mb-4">
        <a href="<?= $backUrl ?>" class="btn btn-sm btn-secondary">
            <i class="ri-arrow-left-line"></i> Kembali
        </a>
    </div>

    <div class="crud-header">
        <div>
            <h1 class="crud-title"><i class="ri-calendar-event-line text-primary"></i> Buat Program Kerja</h1>
            <p class="crud-subtitle">Daun program kerja baru dan tentukan penanggung jawabnya.</p>
        </div>
    </div>

    <div class="card">
        <form method="POST">
            <input type="hidden" name="_csrf" value="<?= Html::encode($this->getParameter('csrf')) ?>">

            <div class="form-group mb-4">
                <label class="form-label" for="entitas_id">Entitas / Organisasi</label>
                <select id="entitas_id" name="entitas_id" class="form-control <?= $model->entitasId !== null ? 'form-control-disabled' : '' ?>" required <?= $model->entitasId !== null ? 'readonly' : '' ?>>
                    <?php foreach ($entitasList as $ent): ?>
                        <option value="<?= $ent->id ?>" <?= $model->entitasId === $ent->id ? 'selected' : '' ?>>
                            <?= Html::encode($ent->nama) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mb-4">
                <label class="form-label" for="nama_program">Nama Program Kerja</label>
                <input type="text"
                       id="nama_program"
                       name="nama_program"
                       value="<?= Html::encode($model->namaProgram) ?>"
                       class="form-control <?= isset($model->errors['namaProgram']) ? 'is-invalid' : '' ?>"
                       placeholder="Contoh: Laporan Pertanggungjawaban Bulanan"
                       required>
                <?php if (isset($model->errors['namaProgram'])): ?>
                    <div class="invalid-feedback"><?= Html::encode($model->errors['namaProgram']) ?></div>
                <?php endif; ?>
            </div>

            <div class="grid grid-2col gap-3 mb-4">
                <div class="form-group">
                    <label class="form-label" for="periode">Periode Evaluasi</label>
                    <select id="periode" name="periode" class="form-control" required>
                        <option value="mingguan"   <?= $model->periode === 'mingguan'   ? 'selected' : '' ?>>Mingguan</option>
                        <option value="bulanan"    <?= $model->periode === 'bulanan'    ? 'selected' : '' ?>>Bulanan</option>
                        <option value="semesteran" <?= $model->periode === 'semesteran' ? 'selected' : '' ?>>Semesteran</option>
                        <option value="tahunan"    <?= $model->periode === 'tahunan'    ? 'selected' : '' ?>>Tahunan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="penanggung_jawab_id">Penanggung Jawab (PIC)</label>
                    <select id="penanggung_jawab_id" name="penanggung_jawab_id" class="form-control">
                        <option value="">-- Pilih Penanggung Jawab --</option>
                        <?php foreach ($members as $mb): ?>
                            <option value="<?= $mb->id ?>" <?= $model->penanggungJawabId === $mb->id ? 'selected' : '' ?>>
                                <?= Html::encode($mb->namaAnggota) ?> (<?= Html::encode($mb->jabatan) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group mb-4">
                <label class="form-label" for="tupoksi">Tugas Pokok &amp; Fungsi (Tupoksi)</label>
                <textarea id="tupoksi"
                          name="tupoksi"
                          rows="4"
                          class="form-control"
                          placeholder="Tuliskan detail tugas pokok dan fungsi program kerja ini..."><?= Html::encode($model->tupoksi ?? '') ?></textarea>
            </div>

            <div class="form-group mb-4">
                <label class="form-label" for="status">Status Program</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="active"   <?= $model->status === 'active'   ? 'selected' : '' ?>>Aktif</option>
                    <option value="inactive" <?= $model->status === 'inactive' ? 'selected' : '' ?>>Non-aktif</option>
                </select>
            </div>

            <div class="form-actions mt-4">
                <a href="<?= $backUrl ?>" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Program</button>
            </div>
        </form>
    </div>
</div>
