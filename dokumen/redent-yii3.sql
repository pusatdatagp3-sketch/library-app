-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 04, 2026 at 12:55 AM
-- Server version: 8.0.46-0ubuntu0.24.04.2
-- PHP Version: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `redent-yii3`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth_user_kampus`
--

CREATE TABLE `auth_user_kampus` (
  `user_id` int NOT NULL,
  `kode_kampus` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `auth_user_kampus`
--

INSERT INTO `auth_user_kampus` (`user_id`, `kode_kampus`) VALUES
(1, 'G1'),
(1, 'G2'),
(2, 'G1'),
(3, 'G2');

-- --------------------------------------------------------

--
-- Table structure for table `list_kampus`
--

CREATE TABLE `list_kampus` (
  `kode` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kampus` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kampus_arab` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sort` tinyint NOT NULL,
  `daerah` char(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `daerah_arab` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
-- Table structure for table `rbac_permissions`
--

CREATE TABLE `rbac_permissions` (
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rbac_permissions`
--

INSERT INTO `rbac_permissions` (`name`, `description`) VALUES
('manage_gii', 'Mengakses Gii Code Generator.'),
('manage_rbac', 'Mengelola RBAC (Role, Permission, User).');

-- --------------------------------------------------------

--
-- Table structure for table `rbac_roles`
--

CREATE TABLE `rbac_roles` (
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rbac_roles`
--

INSERT INTO `rbac_roles` (`name`, `description`) VALUES
('Admin', 'Administrator dengan akses penuh ke sistem dan konfigurasi RBAC.'),
('Guru', 'Pengguna Guru dengan akses dasar.'),
('Operator', 'Staf / Operator dengan akses terbatas.');

-- --------------------------------------------------------

--
-- Table structure for table `rbac_role_permissions`
--

CREATE TABLE `rbac_role_permissions` (
  `role_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permission_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rbac_role_permissions`
--

INSERT INTO `rbac_role_permissions` (`role_name`, `permission_name`) VALUES
('Admin', 'manage_gii'),
('Admin', 'manage_rbac');

-- --------------------------------------------------------

--
-- Table structure for table `rbac_route_permissions`
--

CREATE TABLE `rbac_route_permissions` (
  `route_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permission_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rbac_route_permissions`
--

INSERT INTO `rbac_route_permissions` (`route_name`, `permission_name`) VALUES
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
('users/update/post', 'manage_rbac');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@teqic.com', '$2y$10$/.ud67B.vDgnnDfRCLV8HusIpzorXFP.dttFvLtCn9EoKL8C62S.S', 'Admin', '2026-06-04 00:48:01'),
(2, 'operator', 'operator@teqic.com', '$2y$10$mDq3GjhnZwurLsK1CJ5S5eawlYnYxxyrztjQqQYvmX/eUDKQ2duE.', 'Operator', '2026-06-04 00:48:01'),
(3, 'guru', 'guru@teqic.com', '$2y$10$v4rLnRyYIt2jza8aFFc.FOW0pcLl9Sr8s8d6vJCVI/2xnDHMt0zVW', 'Guru', '2026-06-04 00:48:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth_user_kampus`
--
ALTER TABLE `auth_user_kampus`
  ADD PRIMARY KEY (`user_id`,`kode_kampus`),
  ADD KEY `auth_user_kampus_index_user_id_6a20caf82941c` (`user_id`);

--
-- Indexes for table `list_kampus`
--
ALTER TABLE `list_kampus`
  ADD PRIMARY KEY (`kode`);

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auth_user_kampus`
--
ALTER TABLE `auth_user_kampus`
  ADD CONSTRAINT `auth_user_kampus_foreign_user_id_6a20caf82945c` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
