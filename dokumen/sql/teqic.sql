-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 04, 2026 at 12:19 AM
-- Server version: 10.6.22-MariaDB-0ubuntu0.22.04.1
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `c0teqic`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth_assignment`
--

CREATE TABLE `auth_assignment` (
  `item_name` varchar(64) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `created_at` int(11) DEFAULT NULL
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
  `name` varchar(64) NOT NULL,
  `type` smallint(6) NOT NULL,
  `description` text DEFAULT NULL,
  `rule_name` varchar(64) DEFAULT NULL,
  `data` blob DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL
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
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL
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
  `name` varchar(64) NOT NULL,
  `data` blob DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auth_user_kampus`
--

CREATE TABLE `auth_user_kampus` (
  `user_id` int(11) NOT NULL,
  `kode_kampus` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `auth_user_kampus`
--

INSERT INTO `auth_user_kampus` (`user_id`, `kode_kampus`) VALUES
(1, 'G1'),
(1, 'G10'),
(1, 'G11'),
(1, 'G12'),
(1, 'G2'),
(1, 'G3'),
(1, 'G4'),
(1, 'G5'),
(1, 'G6'),
(1, 'G7'),
(1, 'G8'),
(1, 'G9'),
(1, 'GP1'),
(1, 'GP2'),
(1, 'GP3'),
(1, 'GP4'),
(1, 'GP5'),
(1, 'GP6'),
(1, 'GP7'),
(1, 'GP8'),
(2, 'G1'),
(3, 'G2');

-- --------------------------------------------------------

--
-- Table structure for table `cycle_migration`
--

CREATE TABLE `cycle_migration` (
  `id` int(11) NOT NULL,
  `migration` varchar(191) NOT NULL,
  `time_executed` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entitas`
--

CREATE TABLE `entitas` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `modul_id` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `kode_kampus` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `entitas`
--

INSERT INTO `entitas` (`id`, `nama`, `modul_id`, `deskripsi`, `created_at`, `updated_at`, `kode_kampus`) VALUES
(73, 'Kurikulum KMI', 1, 'kananaa', NULL, NULL, 'G1'),
(74, 'Kurikulum KMI', 1, 'asfsda', NULL, NULL, 'G2'),
(77, 'Fathul Kutub Kelas 6', 2, '', NULL, NULL, 'G1');

-- --------------------------------------------------------

--
-- Table structure for table `entitas_anggota`
--

CREATE TABLE `entitas_anggota` (
  `id` int(11) NOT NULL,
  `entitas_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama_anggota` varchar(255) NOT NULL,
  `jabatan` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `kode_kampus` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `entitas_anggota`
--

INSERT INTO `entitas_anggota` (`id`, `entitas_id`, `user_id`, `nama_anggota`, `jabatan`, `created_at`, `updated_at`, `kode_kampus`) VALUES
(6, 73, 1, 'admin', 'Ketua', NULL, NULL, 'G1'),
(7, 74, 3, 'guru', 'Ketua', NULL, NULL, 'G2'),
(8, 77, NULL, 'Amin', 'Sekretaris', NULL, NULL, 'G1'),
(9, 77, NULL, 'Shidqi', 'Ketua', NULL, NULL, 'G1');

-- --------------------------------------------------------

--
-- Table structure for table `entitas_program`
--

CREATE TABLE `entitas_program` (
  `id` int(11) NOT NULL,
  `entitas_id` int(11) NOT NULL,
  `nama_program` varchar(255) NOT NULL,
  `periode` varchar(50) DEFAULT NULL,
  `penanggung_jawab_id` int(11) DEFAULT NULL,
  `tupoksi` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `kode_kampus` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `entitas_program`
--

INSERT INTO `entitas_program` (`id`, `entitas_id`, `nama_program`, `periode`, `penanggung_jawab_id`, `tupoksi`, `status`, `created_at`, `updated_at`, `kode_kampus`) VALUES
(7, 73, 'Perbaikan Buku Tajwid Kelas 1', 'mingguan', NULL, 'fafa', 'active', NULL, NULL, 'G1'),
(8, 74, 'Perbaikan Buku Tajwid Kelas 6', 'mingguan', 7, 'safsdaf', 'active', NULL, NULL, 'G2'),
(9, 77, 'Membuat undangan MKM', 'mingguan', 8, '', 'active', NULL, NULL, 'G1');

-- --------------------------------------------------------

--
-- Table structure for table `entitas_program_dokumentasi`
--

CREATE TABLE `entitas_program_dokumentasi` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entitas_program_dokumentasi_foto`
--

CREATE TABLE `entitas_program_dokumentasi_foto` (
  `id` int(11) NOT NULL,
  `dokumentasi_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entitas_program_kanban_column`
--

CREATE TABLE `entitas_program_kanban_column` (
  `id` int(11) NOT NULL,
  `entitas_id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `progress` int(11) NOT NULL DEFAULT 0,
  `requires_proof` tinyint(1) NOT NULL DEFAULT 0,
  `requires_reason` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `entitas_program_kanban_column`
--

INSERT INTO `entitas_program_kanban_column` (`id`, `entitas_id`, `nama`, `urutan`, `created_at`, `updated_at`, `progress`, `requires_proof`, `requires_reason`) VALUES
(26, 73, 'To Do', 1, NULL, NULL, 0, 0, 0),
(27, 73, 'Pending', 2, NULL, NULL, 20, 0, 1),
(28, 73, 'On Progress', 3, NULL, NULL, 50, 1, 0),
(29, 73, 'Rejected', 4, NULL, NULL, 0, 0, 1),
(30, 73, 'Done', 5, NULL, NULL, 100, 1, 0),
(31, 74, 'To Do', 1, NULL, NULL, 0, 0, 0),
(32, 74, 'Pending', 2, NULL, NULL, 20, 0, 1),
(33, 74, 'On Progress', 3, NULL, NULL, 50, 1, 0),
(34, 74, 'Rejected', 4, NULL, NULL, 0, 0, 1),
(35, 74, 'Done', 5, NULL, NULL, 100, 1, 0),
(36, 77, 'To Do', 1, NULL, NULL, 0, 0, 0),
(37, 77, 'Pending', 2, NULL, NULL, 20, 0, 1),
(38, 77, 'On Progress', 3, NULL, NULL, 50, 1, 0),
(39, 77, 'Rejected', 4, NULL, NULL, 0, 0, 1),
(40, 77, 'Done', 5, NULL, NULL, 100, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `entitas_program_kanban_log`
--

CREATE TABLE `entitas_program_kanban_log` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `bukti_foto` varchar(500) DEFAULT NULL,
  `alasan` text DEFAULT NULL,
  `progress_sebelumnya` int(11) DEFAULT NULL,
  `progress_baru` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `entitas_program_kanban_log`
--

INSERT INTO `entitas_program_kanban_log` (`id`, `task_id`, `keterangan`, `bukti_foto`, `alasan`, `progress_sebelumnya`, `progress_baru`, `created_at`) VALUES
(167, 8, 'Tugas dibuat.', NULL, NULL, 0, 0, '2026-06-02 01:18:58'),
(168, 9, 'Tugas dibuat.', NULL, NULL, 0, 0, '2026-06-02 01:19:17'),
(169, 8, 'Dipindahkan ke kolom \'Pending\' via Kanban Board.', NULL, 'sadfsdf', 0, 20, '2026-06-02 01:19:24'),
(170, 9, 'Dipindahkan ke kolom \'On Progress\' via Kanban Board.', 'uploads/entitas_program_kanban_bukti/bukti_6a1e2fa9799fb.webp', NULL, 0, 50, '2026-06-02 01:19:37'),
(171, 9, 'Dipindahkan ke kolom \'Done\' via Kanban Board.', 'uploads/entitas_program_kanban_bukti/bukti_6a1e2fb596d50.webp', NULL, 50, 100, '2026-06-02 01:19:49'),
(172, 10, 'Tugas dibuat.', NULL, NULL, 0, 0, '2026-06-02 01:21:18'),
(173, 10, 'Dipindahkan ke kolom \'Done\' via Kanban Board.', 'uploads/entitas_program_kanban_bukti/bukti_6a1e30237cd9f.webp', NULL, 0, 100, '2026-06-02 01:21:39'),
(174, 11, 'Tugas dibuat.', NULL, NULL, 0, 0, '2026-06-02 03:48:43'),
(175, 11, 'Dipindahkan ke kolom \'On Progress\' via Kanban Board.', 'uploads/entitas_program_kanban_bukti/bukti_6a1e52b57db0a.webp', NULL, 0, 50, '2026-06-02 03:49:09'),
(176, 11, 'Dipindahkan ke kolom \'Done\' via Kanban Board.', 'uploads/entitas_program_kanban_bukti/bukti_6a1e52ccf3f25.webp', NULL, 50, 100, '2026-06-02 03:49:33'),
(177, 12, 'Tugas dibuat.', NULL, NULL, 0, 0, '2026-06-02 11:21:08'),
(178, 13, 'Tugas dibuat.', NULL, NULL, 0, 0, '2026-06-02 11:36:29'),
(179, 14, 'Tugas dibuat.', NULL, NULL, 0, 0, '2026-06-02 11:37:00'),
(180, 9, 'Dipindahkan ke kolom \'To Do\' via Kanban Board.', NULL, 'haa', 100, 0, '2026-06-03 12:58:24'),
(181, 8, 'Dipindahkan ke kolom \'To Do\' via Kanban Board.', NULL, 'haha', 20, 0, '2026-06-03 12:58:28'),
(182, 15, 'Tugas dibuat.', NULL, NULL, 0, 0, '2026-06-03 12:58:37'),
(183, 16, 'Tugas dibuat.', NULL, NULL, 0, 0, '2026-06-03 12:58:46'),
(184, 17, 'Tugas dibuat.', NULL, NULL, 0, 0, '2026-06-03 12:59:50'),
(185, 18, 'Tugas dibuat.', NULL, NULL, 0, 0, '2026-06-03 15:10:38');

-- --------------------------------------------------------

--
-- Table structure for table `entitas_program_kanban_task`
--

CREATE TABLE `entitas_program_kanban_task` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `kanban_column_id` int(11) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `progress` int(11) NOT NULL DEFAULT 0,
  `deadline` date DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `kode_kampus` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `entitas_program_kanban_task`
--

INSERT INTO `entitas_program_kanban_task` (`id`, `program_id`, `kanban_column_id`, `judul`, `deskripsi`, `assigned_to`, `progress`, `deadline`, `urutan`, `created_at`, `updated_at`, `kode_kampus`) VALUES
(8, 7, 26, 'membuat rapar koordinasi internal pengajar tajwid', '', 6, 0, '2026-06-01', 2, NULL, NULL, 'G1'),
(9, 7, 26, 'Menginventarisir poin poin yang akan direvisi', '', 6, 0, '2026-06-02', 1, NULL, NULL, 'G1'),
(10, 8, 35, 'berak', '', 7, 100, '2026-06-16', 1, NULL, NULL, 'G2'),
(11, 9, 40, 'Mailing Nama Musyrif', '', 8, 100, '2026-06-03', 1, NULL, NULL, 'G1'),
(12, 9, 36, 'Print surat', '', 8, 0, NULL, 0, NULL, NULL, 'G1'),
(13, 9, 36, 'Membagikan Surat', '', 8, 0, NULL, 0, NULL, NULL, 'G1'),
(14, 9, 36, 'Mengirim PDF Surat Via Japri', '', 8, 0, NULL, 0, NULL, NULL, 'G1'),
(15, 7, 26, 'fsadf', 'sfsf', 6, 0, '2026-06-18', 0, NULL, NULL, 'G1'),
(16, 7, 26, 'safd', 'sdfsdf', 6, 0, '2026-06-12', 0, NULL, NULL, 'G1'),
(17, 7, 26, 'sdafsfd', 'sadf', 6, 0, '2026-06-12', 0, NULL, NULL, 'G1'),
(18, 7, 26, 'melayuot buku tajwid', 'mengetik ulang', 6, 0, '2026-06-09', 0, NULL, NULL, 'G1');

-- --------------------------------------------------------

--
-- Table structure for table `entitas_program_kendala`
--

CREATE TABLE `entitas_program_kendala` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `jenis` varchar(50) NOT NULL DEFAULT 'terbuka',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `kendala` varchar(255) NOT NULL,
  `solusi_singkat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entitas_program_kendala_foto`
--

CREATE TABLE `entitas_program_kendala_foto` (
  `id` int(11) NOT NULL,
  `kendala_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entitas_program_notulensi`
--

CREATE TABLE `entitas_program_notulensi` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `list_kampus`
--

CREATE TABLE `list_kampus` (
  `kode` varchar(4) NOT NULL,
  `kampus` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kampus_arab` varchar(200) DEFAULT NULL,
  `nama` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sort` tinyint(4) NOT NULL,
  `daerah` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `daerah_arab` varchar(200) DEFAULT NULL,
  `gender` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `list_kampus`
--

INSERT INTO `list_kampus` (`kode`, `kampus`, `kampus_arab`, `nama`, `sort`, `daerah`, `daerah_arab`, `gender`) VALUES
('G1', 'PMDG Kampus Pusat Ponorogo', 'معهد دار السلام كونتور للتربية الإسلامية الحديثة\n', 'Gontor Pusat', 1, 'Ponorogo', 'فونوروكو – إندونيسيا\n', 'M'),
('G10', 'PMDG Kampus 10 Jambi', NULL, 'Gontor 10', 10, 'Jambi', NULL, 'M'),
('G11', 'PMDG Kampus 11 Poso', NULL, 'Gontor 11', 11, 'Poso', NULL, 'M'),
('G12', 'PMDG Kampus 12 Siak', NULL, 'Gontor 12', 12, 'Siak', NULL, 'M'),
('G2', 'PMDG Kampus 2 Ponorogo', NULL, 'Gontor 2', 2, 'Ponorogo', NULL, 'M'),
('G3', 'PMDG Kampus 3 Kediri', NULL, 'Gontor 3', 3, 'Kediri', NULL, 'M'),
('G4', 'PMDG Kampus 4 Banyuwangi', NULL, 'Gontor 4', 4, 'Banyuwangi', NULL, 'M'),
('G5', 'PMDG Kampus 5 Magelang', NULL, 'Gontor 5', 5, 'Magelang', NULL, 'M'),
('G6', 'PMDG Kampus 6 Konawe Selatan', NULL, 'Gontor 6', 6, 'Konawe Selatan', NULL, 'M'),
('G7', 'PMDG Kampus 7 Lampung Selatan', NULL, 'Gontor 7', 7, 'Lampung Selatan', NULL, 'M'),
('G8', 'PMDG Kampus 8 Aceh Besar', NULL, 'Gontor 8', 8, 'Aceh Besar', NULL, 'M'),
('G9', 'PMDG Kampus 9 Sulit Air', NULL, 'Gontor 9', 9, 'Sulit Air', NULL, 'M'),
('GP1', 'PMDG Putri Kampus 1 Mantingan, Ngawi', NULL, 'Gontor Putri 1', 13, 'Ngawi', NULL, 'F'),
('GP2', 'PMDG Putri Kampus 2 Mantingan, Ngawi', NULL, 'Gontor Putri 2', 14, 'Ngawi', NULL, 'F'),
('GP3', 'PMDG Putri Kampus 3 Widodaren, Ngawi', 'معهد دار السلام كونتور للتربية الإسلامية الحديثة  الحرم الثالث للبنات\n', 'Gontor Putri 3', 15, 'Ngawi', 'كارانج بانيو – نجاوي – إندونيسيا', 'F'),
('GP4', 'PMDG Putri Kampus 4 Kediri', NULL, 'Gontor Putri 4', 16, 'Kediri', NULL, 'F'),
('GP5', 'PMDG Putri Kampus 5 Konawe Selatan', NULL, 'Gontor Putri 5', 17, 'Konawe Selatan', NULL, 'F'),
('GP6', 'PMDG Putri Kampus 6 Poso', NULL, 'Gontor Putri 6', 18, 'Poso', NULL, 'F'),
('GP7', 'PMDG Putri Kampus 7 Kampar', NULL, 'Gontor Putri 7', 19, 'Kampar', NULL, 'F'),
('GP8', 'PMDG Putri Kampus 8 Lampung Timur', NULL, 'Gontor Putri 8', 20, 'Lampung Timur', NULL, 'F');

-- --------------------------------------------------------

--
-- Table structure for table `migration`
--

CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `tipe` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modul`
--

INSERT INTO `modul` (`id`, `nama`, `tipe`, `created_at`, `updated_at`) VALUES
(1, 'fungsionaris', 'internal', '2026-05-31 01:47:34', '2026-05-31 01:47:34'),
(2, 'kepanitiaan', 'internal', '2026-05-31 01:47:34', '2026-05-31 01:47:34'),
(3, 'empowering', 'internal', '2026-05-31 01:47:34', '2026-05-31 01:47:34'),
(4, 'koordinator', 'internal', '2026-06-01 05:55:22', '2026-06-01 05:55:22');

-- --------------------------------------------------------

--
-- Table structure for table `rbac_permissions`
--

CREATE TABLE `rbac_permissions` (
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rbac_permissions`
--

INSERT INTO `rbac_permissions` (`name`, `description`) VALUES
('create_empowering', 'Menambahkan entitas Empowering KMI.'),
('create_fungsionaris', 'Menambahkan entitas Fungsionaris KMI.'),
('create_kepanitiaan', 'Menambahkan entitas Kepanitiaan KMI.'),
('create_koordinator', 'Menambahkan entitas Koordinator KMI.'),
('create_program', 'Menambahkan Program Kerja.'),
('delete_empowering', 'Menghapus entitas Empowering KMI.'),
('delete_fungsionaris', 'Menghapus entitas Fungsionaris KMI.'),
('delete_kepanitiaan', 'Menghapus entitas Kepanitiaan KMI.'),
('delete_koordinator', 'Menghapus entitas Koordinator KMI.'),
('delete_program', 'Menghapus Program Kerja.'),
('manage_gii', 'Mengakses Gii Code Generator.'),
('manage_rbac', 'Mengelola RBAC (Role, Permission, User).'),
('update_empowering', 'Mengubah entitas Empowering KMI.'),
('update_fungsionaris', 'Mengubah entitas Fungsionaris KMI.'),
('update_kepanitiaan', 'Mengubah entitas Kepanitiaan KMI.'),
('update_koordinator', 'Mengubah entitas Koordinator KMI.'),
('update_program', 'Mengubah/mengelola Program Kerja.'),
('view_empowering', 'Melihat entitas Empowering KMI.'),
('view_fungsionaris', 'Melihat entitas Fungsionaris KMI.'),
('view_kepanitiaan', 'Melihat entitas Kepanitiaan KMI.'),
('view_koordinator', 'Melihat entitas Koordinator KMI.'),
('view_program', 'Melihat Program Kerja.');

-- --------------------------------------------------------

--
-- Table structure for table `rbac_roles`
--

CREATE TABLE `rbac_roles` (
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rbac_roles`
--

INSERT INTO `rbac_roles` (`name`, `description`) VALUES
('Admin', 'Administrator dengan akses penuh ke sistem dan konfigurasi RBAC.'),
('Guru', 'Pengguna Guru yang hanya memiliki hak akses untuk melihat data.'),
('Operator', 'Staf / Operator yang dapat melakukan CRUD pada modul Guru.');

-- --------------------------------------------------------

--
-- Table structure for table `rbac_role_permissions`
--

CREATE TABLE `rbac_role_permissions` (
  `role_name` varchar(50) NOT NULL,
  `permission_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rbac_role_permissions`
--

INSERT INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES
('Admin', 'create_empowering'),
('Admin', 'create_fungsionaris'),
('Admin', 'create_kepanitiaan'),
('Admin', 'create_koordinator'),
('Admin', 'create_program'),
('Admin', 'delete_empowering'),
('Admin', 'delete_fungsionaris'),
('Admin', 'delete_kepanitiaan'),
('Admin', 'delete_koordinator'),
('Admin', 'delete_program'),
('Admin', 'manage_gii'),
('Admin', 'manage_rbac'),
('Admin', 'update_empowering'),
('Admin', 'update_fungsionaris'),
('Admin', 'update_kepanitiaan'),
('Admin', 'update_koordinator'),
('Admin', 'update_program'),
('Admin', 'view_empowering'),
('Admin', 'view_fungsionaris'),
('Admin', 'view_kepanitiaan'),
('Admin', 'view_koordinator'),
('Admin', 'view_program'),
('Guru', 'view_program'),
('Operator', 'view_empowering'),
('Operator', 'view_fungsionaris'),
('Operator', 'view_kepanitiaan'),
('Operator', 'view_program');

-- --------------------------------------------------------

--
-- Table structure for table `rbac_route_permissions`
--

CREATE TABLE `rbac_route_permissions` (
  `route_name` varchar(100) NOT NULL,
  `permission_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rbac_route_permissions`
--

INSERT INTO `rbac_route_permissions` (`route_name`, `permission_name`) VALUES
('home', NULL),
('login', NULL),
('logout', NULL),
('monitor/index', NULL),
('empowering/create', 'create_empowering'),
('empowering/create/post', 'create_empowering'),
('fungsionaris/create', 'create_fungsionaris'),
('fungsionaris/create/post', 'create_fungsionaris'),
('kepanitiaan/create', 'create_kepanitiaan'),
('kepanitiaan/create/post', 'create_kepanitiaan'),
('koordinator/create', 'create_koordinator'),
('koordinator/create/post', 'create_koordinator'),
('program/create', 'create_program'),
('program/create/post', 'create_program'),
('empowering/delete', 'delete_empowering'),
('fungsionaris/delete', 'delete_fungsionaris'),
('kepanitiaan/delete', 'delete_kepanitiaan'),
('koordinator/delete', 'delete_koordinator'),
('program/delete', 'delete_program'),
('gii/generate', 'manage_gii'),
('gii/index', 'manage_gii'),
('permissions/create', 'manage_rbac'),
('permissions/create/post', 'manage_rbac'),
('permissions/delete', 'manage_rbac'),
('permissions/index', 'manage_rbac'),
('permissions/update', 'manage_rbac'),
('permissions/update/post', 'manage_rbac'),
('roles/create', 'manage_rbac'),
('roles/create/post', 'manage_rbac'),
('roles/delete', 'manage_rbac'),
('roles/index', 'manage_rbac'),
('roles/save-matrix', 'manage_rbac'),
('roles/update', 'manage_rbac'),
('roles/update/post', 'manage_rbac'),
('routes/index', 'manage_rbac'),
('routes/save-route-permissions', 'manage_rbac'),
('users/create', 'manage_rbac'),
('users/create/post', 'manage_rbac'),
('users/delete', 'manage_rbac'),
('users/index', 'manage_rbac'),
('users/update', 'manage_rbac'),
('users/update/post', 'manage_rbac'),
('empowering/add-member', 'update_empowering'),
('empowering/delete-member', 'update_empowering'),
('empowering/update', 'update_empowering'),
('empowering/update-member', 'update_empowering'),
('empowering/update/post', 'update_empowering'),
('fungsionaris/add-member', 'update_fungsionaris'),
('fungsionaris/delete-member', 'update_fungsionaris'),
('fungsionaris/update', 'update_fungsionaris'),
('fungsionaris/update-member', 'update_fungsionaris'),
('fungsionaris/update/post', 'update_fungsionaris'),
('kepanitiaan/add-member', 'update_kepanitiaan'),
('kepanitiaan/delete-member', 'update_kepanitiaan'),
('kepanitiaan/update', 'update_kepanitiaan'),
('kepanitiaan/update-member', 'update_kepanitiaan'),
('kepanitiaan/update/post', 'update_kepanitiaan'),
('koordinator/add-member', 'update_koordinator'),
('koordinator/delete-member', 'update_koordinator'),
('koordinator/update', 'update_koordinator'),
('koordinator/update-member', 'update_koordinator'),
('koordinator/update/post', 'update_koordinator'),
('kanban/add-task', 'update_program'),
('kanban/delete-task', 'update_program'),
('kanban/edit-task', 'update_program'),
('kanban/move-task', 'update_program'),
('program/add-dokumentasi', 'update_program'),
('program/add-kendala', 'update_program'),
('program/add-notulensi', 'update_program'),
('program/delete-dokumentasi', 'update_program'),
('program/delete-kendala', 'update_program'),
('program/delete-notulensi', 'update_program'),
('program/resolve-kendala', 'update_program'),
('program/update', 'update_program'),
('program/update/post', 'update_program'),
('empowering/index', 'view_empowering'),
('empowering/view', 'view_empowering'),
('fungsionaris/index', 'view_fungsionaris'),
('fungsionaris/view', 'view_fungsionaris'),
('kepanitiaan/index', 'view_kepanitiaan'),
('kepanitiaan/view', 'view_kepanitiaan'),
('koordinator/index', 'view_koordinator'),
('koordinator/view', 'view_koordinator'),
('kanban/board', 'view_program'),
('program/view', 'view_program');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@teqic.com', '$2y$10$Wyg3apSgUCg6Eah8jCRvC.R7vxutlREUdF/2DQJQIZHTbCHcfFAk6', 'Admin', '2026-05-31 05:08:04'),
(2, 'operator', 'operator@teqic.com', '$2y$10$5WCxDmmu.djZiHTpFT6r8.SZJ8Ep/xt8phtGQMSdytCOBvjXXtpSi', 'Operator', '2026-05-31 05:08:04'),
(3, 'guru', 'guru@teqic.com', '$2y$10$GajydO.IHlqLF7xv4FOSq.yd06A0W4two3REAB6fx8AFCFRjCX.5G', 'Guru', '2026-05-31 05:08:04');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `auth_user_kampus`
--
ALTER TABLE `auth_user_kampus`
  ADD PRIMARY KEY (`user_id`,`kode_kampus`),
  ADD KEY `auth_user_kampus_index_user_id_6a1e1e4f161e3` (`user_id`);

--
-- Indexes for table `cycle_migration`
--
ALTER TABLE `cycle_migration`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cycle_migration_index_migration_created_at_6a1e1e209bccb` (`migration`,`created_at`);

--
-- Indexes for table `entitas`
--
ALTER TABLE `entitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entitas_index_modul_id_6a1bb82b0f61e` (`modul_id`);

--
-- Indexes for table `entitas_anggota`
--
ALTER TABLE `entitas_anggota`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-anggota_entitas-entitas_id` (`entitas_id`);

--
-- Indexes for table `entitas_program`
--
ALTER TABLE `entitas_program`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-program-entitas_id` (`entitas_id`),
  ADD KEY `fk-program-penanggung_jawab_id` (`penanggung_jawab_id`);

--
-- Indexes for table `entitas_program_dokumentasi`
--
ALTER TABLE `entitas_program_dokumentasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-dokumentasi-program_id` (`program_id`);

--
-- Indexes for table `entitas_program_dokumentasi_foto`
--
ALTER TABLE `entitas_program_dokumentasi_foto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `da5166e56920f54ff3e365e7fb4886a1` (`dokumentasi_id`);

--
-- Indexes for table `entitas_program_kanban_column`
--
ALTER TABLE `entitas_program_kanban_column`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-kanban_columns-entitas_id` (`entitas_id`);

--
-- Indexes for table `entitas_program_kanban_log`
--
ALTER TABLE `entitas_program_kanban_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-task_progress_logs-task_id` (`task_id`);

--
-- Indexes for table `entitas_program_kanban_task`
--
ALTER TABLE `entitas_program_kanban_task`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-tasks-program_id` (`program_id`),
  ADD KEY `fk-tasks-kanban_column_id` (`kanban_column_id`),
  ADD KEY `fk-tasks-assigned_to` (`assigned_to`);

--
-- Indexes for table `entitas_program_kendala`
--
ALTER TABLE `entitas_program_kendala`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-kendala_program-program_id` (`program_id`);

--
-- Indexes for table `entitas_program_kendala_foto`
--
ALTER TABLE `entitas_program_kendala_foto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entitas_program_kendala_foto_index_kendala_id_6a1d3319e2ee6` (`kendala_id`);

--
-- Indexes for table `entitas_program_notulensi`
--
ALTER TABLE `entitas_program_notulensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk-notulensi-program_id` (`program_id`);

--
-- Indexes for table `list_kampus`
--
ALTER TABLE `list_kampus`
  ADD PRIMARY KEY (`kode`);

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
-- Indexes for table `rbac_permissions`
--
ALTER TABLE `rbac_permissions`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `rbac_roles`
--
ALTER TABLE `rbac_roles`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `rbac_role_permissions`
--
ALTER TABLE `rbac_role_permissions`
  ADD PRIMARY KEY (`role_name`,`permission_name`),
  ADD KEY `permission_name` (`permission_name`);

--
-- Indexes for table `rbac_route_permissions`
--
ALTER TABLE `rbac_route_permissions`
  ADD PRIMARY KEY (`route_name`),
  ADD KEY `permission_name` (`permission_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cycle_migration`
--
ALTER TABLE `cycle_migration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entitas`
--
ALTER TABLE `entitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `entitas_anggota`
--
ALTER TABLE `entitas_anggota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `entitas_program`
--
ALTER TABLE `entitas_program`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `entitas_program_dokumentasi`
--
ALTER TABLE `entitas_program_dokumentasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `entitas_program_dokumentasi_foto`
--
ALTER TABLE `entitas_program_dokumentasi_foto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `entitas_program_kanban_column`
--
ALTER TABLE `entitas_program_kanban_column`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `entitas_program_kanban_log`
--
ALTER TABLE `entitas_program_kanban_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- AUTO_INCREMENT for table `entitas_program_kanban_task`
--
ALTER TABLE `entitas_program_kanban_task`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `entitas_program_kendala`
--
ALTER TABLE `entitas_program_kendala`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `entitas_program_kendala_foto`
--
ALTER TABLE `entitas_program_kendala_foto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `entitas_program_notulensi`
--
ALTER TABLE `entitas_program_notulensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `modul`
--
ALTER TABLE `modul`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

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
-- Constraints for table `auth_user_kampus`
--
ALTER TABLE `auth_user_kampus`
  ADD CONSTRAINT `auth_user_kampus_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entitas`
--
ALTER TABLE `entitas`
  ADD CONSTRAINT `entitas_foreign_modul_id_6a1bb8d13c969` FOREIGN KEY (`modul_id`) REFERENCES `modul` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entitas_anggota`
--
ALTER TABLE `entitas_anggota`
  ADD CONSTRAINT `fk-anggota_entitas-entitas_id` FOREIGN KEY (`entitas_id`) REFERENCES `entitas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entitas_program`
--
ALTER TABLE `entitas_program`
  ADD CONSTRAINT `fk-program-entitas_id` FOREIGN KEY (`entitas_id`) REFERENCES `entitas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk-program-penanggung_jawab_id` FOREIGN KEY (`penanggung_jawab_id`) REFERENCES `entitas_anggota` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `entitas_program_dokumentasi`
--
ALTER TABLE `entitas_program_dokumentasi`
  ADD CONSTRAINT `fk-dokumentasi-program_id` FOREIGN KEY (`program_id`) REFERENCES `entitas_program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entitas_program_dokumentasi_foto`
--
ALTER TABLE `entitas_program_dokumentasi_foto`
  ADD CONSTRAINT `6a7a4acfe56ff6caf111e5d8066ec896` FOREIGN KEY (`dokumentasi_id`) REFERENCES `entitas_program_dokumentasi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entitas_program_kanban_column`
--
ALTER TABLE `entitas_program_kanban_column`
  ADD CONSTRAINT `fk-kanban_columns-entitas_id` FOREIGN KEY (`entitas_id`) REFERENCES `entitas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entitas_program_kanban_log`
--
ALTER TABLE `entitas_program_kanban_log`
  ADD CONSTRAINT `fk-task_progress_logs-task_id` FOREIGN KEY (`task_id`) REFERENCES `entitas_program_kanban_task` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entitas_program_kanban_task`
--
ALTER TABLE `entitas_program_kanban_task`
  ADD CONSTRAINT `fk-tasks-assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `entitas_anggota` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `fk-tasks-kanban_column_id` FOREIGN KEY (`kanban_column_id`) REFERENCES `entitas_program_kanban_column` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `fk-tasks-program_id` FOREIGN KEY (`program_id`) REFERENCES `entitas_program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entitas_program_kendala`
--
ALTER TABLE `entitas_program_kendala`
  ADD CONSTRAINT `fk-kendala_program-program_id` FOREIGN KEY (`program_id`) REFERENCES `entitas_program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entitas_program_kendala_foto`
--
ALTER TABLE `entitas_program_kendala_foto`
  ADD CONSTRAINT `entitas_program_kendala_foto_foreign_kendala_id_6a1d3319e2ef3` FOREIGN KEY (`kendala_id`) REFERENCES `entitas_program_kendala` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entitas_program_notulensi`
--
ALTER TABLE `entitas_program_notulensi`
  ADD CONSTRAINT `fk-notulensi-program_id` FOREIGN KEY (`program_id`) REFERENCES `entitas_program` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rbac_role_permissions`
--
ALTER TABLE `rbac_role_permissions`
  ADD CONSTRAINT `rbac_role_permissions_ibfk_1` FOREIGN KEY (`role_name`) REFERENCES `rbac_roles` (`name`) ON DELETE CASCADE,
  ADD CONSTRAINT `rbac_role_permissions_ibfk_2` FOREIGN KEY (`permission_name`) REFERENCES `rbac_permissions` (`name`) ON DELETE CASCADE;

--
-- Constraints for table `rbac_route_permissions`
--
ALTER TABLE `rbac_route_permissions`
  ADD CONSTRAINT `rbac_route_permissions_ibfk_1` FOREIGN KEY (`permission_name`) REFERENCES `rbac_permissions` (`name`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
