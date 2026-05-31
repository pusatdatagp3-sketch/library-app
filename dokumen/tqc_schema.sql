-- SQL Schema for TQC KMI Feature Migration to Yii3

SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables in reverse dependency order
DROP TABLE IF EXISTS `task_progress_logs`;
DROP TABLE IF EXISTS `tasks`;
DROP TABLE IF EXISTS `kanban_columns`;
DROP TABLE IF EXISTS `kendala_program`;
DROP TABLE IF EXISTS `notulensi`;
DROP TABLE IF EXISTS `dokumentasi`;
DROP TABLE IF EXISTS `program`;
DROP TABLE IF EXISTS `anggota_entitas`;
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
-- Table structure for table `anggota_entitas`
--
CREATE TABLE `anggota_entitas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entitas_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `nama_anggota` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-anggota_entitas-entitas_id` (`entitas_id`),
  CONSTRAINT `fk-anggota_entitas-entitas_id` FOREIGN KEY (`entitas_id`) REFERENCES `entitas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `program`
--
CREATE TABLE `program` (
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
  KEY `fk-program-entitas_id` (`entitas_id`),
  KEY `fk-program-penanggung_jawab_id` (`penanggung_jawab_id`),
  CONSTRAINT `fk-program-entitas_id` FOREIGN KEY (`entitas_id`) REFERENCES `entitas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk-program-penanggung_jawab_id` FOREIGN KEY (`penanggung_jawab_id`) REFERENCES `anggota_entitas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `kanban_columns`
--
CREATE TABLE `kanban_columns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entitas_id` int NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-kanban_columns-entitas_id` (`entitas_id`),
  CONSTRAINT `fk-kanban_columns-entitas_id` FOREIGN KEY (`entitas_id`) REFERENCES `entitas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `tasks`
--
CREATE TABLE `tasks` (
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
  KEY `fk-tasks-program_id` (`program_id`),
  KEY `fk-tasks-kanban_column_id` (`kanban_column_id`),
  KEY `fk-tasks-assigned_to` (`assigned_to`),
  CONSTRAINT `fk-tasks-assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `anggota_entitas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk-tasks-kanban_column_id` FOREIGN KEY (`kanban_column_id`) REFERENCES `kanban_columns` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk-tasks-program_id` FOREIGN KEY (`program_id`) REFERENCES `program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `task_progress_logs`
--
CREATE TABLE `task_progress_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `progress_sebelumnya` int DEFAULT NULL,
  `progress_baru` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-task_progress_logs-task_id` (`task_id`),
  CONSTRAINT `fk-task_progress_logs-task_id` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `kendala_program`
--
CREATE TABLE `kendala_program` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `jenis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'terbuka',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-kendala_program-program_id` (`program_id`),
  CONSTRAINT `fk-kendala_program-program_id` FOREIGN KEY (`program_id`) REFERENCES `program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `notulensi`
--
CREATE TABLE `notulensi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi` text COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-notulensi-program_id` (`program_id`),
  CONSTRAINT `fk-notulensi-program_id` FOREIGN KEY (`program_id`) REFERENCES `program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `dokumentasi`
--
CREATE TABLE `dokumentasi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-dokumentasi-program_id` (`program_id`),
  CONSTRAINT `fk-dokumentasi-program_id` FOREIGN KEY (`program_id`) REFERENCES `program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
