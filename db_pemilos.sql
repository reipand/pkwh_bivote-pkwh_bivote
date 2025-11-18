-- phpMyAdmin SQL Dump
-- version 5.2.3-1.fc42.remi
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 27, 2025 at 06:31 AM
-- Server version: 10.11.11-MariaDB
-- PHP Version: 8.4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_pemilos`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$8glLM.iNLCebL/AOqREoW.Jnwf3BmHW9sIzGabg7SW8LBFPGPIoaa');

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `nik` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `status_memilih` tinyint(1) DEFAULT 0 COMMENT '0=Belum, 1=Sudah',
  `id_kandidat_dipilih` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id`, `nama_lengkap`, `nik`, `password`, `jabatan`, `status_memilih`, `id_kandidat_dipilih`, `created_at`) VALUES
(1, 'Guru Test', '13.22.16.181', '132216181', 'Guru Matematika', 0, NULL, '2025-10-08 14:48:19'),
(2, 'Dr. Siti Nurhaliza, M.Pd', '1234567890123456', 'guru123', 'Kepala Sekolah', 0, NULL, '2025-10-21 05:05:08'),
(3, 'Budi Santoso, S.Pd', '2345678901234567', 'guru456', 'Wakil Kepala Sekolah', 0, NULL, '2025-10-21 05:05:08'),
(4, 'Sari Indah, S.Pd', '3456789012345678', 'guru789', 'Guru Matematika', 0, NULL, '2025-10-21 05:05:08'),
(5, 'Ahmad Fauzi, S.Pd', '4567890123456789', 'guru101', 'Guru Bahasa Indonesia', 0, NULL, '2025-10-21 05:05:08'),
(6, 'Dewi Kartika, S.Pd', '5678901234567890', 'guru202', 'Guru Bahasa Inggris', 0, NULL, '2025-10-21 05:05:08');

-- --------------------------------------------------------

--
-- Table structure for table `kandidat`
--

CREATE TABLE `kandidat` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `nis` varchar(50) NOT NULL,
  `visi` text NOT NULL,
  `misi` text NOT NULL,
  `video_path` varchar(255) DEFAULT NULL,
  `foto_path` varchar(255) NOT NULL,
  `kejar` varchar(255) NOT NULL,
  `usia` int(11) DEFAULT NULL,
  `jumlah_suara` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kandidat`
--

INSERT INTO `kandidat` (`id`, `nama_lengkap`, `nis`, `visi`, `misi`, `video_path`, `foto_path`, `kejar`, `usia`, `jumlah_suara`) VALUES
(1, 'Qanita Malila Oufwa Pahingguan', '2001', 'Mewujudkan sekolah yang berbasis teknologi, berintegritas, dan transparan.', 'Membangun sistem voting online yang transparan dan aman.; Mengadakan workshop coding dan pengembangan aplikasi untuk siswa.; Meningkatkan literasi digital di lingkungan sekolah.', '../assets/videos/video_1_1755678443.mp4', '../assets/image/kandidat_1_1756265965.png', 'Desain Komunikasi Visual', 17, 2),
(2, 'Aura Anastasya Putri Fiara', '2002', 'Menciptakan lingkungan sekolah yang kreatif dan inovatif.', 'Mengadakan workshop seni dan event untuk mengembangkan bakat siswa.; Membentuk komunitas kreatif di setiap kelas.; Menjalin kolaborasi dengan pihak luar untuk event sekolah.', '../assets/videos/video_2_1756106736.mp4', '../assets/image/kandidat_2_1756266166.jpg', 'Rekayasa Perangkat Lunak', 17, 1),
(3, 'Safdiza Azizi', '2003', 'Meningkatkan kesadaran lingkungan di sekolah.', 'Mengadakan program daur ulang dan penanaman pohon rutin.; Mengampanyekan penggunaan tumbler dan mengurangi sampah plastik.; Membangun taman sekolah yang produktif.', '../assets/videos/video_3_1755879775.mp4', '../assets/image/kandidat_3_1756265984.png', 'Rekayasa Perangkat Lunak', 17, 0),
(4, 'Samudera', '2004', 'Menjadikan OSIS sebagai wadah aspirasi siswa.', 'Membentuk forum diskusi bulanan untuk semua siswa.; Menyediakan kotak saran online yang dapat diakses setiap saat.; Mengadakan pertemuan rutin antara perwakilan kelas dengan pihak sekolah.', '../assets/videos/video_4_1755879707.mp4', '../assets/image/kandidat_4_1756266005.jpg', 'Rekayasa Perangkat Lunak', 17, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pemilih`
--

CREATE TABLE `pemilih` (
  `id` int(11) NOT NULL,
  `nis` varchar(50) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `tanggal_lahir` varchar(20) NOT NULL COMMENT 'Format: DD/MM/YY',
  `status_memilih` tinyint(1) DEFAULT 0 COMMENT '0=Belum, 1=Sudah',
  `id_kandidat_dipilih` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemilih`
--

INSERT INTO `pemilih` (`id`, `nis`, `nama_lengkap`, `tanggal_lahir`, `status_memilih`, `id_kandidat_dipilih`) VALUES
(1, '1001', 'Budi Santoso', '15/08/06', 0, NULL),
(2, '1002', 'Citra Lestari', '22/01/07', 0, NULL),
(3, '1003', 'Dewi Anggraini', '30/11/06', 0, NULL),
(5, '23173100', 'Reisan Adrefa', '19/06/08', 0, NULL),
(6, '23173056', 'Rafa Adya', '26/01/08', 1, NULL),
(7, '23173053', 'Naufal', '27/04/08', 0, NULL),
(8, '23173063', 'Safdiza Azizi', '15/05/08', 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD KEY `id_kandidat_dipilih` (`id_kandidat_dipilih`);

--
-- Indexes for table `kandidat`
--
ALTER TABLE `kandidat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`);

--
-- Indexes for table `pemilih`
--
ALTER TABLE `pemilih`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD KEY `id_kandidat_dipilih` (`id_kandidat_dipilih`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `kandidat`
--
ALTER TABLE `kandidat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pemilih`
--
ALTER TABLE `pemilih`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guru`
--
ALTER TABLE `guru`
  ADD CONSTRAINT `guru_ibfk_1` FOREIGN KEY (`id_kandidat_dipilih`) REFERENCES `kandidat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pemilih`
--
ALTER TABLE `pemilih`
  ADD CONSTRAINT `pemilih_ibfk_1` FOREIGN KEY (`id_kandidat_dipilih`) REFERENCES `kandidat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
