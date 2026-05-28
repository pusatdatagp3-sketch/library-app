-- ============================================================
-- Migration: guru.konsulat (varchar) → guru.konsulat_id (FK)
-- Jalankan script ini di database teqic_yii3
-- ============================================================

-- 1. Hapus kolom lama konsulat (varchar)
ALTER TABLE `guru` DROP COLUMN `konsulat`;

-- 2. Tambah kolom konsulat_id (FK nullable)
ALTER TABLE `guru` ADD COLUMN `konsulat_id` INT DEFAULT NULL AFTER `daerah`;

-- 3. Tambah index dan foreign key ke tabel konsulat
ALTER TABLE `guru`
    ADD KEY `fk_guru_konsulat` (`konsulat_id`),
    ADD CONSTRAINT `fk_guru_konsulat`
        FOREIGN KEY (`konsulat_id`) REFERENCES `konsulat` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE;
