-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 24, 2026 at 03:36 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webinar_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_admin` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`, `nama_admin`) VALUES
(1, 'admin', '$2y$10$YourHashedPassword', 'Bayu Anggara');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `npp` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_mahasiswa` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `fakultas` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jurusan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`npp`, `nama_mahasiswa`, `fakultas`, `jurusan`) VALUES
('2021117657', 'Fuad', 'Ekonomi', 'Teknik Jomok'),
('20211176576', 'Amba', 'Ekonomi', 'Teknik Jmk'),
('202677788', 'Fuad', 'Ekonomi', 'JMK');

-- --------------------------------------------------------

--
-- Table structure for table `pemantauan_webinar`
--

CREATE TABLE `pemantauan_webinar` (
  `id_pendaftaran` int NOT NULL,
  `id_webinar` int DEFAULT NULL,
  `npp` varchar(13) COLLATE utf8mb4_general_ci NOT NULL,
  `motivasi` text COLLATE utf8mb4_general_ci,
  `status_pendaftaran` enum('menunggu','disetujui','ditolak') COLLATE utf8mb4_general_ci DEFAULT 'menunggu',
  `tanggal_daftar` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemantauan_webinar`
--

INSERT INTO `pemantauan_webinar` (`id_pendaftaran`, `id_webinar`, `npp`, `motivasi`, `status_pendaftaran`, `tanggal_daftar`) VALUES
(5, 5, '2021117657', 'Jomok banger', 'ditolak', '2026-01-19 14:44:52'),
(6, 5, '20211176576', 'Jomok banger', 'disetujui', '2026-01-19 14:52:17'),
(7, 5, '202677788', 'Saya ingin mendpaatkan cukuruk', 'disetujui', '2026-01-20 03:46:20');

-- --------------------------------------------------------

--
-- Table structure for table `penyelenggara`
--

CREATE TABLE `penyelenggara` (
  `id_penyelenggara` int NOT NULL,
  `nama_penyelenggara` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `instansi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_wa` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penyelenggara`
--

INSERT INTO `penyelenggara` (`id_penyelenggara`, `nama_penyelenggara`, `instansi`, `no_wa`) VALUES
(1, 'Bayu Anggara', 'UNIBI', '081234567890'),
(2, 'Budi Santoso', 'Teknologi Pendidikan', '081298765432');

-- --------------------------------------------------------

--
-- Table structure for table `webinar`
--

CREATE TABLE `webinar` (
  `id_webinar` int NOT NULL,
  `judul` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `kategori` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_selesai` time DEFAULT NULL,
  `pembicara` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `platform` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `poin_skkm` int DEFAULT NULL,
  `kuota_peserta` int DEFAULT NULL,
  `biaya` decimal(10,2) DEFAULT '0.00',
  `tipe_webinar` enum('gratis','berbayar') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('draft','publish','closed') COLLATE utf8mb4_general_ci DEFAULT 'draft',
  `status_verifikasi` enum('menunggu','disetujui','ditolak') COLLATE utf8mb4_general_ci DEFAULT 'menunggu',
  `tanggal_mulai_pendaftaran` datetime DEFAULT NULL,
  `tanggal_akhir_pendaftaran` datetime DEFAULT NULL,
  `id_penyelenggara` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `webinar`
--

INSERT INTO `webinar` (`id_webinar`, `judul`, `deskripsi`, `kategori`, `tanggal`, `waktu_mulai`, `waktu_selesai`, `pembicara`, `platform`, `poin_skkm`, `kuota_peserta`, `biaya`, `tipe_webinar`, `status`, `status_verifikasi`, `tanggal_mulai_pendaftaran`, `tanggal_akhir_pendaftaran`, `id_penyelenggara`, `created_at`) VALUES
(1, 'AI and Machine Learning in Modern Education', 'Webinar tentang implementasi AI dalam pendidikan modern', 'Teknologi', '2026-01-20', '14:00:00', '16:00:00', 'Dr. Andi Wijaya', 'Zoom', 2, 200, 0.00, 'gratis', 'publish', 'disetujui', NULL, NULL, 1, '2026-01-18 14:29:29'),
(2, 'Digital Marketing Strategies for Startups', 'Strategi pemasaran digital untuk startup', 'Bisnis', '2026-01-22', '13:00:00', '15:00:00', 'Siti Nurhaliza', 'Google Meet', 2, 200, 0.00, 'gratis', 'publish', 'disetujui', NULL, NULL, 1, '2026-01-18 14:29:29'),
(3, 'Sustainable Development and Climate Action', 'Pembangunan berkelanjutan dan aksi iklim', 'Lingkungan', '2026-01-25', '10:00:00', '12:00:00', 'Prof. Bambang Sutrisno', 'Zoom', 3, 150, 0.00, 'gratis', 'publish', 'disetujui', NULL, NULL, 1, '2026-01-18 14:29:29'),
(4, 'Ai machinelearning', 'nice', 'Teknologi', '2026-02-01', '14:00:00', '15:00:00', 'Andika', 'Zoom', 5, 100, 0.00, '', 'publish', 'disetujui', '2026-01-18 00:00:00', '2026-01-23 00:00:00', 1, '2026-01-18 14:34:02'),
(5, 'Menangkao Suki', 'Suki', 'Lingkungan', '2026-01-22', '08:35:00', '10:35:00', 'Rusdi', 'Zoom', 10, 500, 0.00, '', 'publish', 'disetujui', '2026-01-19 08:35:00', '2026-01-21 08:36:00', 1, '2026-01-19 01:36:10'),
(6, 'Pendidikan Cara Menjadi Jomok', 'Disini anda akan disuguhkan muaniteri yang dapat membantu anda dalam memahami cara menjadi jomo', 'Pendidikan', '2026-01-22', '16:26:00', '18:26:00', 'Rusdi', 'Zoom', 10, 200, 0.00, 'gratis', 'publish', 'menunggu', '2026-01-10 20:26:00', '2026-01-21 20:26:00', 1, '2026-01-19 13:32:58'),
(7, 'AI', 'Artificial Intelligence', 'Teknologi', '2026-01-23', '21:35:00', '23:35:00', 'Gibran Rakabuming', 'Zoom', 10, 500, 0.00, 'gratis', 'draft', 'ditolak', '2026-01-01 20:35:00', '2026-01-20 20:35:00', 1, '2026-01-19 13:35:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`npp`);

--
-- Indexes for table `pemantauan_webinar`
--
ALTER TABLE `pemantauan_webinar`
  ADD PRIMARY KEY (`id_pendaftaran`),
  ADD KEY `id_webinar` (`id_webinar`),
  ADD KEY `fk_mahasiswa_pendaftaran` (`npp`);

--
-- Indexes for table `penyelenggara`
--
ALTER TABLE `penyelenggara`
  ADD PRIMARY KEY (`id_penyelenggara`);

--
-- Indexes for table `webinar`
--
ALTER TABLE `webinar`
  ADD PRIMARY KEY (`id_webinar`),
  ADD KEY `id_penyelenggara` (`id_penyelenggara`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pemantauan_webinar`
--
ALTER TABLE `pemantauan_webinar`
  MODIFY `id_pendaftaran` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `penyelenggara`
--
ALTER TABLE `penyelenggara`
  MODIFY `id_penyelenggara` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `webinar`
--
ALTER TABLE `webinar`
  MODIFY `id_webinar` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pemantauan_webinar`
--
ALTER TABLE `pemantauan_webinar`
  ADD CONSTRAINT `fk_mahasiswa_pendaftaran` FOREIGN KEY (`npp`) REFERENCES `mahasiswa` (`npp`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pemantauan_webinar_ibfk_1` FOREIGN KEY (`id_webinar`) REFERENCES `webinar` (`id_webinar`) ON DELETE CASCADE;

--
-- Constraints for table `webinar`
--
ALTER TABLE `webinar`
  ADD CONSTRAINT `webinar_ibfk_1` FOREIGN KEY (`id_penyelenggara`) REFERENCES `penyelenggara` (`id_penyelenggara`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
