-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 21, 2026 at 05:42 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `teqic`
--

-- --------------------------------------------------------

--
-- Table structure for table `anggota_entitas`
--

CREATE TABLE `anggota_entitas` (
  `id` int NOT NULL,
  `entitas_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `nama_anggota` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `anggota_entitas`
--

INSERT INTO `anggota_entitas` (`id`, `entitas_id`, `user_id`, `nama_anggota`, `jabatan`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'hamid', 'ketua', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `auth_assignment`
--

CREATE TABLE `auth_assignment` (
  `item_name` varchar(64) COLLATE utf8mb3_unicode_ci NOT NULL,
  `user_id` varchar(64) COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `auth_assignment`
--

INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES
('admin', '1', 1777214778);

-- --------------------------------------------------------

--
-- Table structure for table `auth_item`
--

CREATE TABLE `auth_item` (
  `name` varchar(64) COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` smallint NOT NULL,
  `description` text COLLATE utf8mb3_unicode_ci,
  `rule_name` varchar(64) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `data` blob,
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `auth_item`
--

INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`) VALUES
('/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/assignment/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/assignment/assign', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/assignment/index', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/assignment/revoke', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/assignment/view', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/default/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/default/index', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/menu/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/menu/create', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/menu/delete', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/menu/index', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/menu/update', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/menu/view', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/permission/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/permission/assign', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/permission/create', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/permission/delete', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/permission/get-users', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/permission/index', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/permission/remove', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/permission/update', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/permission/view', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/role/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/role/assign', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/role/create', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/role/delete', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/role/get-users', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/role/index', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/role/remove', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/role/update', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/role/view', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/route/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/route/assign', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/route/create', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/route/index', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/route/refresh', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/route/remove', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/rule/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/rule/create', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/rule/delete', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/rule/index', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/rule/update', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/rule/view', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/user/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/user/activate', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/user/change-password', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/user/delete', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/user/index', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/user/login', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/user/logout', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/user/request-password-reset', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/user/reset-password', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/user/signup', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/admin/user/view', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/debug/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/debug/default/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/debug/default/db-explain', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/debug/default/download-mail', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/debug/default/index', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/debug/default/toolbar', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/debug/default/view', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/debug/user/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/debug/user/reset-identity', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/debug/user/set-identity', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/entitas/*', 2, NULL, NULL, NULL, 1777214579, 1777214579),
('/entitas/create', 2, NULL, NULL, NULL, 1777214579, 1777214579),
('/entitas/delete', 2, NULL, NULL, NULL, 1777214579, 1777214579),
('/entitas/index', 2, NULL, NULL, NULL, 1777214579, 1777214579),
('/entitas/update', 2, NULL, NULL, NULL, 1777214579, 1777214579),
('/entitas/view', 2, NULL, NULL, NULL, 1777214579, 1777214579),
('/gii/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/gii/default/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/gii/default/action', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/gii/default/diff', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/gii/default/index', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/gii/default/preview', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/gii/default/view', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/program/*', 2, NULL, NULL, NULL, 1777214579, 1777214579),
('/program/create', 2, NULL, NULL, NULL, 1777214579, 1777214579),
('/program/delete', 2, NULL, NULL, NULL, 1777214579, 1777214579),
('/program/index', 2, NULL, NULL, NULL, 1777214579, 1777214579),
('/program/update', 2, NULL, NULL, NULL, 1777214579, 1777214579),
('/program/view', 2, NULL, NULL, NULL, 1777214579, 1777214579),
('/site/*', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/site/error', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/site/index', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/site/login', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('/site/logout', 2, NULL, NULL, NULL, 1776734010, 1776734010),
('admin', 1, 'admin', NULL, NULL, 1776734118, 1776734118);

-- --------------------------------------------------------

--
-- Table structure for table `auth_item_child`
--

CREATE TABLE `auth_item_child` (
  `parent` varchar(64) COLLATE utf8mb3_unicode_ci NOT NULL,
  `child` varchar(64) COLLATE utf8mb3_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `auth_item_child`
--

INSERT INTO `auth_item_child` (`parent`, `child`) VALUES
('admin', '/*'),
('admin', '/admin/*'),
('admin', '/admin/assignment/*'),
('admin', '/admin/assignment/assign'),
('admin', '/admin/assignment/index'),
('admin', '/admin/assignment/revoke'),
('admin', '/admin/assignment/view'),
('admin', '/admin/default/*'),
('admin', '/admin/default/index'),
('admin', '/admin/menu/*'),
('admin', '/admin/menu/create'),
('admin', '/admin/menu/delete'),
('admin', '/admin/menu/index'),
('admin', '/admin/menu/update'),
('admin', '/admin/menu/view'),
('admin', '/admin/permission/*'),
('admin', '/admin/permission/assign'),
('admin', '/admin/permission/create'),
('admin', '/admin/permission/delete'),
('admin', '/admin/permission/get-users'),
('admin', '/admin/permission/index'),
('admin', '/admin/permission/remove'),
('admin', '/admin/permission/update'),
('admin', '/admin/permission/view'),
('admin', '/admin/role/*'),
('admin', '/admin/role/assign'),
('admin', '/admin/role/create'),
('admin', '/admin/role/delete'),
('admin', '/admin/role/get-users'),
('admin', '/admin/role/index'),
('admin', '/admin/role/remove'),
('admin', '/admin/role/update'),
('admin', '/admin/role/view'),
('admin', '/admin/route/*'),
('admin', '/admin/route/assign'),
('admin', '/admin/route/create'),
('admin', '/admin/route/index'),
('admin', '/admin/route/refresh'),
('admin', '/admin/route/remove'),
('admin', '/admin/rule/*'),
('admin', '/admin/rule/create'),
('admin', '/admin/rule/delete'),
('admin', '/admin/rule/index'),
('admin', '/admin/rule/update'),
('admin', '/admin/rule/view'),
('admin', '/admin/user/*'),
('admin', '/admin/user/activate'),
('admin', '/admin/user/change-password'),
('admin', '/admin/user/delete'),
('admin', '/admin/user/index'),
('admin', '/admin/user/login'),
('admin', '/admin/user/logout'),
('admin', '/admin/user/request-password-reset'),
('admin', '/admin/user/reset-password'),
('admin', '/admin/user/signup'),
('admin', '/admin/user/view'),
('admin', '/debug/*'),
('admin', '/debug/default/*'),
('admin', '/debug/default/db-explain'),
('admin', '/debug/default/download-mail'),
('admin', '/debug/default/index'),
('admin', '/debug/default/toolbar'),
('admin', '/debug/default/view'),
('admin', '/debug/user/*'),
('admin', '/debug/user/reset-identity'),
('admin', '/debug/user/set-identity'),
('admin', '/entitas/*'),
('admin', '/entitas/create'),
('admin', '/entitas/delete'),
('admin', '/entitas/index'),
('admin', '/entitas/update'),
('admin', '/entitas/view'),
('admin', '/gii/*'),
('admin', '/gii/default/*'),
('admin', '/gii/default/action'),
('admin', '/gii/default/diff'),
('admin', '/gii/default/index'),
('admin', '/gii/default/preview'),
('admin', '/gii/default/view'),
('admin', '/program/*'),
('admin', '/program/create'),
('admin', '/program/delete'),
('admin', '/program/index'),
('admin', '/program/update'),
('admin', '/program/view'),
('admin', '/site/*'),
('admin', '/site/error'),
('admin', '/site/index'),
('admin', '/site/login'),
('admin', '/site/logout');

-- --------------------------------------------------------

--
-- Table structure for table `auth_rule`
--

CREATE TABLE `auth_rule` (
  `name` varchar(64) COLLATE utf8mb3_unicode_ci NOT NULL,
  `data` blob,
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dokumentasi`
--

CREATE TABLE `dokumentasi` (
  `id` int NOT NULL,
  `program_id` int NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entitas`
--

CREATE TABLE `entitas` (
  `id` int NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modul_id` bigint UNSIGNED DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `entitas`
--

INSERT INTO `entitas` (`id`, `nama`, `modul_id`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 'Kurikulum KMI', 1, 'Ini bagian manhaj dirosy', NULL, NULL),
(2, 'Fathul Kutub Kelas 6', 2, 'akaka', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kanban_columns`
--

CREATE TABLE `kanban_columns` (
  `id` int NOT NULL,
  `entitas_id` int NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kanban_columns`
--

INSERT INTO `kanban_columns` (`id`, `entitas_id`, `nama`, `urutan`, `created_at`, `updated_at`) VALUES
(1, 1, 'To Do', 1, NULL, NULL),
(2, 1, 'On Progress', 2, NULL, NULL),
(3, 1, 'Done', 3, NULL, NULL),
(4, 1, 'Diulangi', 4, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kendala_program`
--

CREATE TABLE `kendala_program` (
  `id` int NOT NULL,
  `program_id` int NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `jenis` enum('terbuka','tertutup') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'terbuka',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migration`
--

CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `migration`
--

INSERT INTO `migration` (`version`, `apply_time`) VALUES
('m000000_000000_base', 1776344618),
('m130524_201442_init', 1776344621),
('m140506_102106_rbac_init', 1776732902),
('m170907_052038_rbac_add_index_on_auth_assignment_user_id', 1776732902),
('m180523_151638_rbac_updates_indexes_without_prefix', 1776732903),
('m190124_110200_add_verification_token_column_to_user_table', 1776344621),
('m200409_110543_rbac_update_mssql_trigger', 1776732903),
('m260426_142549_create_tqc_kmi_tables', 1777213746),
('m260427_011729_change_entitas_modul_to_fk', 1777252693);

-- --------------------------------------------------------

--
-- Table structure for table `modul`
--

CREATE TABLE `modul` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `tipe` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `modul`
--

INSERT INTO `modul` (`id`, `nama`, `tipe`, `created_at`, `updated_at`) VALUES
(1, 'fungsionaris', 'internal', '2026-04-27 01:16:42', '2026-04-27 01:16:42'),
(2, 'kepanitiaan', 'internal', '2026-04-27 01:16:42', '2026-04-27 01:16:42'),
(3, 'empowering', 'internal', '2026-04-27 01:16:42', '2026-04-27 01:16:42');

-- --------------------------------------------------------

--
-- Table structure for table `nilai_akademik`
--

CREATE TABLE `nilai_akademik` (
  `id` bigint UNSIGNED NOT NULL,
  `santri_id` bigint UNSIGNED DEFAULT NULL,
  `mapel_id` bigint UNSIGNED DEFAULT NULL,
  `nilai` decimal(5,2) DEFAULT NULL,
  `semester` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notulensi`
--

CREATE TABLE `notulensi` (
  `id` int NOT NULL,
  `program_id` int NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi` text COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program`
--

CREATE TABLE `program` (
  `id` int NOT NULL,
  `entitas_id` int NOT NULL,
  `nama_program` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `periode` enum('mingguan','bulanan','semesteran','tahunan') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penanggung_jawab_id` int DEFAULT NULL,
  `tupoksi` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `program`
--

INSERT INTO `program` (`id`, `entitas_id`, `nama_program`, `periode`, `penanggung_jawab_id`, `tupoksi`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'jaja', 'mingguan', 1, 'ajdfj\r\n', 'active', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int NOT NULL,
  `program_id` int NOT NULL,
  `kanban_column_id` int DEFAULT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `assigned_to` int DEFAULT NULL,
  `progress` int DEFAULT '0',
  `deadline` date DEFAULT NULL,
  `urutan` int DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `program_id`, `kanban_column_id`, `judul`, `deskripsi`, `assigned_to`, `progress`, `deadline`, `urutan`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'haha', NULL, NULL, 0, NULL, 1, NULL, '2026-04-27 10:16:25');

-- --------------------------------------------------------

--
-- Table structure for table `task_progress_logs`
--

CREATE TABLE `task_progress_logs` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `progress_sebelumnya` int DEFAULT NULL,
  `progress_baru` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_progress_logs`
--

INSERT INTO `task_progress_logs` (`id`, `task_id`, `keterangan`, `progress_sebelumnya`, `progress_baru`, `created_at`) VALUES
(1, 1, 'Dipindahkan ke Kolom Baru', 0, 50, NULL),
(2, 1, 'Dipindahkan ke Kolom Baru', 50, 100, NULL),
(3, 1, 'Dipindahkan ke Kolom Baru', 100, 50, NULL),
(4, 1, 'Dipindahkan ke Kolom Baru', 50, 0, NULL),
(5, 1, 'Dipindahkan ke Kolom Baru', 0, 50, NULL),
(6, 1, 'Dipindahkan ke Kolom Baru', 50, 100, NULL),
(7, 1, 'Dipindahkan ke Kolom Baru', 100, 0, NULL),
(8, 1, 'Dipindahkan ke Kolom Baru', 0, 100, NULL),
(9, 1, 'Dipindahkan ke Kolom Baru', 100, 50, NULL),
(10, 1, 'Dipindahkan ke Kolom Baru', 50, 100, NULL),
(11, 1, 'Dipindahkan ke Kolom Baru', 100, 0, NULL),
(12, 1, 'Dipindahkan ke Kolom Baru', 0, 0, NULL),
(13, 1, 'Dipindahkan ke Kolom Baru', 0, 50, NULL),
(14, 1, 'Dipindahkan ke Kolom Baru', 50, 100, NULL),
(15, 1, 'Dipindahkan ke Kolom Baru', 100, 0, NULL),
(16, 1, 'Dipindahkan ke Kolom Baru', 0, 100, NULL),
(17, 1, 'Dipindahkan ke Kolom Baru', 100, 0, NULL),
(18, 1, 'Dipindahkan ke Kolom Baru', 0, 50, NULL),
(19, 1, 'Dipindahkan ke Kolom Baru', 50, 100, NULL),
(20, 1, 'Dipindahkan ke Kolom Baru', 100, 0, NULL),
(21, 1, 'Dipindahkan ke Kolom Baru', 0, 0, NULL),
(22, 1, 'Dipindahkan ke Kolom Baru', 0, 50, NULL),
(23, 1, 'Dipindahkan ke Kolom Baru', 50, 0, NULL),
(24, 1, 'Dipindahkan ke Kolom Baru', 0, 100, NULL),
(25, 1, 'Dipindahkan ke Kolom Baru', 100, 50, NULL),
(26, 1, 'Dipindahkan ke Kolom Baru', 50, 0, NULL),
(27, 1, 'Dipindahkan ke Kolom Baru', 0, 50, NULL),
(28, 1, 'Dipindahkan ke Kolom Baru', 50, 100, NULL),
(29, 1, 'Dipindahkan ke Kolom Baru', 100, 50, NULL),
(30, 1, 'Dipindahkan ke Kolom Baru', 50, 100, NULL),
(31, 1, 'Dipindahkan ke Kolom Baru', 100, 50, NULL),
(32, 1, 'Dipindahkan ke Kolom Baru', 50, 0, NULL),
(33, 1, 'Dipindahkan ke Kolom Baru', 0, 50, NULL),
(34, 1, 'Dipindahkan ke Kolom Baru', 50, 100, NULL),
(35, 1, 'Dipindahkan ke Kolom Baru', 100, 0, NULL),
(36, 1, 'Dipindahkan ke Kolom Baru', 0, 50, NULL),
(37, 1, 'Dipindahkan ke Kolom Baru', 50, 100, NULL),
(38, 1, 'Dipindahkan ke Kolom Baru', 100, 0, NULL),
(39, 1, 'Dipindahkan ke Kolom Baru', 0, 100, NULL),
(40, 1, 'Dipindahkan ke Kolom Baru', 100, 50, NULL),
(41, 1, 'Dipindahkan ke Kolom Baru', 50, 0, NULL),
(42, 1, 'Dipindahkan ke Kolom Baru', 0, 50, NULL),
(43, 1, 'Dipindahkan ke Kolom Baru', 50, 100, NULL),
(44, 1, 'Dipindahkan ke Kolom Baru', 100, 50, NULL),
(45, 1, 'Dipindahkan ke Kolom Baru', 50, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `username` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `auth_key` varchar(32) COLLATE utf8mb3_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `status` smallint NOT NULL DEFAULT '10',
  `created_at` int NOT NULL,
  `updated_at` int NOT NULL,
  `verification_token` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `auth_key`, `password_hash`, `password_reset_token`, `email`, `status`, `created_at`, `updated_at`, `verification_token`) VALUES
(1, 'admin', '', '$2y$10$WnP/2aKkCWg6tWCFcA9lc.lNA9va/12lNmFXmprSehdu3DJEgJ91a', NULL, 'admin@gmail.com', 10, 1673670708, 1761032434, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anggota_entitas`
--
ALTER TABLE `anggota_entitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-anggota_entitas-entitas_id` (`entitas_id`);

--
-- Indexes for table `auth_assignment`
--
ALTER TABLE `auth_assignment`
  ADD PRIMARY KEY (`item_name`,`user_id`),
  ADD KEY `idx-auth_assignment-user_id` (`user_id`);

--
-- Indexes for table `auth_item`
--
ALTER TABLE `auth_item`
  ADD PRIMARY KEY (`name`),
  ADD KEY `rule_name` (`rule_name`),
  ADD KEY `idx-auth_item-type` (`type`);

--
-- Indexes for table `auth_item_child`
--
ALTER TABLE `auth_item_child`
  ADD PRIMARY KEY (`parent`,`child`),
  ADD KEY `child` (`child`);

--
-- Indexes for table `auth_rule`
--
ALTER TABLE `auth_rule`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `dokumentasi`
--
ALTER TABLE `dokumentasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-dokumentasi-program_id` (`program_id`);

--
-- Indexes for table `entitas`
--
ALTER TABLE `entitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-entitas-modul_id` (`modul_id`);

--
-- Indexes for table `kanban_columns`
--
ALTER TABLE `kanban_columns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-kanban_columns-entitas_id` (`entitas_id`);

--
-- Indexes for table `kendala_program`
--
ALTER TABLE `kendala_program`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-kendala_program-program_id` (`program_id`);

--
-- Indexes for table `migration`
--
ALTER TABLE `migration`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `modul`
--
ALTER TABLE `modul`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nilai_akademik`
--
ALTER TABLE `nilai_akademik`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notulensi`
--
ALTER TABLE `notulensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-notulensi-program_id` (`program_id`);

--
-- Indexes for table `program`
--
ALTER TABLE `program`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-program-entitas_id` (`entitas_id`),
  ADD KEY `fk-program-penanggung_jawab_id` (`penanggung_jawab_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-tasks-program_id` (`program_id`),
  ADD KEY `fk-tasks-kanban_column_id` (`kanban_column_id`),
  ADD KEY `fk-tasks-assigned_to` (`assigned_to`);

--
-- Indexes for table `task_progress_logs`
--
ALTER TABLE `task_progress_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-task_progress_logs-task_id` (`task_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `password_reset_token` (`password_reset_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anggota_entitas`
--
ALTER TABLE `anggota_entitas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dokumentasi`
--
ALTER TABLE `dokumentasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entitas`
--
ALTER TABLE `entitas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kanban_columns`
--
ALTER TABLE `kanban_columns`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kendala_program`
--
ALTER TABLE `kendala_program`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `modul`
--
ALTER TABLE `modul`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `nilai_akademik`
--
ALTER TABLE `nilai_akademik`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notulensi`
--
ALTER TABLE `notulensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program`
--
ALTER TABLE `program`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `task_progress_logs`
--
ALTER TABLE `task_progress_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anggota_entitas`
--
ALTER TABLE `anggota_entitas`
  ADD CONSTRAINT `fk-anggota_entitas-entitas_id` FOREIGN KEY (`entitas_id`) REFERENCES `entitas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `auth_assignment`
--
ALTER TABLE `auth_assignment`
  ADD CONSTRAINT `auth_assignment_ibfk_1` FOREIGN KEY (`item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `auth_item`
--
ALTER TABLE `auth_item`
  ADD CONSTRAINT `auth_item_ibfk_1` FOREIGN KEY (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `auth_item_child`
--
ALTER TABLE `auth_item_child`
  ADD CONSTRAINT `auth_item_child_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `auth_item_child_ibfk_2` FOREIGN KEY (`child`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dokumentasi`
--
ALTER TABLE `dokumentasi`
  ADD CONSTRAINT `fk-dokumentasi-program_id` FOREIGN KEY (`program_id`) REFERENCES `program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entitas`
--
ALTER TABLE `entitas`
  ADD CONSTRAINT `fk-entitas-modul_id` FOREIGN KEY (`modul_id`) REFERENCES `modul` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kanban_columns`
--
ALTER TABLE `kanban_columns`
  ADD CONSTRAINT `fk-kanban_columns-entitas_id` FOREIGN KEY (`entitas_id`) REFERENCES `entitas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kendala_program`
--
ALTER TABLE `kendala_program`
  ADD CONSTRAINT `fk-kendala_program-program_id` FOREIGN KEY (`program_id`) REFERENCES `program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notulensi`
--
ALTER TABLE `notulensi`
  ADD CONSTRAINT `fk-notulensi-program_id` FOREIGN KEY (`program_id`) REFERENCES `program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `program`
--
ALTER TABLE `program`
  ADD CONSTRAINT `fk-program-entitas_id` FOREIGN KEY (`entitas_id`) REFERENCES `entitas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk-program-penanggung_jawab_id` FOREIGN KEY (`penanggung_jawab_id`) REFERENCES `anggota_entitas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk-tasks-assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `anggota_entitas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk-tasks-kanban_column_id` FOREIGN KEY (`kanban_column_id`) REFERENCES `kanban_columns` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk-tasks-program_id` FOREIGN KEY (`program_id`) REFERENCES `program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task_progress_logs`
--
ALTER TABLE `task_progress_logs`
  ADD CONSTRAINT `fk-task_progress_logs-task_id` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
