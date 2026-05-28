-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 28, 2026 at 03:44 AM
-- Server version: 8.0.45-0ubuntu0.24.04.1
-- PHP Version: 8.5.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `teqic_yii3`
--

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `kdg` int NOT NULL,
  `stambuk` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `daerah` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `konsulat` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_telp` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kamar_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`kdg`, `stambuk`, `nama`, `daerah`, `konsulat`, `email`, `no_telp`, `kamar_id`) VALUES
(1, '20261001', 'Ruba Fana', 'Karawang', 'Bekasi', 'ruba@gmail.com', '081234567890', 3),
(2, '20269999', 'Guru Baru Excel Diubah', 'Jawa Barat', 'Gontor 2', 'newexcel@gmail.com', '085544443333', 1),
(3, '20260001', 'Ahmad Fauzi', 'Jawa Timur', 'Gontor 1', 'ahmad@gmail.com', '081234567890', 2),
(4, '23487985', 'Kakanya', 'Banyuwangi', 'Besuki', 'Kimak@gmail.com', '321654987', 4),
(5, '20269999', 'Test Guru Kamar', 'Jatim', 'Ponorogo', 'testkamar@teqic.com', '089999999999', 5);

-- --------------------------------------------------------

--
-- Table structure for table `kamar`
--

CREATE TABLE `kamar` (
  `id` int NOT NULL,
  `nama_kamar` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kapasitas` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kamar`
--

INSERT INTO `kamar` (`id`, `nama_kamar`, `kapasitas`) VALUES
(1, 'Kamar Abu Bakar', 10),
(2, 'Kamar Umar bin Khattab', 12),
(3, 'Kamar Utsman bin Affan', 8),
(4, 'Kamar Ali bin Abi Thalib', 15),
(5, 'Kamar Al-Ghazali', 20);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int NOT NULL,
  `nama_kategori` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `konsulat`
--

CREATE TABLE `konsulat` (
  `id` int NOT NULL,
  `konsulat` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kampus` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `konsulat`
--

INSERT INTO `konsulat` (`id`, `konsulat`, `kampus`) VALUES
(1, 'Surabaya', 'Kampus 1'),
(2, 'Jakarta', 'Kampus 2'),
(5, 'Besuki', '3'),
(6, 'Gontor 1', 'G3'),
(7, 'Bandung', 'Kampus Gontor 4');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggaran`
--

CREATE TABLE `pelanggaran` (
  `id` int NOT NULL,
  `stambuk` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pelanggaran` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `poin` int NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
('create_guru', 'Menambahkan data Guru baru dan mengunggah Excel.'),
('create_kamar', 'Menambahkan data Kamar baru.'),
('create_konsulat', 'Menambahkan data Konsulat baru.'),
('create_pelanggaran', 'Menambahkan data Pelanggaran baru.'),
('create_santri', 'Menambahkan data Santri baru.'),
('delete_guru', 'Menghapus data Guru.'),
('delete_kamar', 'Menghapus data Kamar.'),
('delete_konsulat', 'Menghapus data Konsulat.'),
('delete_pelanggaran', 'Menghapus data Pelanggaran.'),
('delete_santri', 'Menghapus data Santri.'),
('manage_gii', 'Akses dan penggunaan Gii Generator.'),
('manage_rbac', 'Mengelola RBAC (Peran, Hak Akses, Matrix, & Pengguna).'),
('update_guru', 'Mengubah detail data Guru.'),
('update_kamar', 'Mengubah detail data Kamar.'),
('update_konsulat', 'Mengubah detail data Konsulat.'),
('update_pelanggaran', 'Mengubah detail data Pelanggaran.'),
('update_santri', 'Mengubah detail data Santri.'),
('view_guru', 'Melihat daftar dan detail data Guru.'),
('view_kamar', 'Melihat daftar dan detail data Kamar.'),
('view_konsulat', 'Melihat daftar dan detail data Konsulat.'),
('view_pelanggaran', 'Melihat daftar dan detail data Pelanggaran.'),
('view_santri', 'Melihat daftar dan detail data Santri.');

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
('Guru', 'Pengguna Guru yang hanya memiliki hak akses untuk melihat data.'),
('Operator', 'Staf / Operator yang dapat melakukan CRUD pada modul Guru.');

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
('Admin', 'create_guru'),
('Operator', 'create_guru'),
('Admin', 'create_kamar'),
('Operator', 'create_kamar'),
('Admin', 'create_konsulat'),
('Operator', 'create_konsulat'),
('Admin', 'create_pelanggaran'),
('Operator', 'create_pelanggaran'),
('Admin', 'create_santri'),
('Operator', 'create_santri'),
('Admin', 'delete_guru'),
('Operator', 'delete_guru'),
('Admin', 'delete_kamar'),
('Admin', 'delete_konsulat'),
('Admin', 'delete_pelanggaran'),
('Admin', 'delete_santri'),
('Admin', 'manage_gii'),
('Admin', 'manage_rbac'),
('Admin', 'update_guru'),
('Operator', 'update_guru'),
('Admin', 'update_kamar'),
('Operator', 'update_kamar'),
('Admin', 'update_konsulat'),
('Operator', 'update_konsulat'),
('Admin', 'update_pelanggaran'),
('Operator', 'update_pelanggaran'),
('Admin', 'update_santri'),
('Operator', 'update_santri'),
('Admin', 'view_guru'),
('Guru', 'view_guru'),
('Operator', 'view_guru'),
('Admin', 'view_kamar'),
('Operator', 'view_kamar'),
('Admin', 'view_konsulat'),
('Operator', 'view_konsulat'),
('Admin', 'view_pelanggaran'),
('Operator', 'view_pelanggaran'),
('Admin', 'view_santri'),
('Operator', 'view_santri');

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
('hello', NULL),
('home', NULL),
('login', NULL),
('logout', NULL),
('guru/create', 'create_guru'),
('guru/create/post', 'create_guru'),
('guru/upload', 'create_guru'),
('kamar/create', 'create_kamar'),
('kamar/create/post', 'create_kamar'),
('konsulat/create', 'create_konsulat'),
('konsulat/create/post', 'create_konsulat'),
('pelanggaran/create', 'create_pelanggaran'),
('pelanggaran/create/post', 'create_pelanggaran'),
('santri/create', 'create_santri'),
('santri/create/post', 'create_santri'),
('guru/delete', 'delete_guru'),
('kamar/delete', 'delete_kamar'),
('konsulat/delete', 'delete_konsulat'),
('pelanggaran/delete', 'delete_pelanggaran'),
('santri/delete', 'delete_santri'),
('gii/generate', 'manage_gii'),
('gii/index', 'manage_gii'),
('permissions/create', 'manage_rbac'),
('permissions/create/post', 'manage_rbac'),
('permissions/delete', 'manage_rbac'),
('permissions/index', 'manage_rbac'),
('permissions/update', 'manage_rbac'),
('permissions/update/post', 'manage_rbac'),
('rbac/create-user', 'manage_rbac'),
('rbac/index', 'manage_rbac'),
('rbac/save-matrix', 'manage_rbac'),
('rbac/save-route-permissions', 'manage_rbac'),
('rbac/update-user-role', 'manage_rbac'),
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
('guru/update', 'update_guru'),
('guru/update/post', 'update_guru'),
('kamar/update', 'update_kamar'),
('kamar/update/post', 'update_kamar'),
('konsulat/update', 'update_konsulat'),
('konsulat/update/post', 'update_konsulat'),
('pelanggaran/update', 'update_pelanggaran'),
('pelanggaran/update/post', 'update_pelanggaran'),
('santri/update', 'update_santri'),
('santri/update/post', 'update_santri'),
('guru/download-template', 'view_guru'),
('guru/index', 'view_guru'),
('kamar/index', 'view_kamar'),
('konsulat/index', 'view_konsulat'),
('pelanggaran/index', 'view_pelanggaran'),
('santri/index', 'view_santri');

-- --------------------------------------------------------

--
-- Table structure for table `santri`
--

CREATE TABLE `santri` (
  `kds` int UNSIGNED NOT NULL,
  `nama` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `daerah` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `santri`
--

INSERT INTO `santri` (`kds`, `nama`, `kelas`, `daerah`) VALUES
(1, 'Hamid', '2b', 'Yogyakarta');

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
(1, 'admin', 'admin@teqic.com', '$2y$12$vJzL0dYf4hbraAe9QEv43epPe/7l/gt/Qie0ORthjQp/N0H1VfhHa', 'Admin', '2026-05-23 13:06:10'),
(2, 'operator', 'operator@teqic.com', '$2y$12$TC4MVM3Ozlt2zhIe7h93ueDx5kv5j3lylyz6WsvajgtZ29wA05ZlW', 'Operator', '2026-05-23 13:06:10'),
(3, 'guru', 'guru@teqic.com', '$2y$12$fQdKC1/zf2SobI2YL9348.HfQgENm0XXJ8g6A8P9HncujOxy1/naG', 'Guru', '2026-05-23 13:06:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`kdg`),
  ADD KEY `fk_guru_kamar` (`kamar_id`);

--
-- Indexes for table `kamar`
--
ALTER TABLE `kamar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `konsulat`
--
ALTER TABLE `konsulat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pelanggaran`
--
ALTER TABLE `pelanggaran`
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
-- Indexes for table `santri`
--
ALTER TABLE `santri`
  ADD PRIMARY KEY (`kds`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `kdg` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kamar`
--
ALTER TABLE `kamar`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `konsulat`
--
ALTER TABLE `konsulat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pelanggaran`
--
ALTER TABLE `pelanggaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `santri`
--
ALTER TABLE `santri`
  MODIFY `kds` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guru`
--
ALTER TABLE `guru`
  ADD CONSTRAINT `fk_guru_kamar` FOREIGN KEY (`kamar_id`) REFERENCES `kamar` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

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
