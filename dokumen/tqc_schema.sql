-- SQL Schema for TQC KMI Feature Migration to Yii3

SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables in reverse dependency order
DROP TABLE IF EXISTS `entitas_program_kanban_log`;
DROP TABLE IF EXISTS `entitas_program_kanban_task`;
DROP TABLE IF EXISTS `entitas_program_kanban_column`;
DROP TABLE IF EXISTS `entitas_program_kendala`;
DROP TABLE IF EXISTS `entitas_program_notulensi`;
DROP TABLE IF EXISTS `entitas_program_dokumentasi`;
DROP TABLE IF EXISTS `entitas_program`;
DROP TABLE IF EXISTS `entitas_anggota`;
DROP TABLE IF EXISTS `entitas`;
DROP TABLE IF EXISTS `modul`;

-- --------------------------------------------------------
-- Table structure for table `modul`
--
CREATE TABLE `modul` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) DEFAULT NULL,
  `tipe` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping default modules
INSERT INTO `modul` (`id`, `nama`, `tipe`, `created_at`, `updated_at`) VALUES
(1, 'fungsionaris', 'internal', NOW(), NOW()),
(2, 'kepanitiaan', 'internal', NOW(), NOW()),
(3, 'empowering', 'internal', NOW(), NOW());

-- --------------------------------------------------------
-- Table structure for table `entitas`
--
CREATE TABLE `entitas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modul_id` int DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-entitas-modul_id` (`modul_id`),
  CONSTRAINT `fk-entitas-modul_id` FOREIGN KEY (`modul_id`) REFERENCES `modul` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `entitas_anggota`
--
CREATE TABLE `entitas_anggota` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entitas_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `nama_anggota` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-entitas_anggota-entitas_id` (`entitas_id`),
  CONSTRAINT `fk-entitas_anggota-entitas_id` FOREIGN KEY (`entitas_id`) REFERENCES `entitas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `entitas_program`
--
CREATE TABLE `entitas_program` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entitas_id` int NOT NULL,
  `nama_program` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `periode` varchar(50) DEFAULT NULL,
  `penanggung_jawab_id` int DEFAULT NULL,
  `tupoksi` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-entitas_program-entitas_id` (`entitas_id`),
  KEY `fk-entitas_program-penanggung_jawab_id` (`penanggung_jawab_id`),
  CONSTRAINT `fk-entitas_program-entitas_id` FOREIGN KEY (`entitas_id`) REFERENCES `entitas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk-entitas_program-penanggung_jawab_id` FOREIGN KEY (`penanggung_jawab_id`) REFERENCES `entitas_anggota` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `entitas_program_kanban_column`
--
CREATE TABLE `entitas_program_kanban_column` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entitas_id` int NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int NOT NULL DEFAULT '1',
  `progress` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-entitas_program_kanban_column-entitas_id` (`entitas_id`),
  CONSTRAINT `fk-entitas_program_kanban_column-entitas_id` FOREIGN KEY (`entitas_id`) REFERENCES `entitas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `entitas_program_kanban_task`
--
CREATE TABLE `entitas_program_kanban_task` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `kanban_column_id` int DEFAULT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `assigned_to` int DEFAULT NULL,
  `progress` int DEFAULT '0',
  `deadline` date DEFAULT NULL,
  `urutan` int DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-entitas_program_kanban_task-program_id` (`program_id`),
  KEY `fk-entitas_program_kanban_task-kanban_column_id` (`kanban_column_id`),
  KEY `fk-entitas_program_kanban_task-assigned_to` (`assigned_to`),
  CONSTRAINT `fk-entitas_program_kanban_task-assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `entitas_anggota` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk-entitas_program_kanban_task-kanban_column_id` FOREIGN KEY (`kanban_column_id`) REFERENCES `entitas_program_kanban_column` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk-entitas_program_kanban_task-program_id` FOREIGN KEY (`program_id`) REFERENCES `entitas_program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `entitas_program_kanban_log`
--
CREATE TABLE `entitas_program_kanban_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `progress_sebelumnya` int DEFAULT NULL,
  `progress_baru` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-entitas_program_kanban_log-task_id` (`task_id`),
  CONSTRAINT `fk-entitas_program_kanban_log-task_id` FOREIGN KEY (`task_id`) REFERENCES `entitas_program_kanban_task` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `entitas_program_kendala`
--
CREATE TABLE `entitas_program_kendala` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `jenis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'terbuka',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-entitas_program_kendala-program_id` (`program_id`),
  CONSTRAINT `fk-entitas_program_kendala-program_id` FOREIGN KEY (`program_id`) REFERENCES `entitas_program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `entitas_program_notulensi`
--
CREATE TABLE `entitas_program_notulensi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi` text COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-entitas_program_notulensi-program_id` (`program_id`),
  CONSTRAINT `fk-entitas_program_notulensi-program_id` FOREIGN KEY (`program_id`) REFERENCES `entitas_program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `entitas_program_dokumentasi`
--
CREATE TABLE `entitas_program_dokumentasi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-entitas_program_dokumentasi-program_id` (`program_id`),
  CONSTRAINT `fk-entitas_program_dokumentasi-program_id` FOREIGN KEY (`program_id`) REFERENCES `entitas_program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
