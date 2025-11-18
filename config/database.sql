
-- -- Membuat tabel kandidat
-- CREATE TABLE kandidat (
--   id INT(11) NOT NULL AUTO_INCREMENT,
--   nama_lengkap VARCHAR(255) NOT NULL,
--   nis VARCHAR(50) NOT NULL,
--   visi_misi TEXT NOT NULL,
--   video_path VARCHAR(255) DEFAULT NULL, 
--   foto_path VARCHAR(255) NOT NULL,
--   jumlah_suara INT(11) DEFAULT 0,
--   PRIMARY KEY (id)
-- )


-- CREATE TABLE IF NOT EXISTS `kandidat` (
--   `id` INT(11) NOT NULL AUTO_INCREMENT,
--   `nama_lengkap` VARCHAR(255) NOT NULL,
--   `nis` VARCHAR(50) NOT NULL UNIQUE,
--   `visi` TEXT NOT NULL,
--   `misi` TEXT NOT NULL,
--   `video_path` VARCHAR(255) DEFAULT NULL, 
--   `foto_path` VARCHAR(255) NOT NULL,
--   `kejar` VARCHAR(255) NOT NULL,
--   `usia` INT(11) DEFAULT NULL,
--   `jumlah_suara` INT(11) DEFAULT 0,
--   PRIMARY KEY (`id`)
-- );
-- -- Membuat tabel pemilih
-- CREATE TABLE pemilih (
--   id INT(11) NOT NULL AUTO_INCREMENT,
--   nis VARCHAR(50) NOT NULL,
--   nama_lengkap VARCHAR(255) NOT NULL,
--   tanggal_lahir VARCHAR(8) NOT NULL COMMENT 'Format: DD/MM/YY',
--   status_memilih TINYINT(1) DEFAULT 0 COMMENT '0=Belum, 1=Sudah',
--   PRIMARY KEY (id),
--   UNIQUE KEY nis (nis)
-- );

-- ALTER TABLE `pemilih` ADD FOREIGN KEY (`id_kandidat_dipilih`) REFERENCES `kandidat`(`id`);

-- ALTER TABLE `pemilih` ADD `id_kandidat_dipilih` INT(11) NULL DEFAULT NULL AFTER `status_memilih`;
-- -- Membuat tabel admin
-- CREATE TABLE admin (
--   id INT(11) NOT NULL AUTO_INCREMENT,
--   username VARCHAR(100) NOT NULL,
--   password VARCHAR(255) NOT NULL,
--   PRIMARY KEY (id),
--   UNIQUE KEY username (username)
-- ) 

-- -- === CONTOH DATA AWAL ===

-- -- Menambahkan contoh data admin
-- -- Passwordnya adalah 'admin123', ini adalah hash-nya
-- INSERT INTO admin (username, password) VALUES
-- ('admin', '$2y$10$8glLM.iNLCebL/AOqREoW.Jnwf3BmHW9sIzGabg7SW8LBFPGPIoaa');

-- -- Menambahkan contoh data pemilih
-- INSERT INTO pemilih (nis, nama_lengkap, tanggal_lahir) VALUES
-- ('1001', 'Budi Santoso', '15/08/06'),
-- ('1002', 'Citra Lestari', '22/01/07'),
-- ('1003', 'Dewi Anggraini', '30/11/06');

-- -- Menambahkan contoh data kandidat
-- INSERT INTO `kandidat` (`id`, `nama_lengkap`, `nis`, `visi`, `misi`, `video_path`, `foto_path`, `kejar`, `usia`, `jumlah_suara`) VALUES
-- (1, 'Qanita Malila Oufwa Pahingguan', '2001', 'Mewujudkan sekolah yang berbasis teknologi, berintegritas, dan transparan.', 'Membangun sistem voting online yang transparan dan aman.; Mengadakan workshop coding dan pengembangan aplikasi untuk siswa.; Meningkatkan literasi digital di lingkungan sekolah.', '../assets/videos/compressed_video_1.mp4', '../assets/image/qani.png', 'DKV', 17, 0),
-- (2, 'Aura Anastasya Putri Fiara', '2002', 'Menciptakan lingkungan sekolah yang kreatif dan inovatif.', 'Mengadakan workshop seni dan event untuk mengembangkan bakat siswa.; Membentuk komunitas kreatif di setiap kelas.; Menjalin kolaborasi dengan pihak luar untuk event sekolah.', '../assets/videos/compressed_video_2.mp4', '../assets/image/aura.png', 'RPL', 18, 0),
-- (3, 'Safdiza Azizi', '2003', 'Meningkatkan kesadaran lingkungan di sekolah.', 'Mengadakan program daur ulang dan penanaman pohon rutin.; Mengampanyekan penggunaan tumbler dan mengurangi sampah plastik.; Membangun taman sekolah yang produktif.', '../assets/videos/compressed_video_3.mp4', '../assets/image/diza.png', 'RPL', 17, 0),
-- (4, 'Ananda Dio Pratama Harahap', '2004', 'Menjadikan OSIS sebagai wadah aspirasi siswa.', 'Membentuk forum diskusi bulanan untuk semua siswa.; Menyediakan kotak saran online yang dapat diakses setiap saat.; Mengadakan pertemuan rutin antara perwakilan kelas dengan pihak sekolah.', '../assets/videos/compressed_video_4.mp4', '../assets/image/dio.png', 'RPL', 17, 0);

-- CREATE TABLE `kepuasan_debat` (
--     `id` INT(11) NOT NULL AUTO_INCREMENT,
--     `id_pemilih` INT(11) NOT NULL,
--     `id_kandidat` INT(11) NOT NULL,
--     `nilai_kepuasan` INT(1) NOT NULL COMMENT 'Skala 1-5',
--     `sesi_ke` INT(1) NOT NULL,
--     `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     PRIMARY KEY (`id`),
--     UNIQUE KEY `unique_vote_per_session` (`id_pemilih`, `sesi_ke`),
--     FOREIGN KEY (`id_pemilih`) REFERENCES `pemilih`(`id`),
--     FOREIGN KEY (`id_kandidat`) REFERENCES `kandidat`(`id`)
-- );


/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.11-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: db_pemilos
-- ------------------------------------------------------
-- Server version	10.11.11-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES
(1,'admin','$2y$10$8glLM.iNLCebL/AOqREoW.Jnwf3BmHW9sIzGabg7SW8LBFPGPIoaa');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guru`
--

DROP TABLE IF EXISTS `guru`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `guru` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(255) NOT NULL,
  `nik` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `status_memilih` tinyint(1) DEFAULT 0 COMMENT '0=Belum, 1=Sudah',
  `id_kandidat_dipilih` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nik` (`nik`),
  KEY `id_kandidat_dipilih` (`id_kandidat_dipilih`),
  CONSTRAINT `guru_ibfk_1` FOREIGN KEY (`id_kandidat_dipilih`) REFERENCES `kandidat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guru`
--

LOCK TABLES `guru` WRITE;
/*!40000 ALTER TABLE `guru` DISABLE KEYS */;
INSERT INTO `guru` VALUES
(1,'Guru Test','13.22.16.181','132216181','Guru Matematika',0,NULL,'2025-10-08 14:48:19');
/*!40000 ALTER TABLE `guru` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kandidat`
--

DROP TABLE IF EXISTS `kandidat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kandidat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(255) NOT NULL,
  `nis` varchar(50) NOT NULL,
  `visi` text NOT NULL,
  `misi` text NOT NULL,
  `video_path` varchar(255) DEFAULT NULL,
  `foto_path` varchar(255) NOT NULL,
  `kejar` varchar(255) NOT NULL,
  `usia` int(11) DEFAULT NULL,
  `jumlah_suara` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nis` (`nis`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kandidat`
--

LOCK TABLES `kandidat` WRITE;
/*!40000 ALTER TABLE `kandidat` DISABLE KEYS */;
INSERT INTO `kandidat` VALUES
(1,'Qanita Malila Oufwa Pahingguan','2001','Mewujudkan sekolah yang berbasis teknologi, berintegritas, dan transparan.','Membangun sistem voting online yang transparan dan aman.; Mengadakan workshop coding dan pengembangan aplikasi untuk siswa.; Meningkatkan literasi digital di lingkungan sekolah.','../assets/videos/video_1_1755678443.mp4','../assets/image/kandidat_1_1756265965.png','Desain Komunikasi Visual',17,2),
(2,'Aura Anastasya Putri Fiara','2002','Menciptakan lingkungan sekolah yang kreatif dan inovatif.','Mengadakan workshop seni dan event untuk mengembangkan bakat siswa.; Membentuk komunitas kreatif di setiap kelas.; Menjalin kolaborasi dengan pihak luar untuk event sekolah.','../assets/videos/video_2_1756106736.mp4','../assets/image/kandidat_2_1756266166.jpg','Rekayasa Perangkat Lunak',17,1),
(3,'Safdiza Azizi','2003','Meningkatkan kesadaran lingkungan di sekolah.','Mengadakan program daur ulang dan penanaman pohon rutin.; Mengampanyekan penggunaan tumbler dan mengurangi sampah plastik.; Membangun taman sekolah yang produktif.','../assets/videos/video_3_1755879775.mp4','../assets/image/kandidat_3_1756265984.png','Rekayasa Perangkat Lunak',17,0),
(4,'Samudera','2004','Menjadikan OSIS sebagai wadah aspirasi siswa.','Membentuk forum diskusi bulanan untuk semua siswa.; Menyediakan kotak saran online yang dapat diakses setiap saat.; Mengadakan pertemuan rutin antara perwakilan kelas dengan pihak sekolah.','../assets/videos/video_4_1755879707.mp4','../assets/image/kandidat_4_1756266005.jpg','Rekayasa Perangkat Lunak',17,0);
/*!40000 ALTER TABLE `kandidat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kepuasan_debat`
--

DROP TABLE IF EXISTS `kepuasan_debat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kepuasan_debat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pemilih` int(11) NOT NULL,
  `id_kandidat` int(11) NOT NULL,
  `nilai_kepuasan` int(11) NOT NULL COMMENT 'Skala 1-5',
  `sesi_ke` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote_per_session` (`id_pemilih`,`sesi_ke`),
  KEY `id_kandidat` (`id_kandidat`),
  CONSTRAINT `kepuasan_debat_ibfk_1` FOREIGN KEY (`id_pemilih`) REFERENCES `pemilih` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `kepuasan_debat_ibfk_2` FOREIGN KEY (`id_kandidat`) REFERENCES `kandidat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kepuasan_debat`
--

LOCK TABLES `kepuasan_debat` WRITE;
/*!40000 ALTER TABLE `kepuasan_debat` DISABLE KEYS */;
/*!40000 ALTER TABLE `kepuasan_debat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pemilih`
--

DROP TABLE IF EXISTS `pemilih`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pemilih` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nis` varchar(50) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `tanggal_lahir` varchar(20) NOT NULL COMMENT 'Format: DD/MM/YY',
  `status_memilih` tinyint(1) DEFAULT 0 COMMENT '0=Belum, 1=Sudah',
  `id_kandidat_dipilih` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nis` (`nis`),
  KEY `id_kandidat_dipilih` (`id_kandidat_dipilih`),
  CONSTRAINT `pemilih_ibfk_1` FOREIGN KEY (`id_kandidat_dipilih`) REFERENCES `kandidat` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pemilih`
--

LOCK TABLES `pemilih` WRITE;
/*!40000 ALTER TABLE `pemilih` DISABLE KEYS */;
INSERT INTO `pemilih` VALUES
(1,'1001','Budi Santoso','15/08/06',0,NULL),
(2,'1002','Citra Lestari','22/01/07',0,NULL),
(3,'1003','Dewi Anggraini','30/11/06',0,NULL),
(5,'23173100','Reisan Adrefa','19/06/08',0,NULL),
(6,'23173056','Rafa Adya','26/01/08',1,NULL),
(7,'23173053','Naufal','27/04/08',0,NULL),
(8,'23173063','Safdiza Azizi','15/05/08',1,NULL);
/*!40000 ALTER TABLE `pemilih` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-15 10:52:38
