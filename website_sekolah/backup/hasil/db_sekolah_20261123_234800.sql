-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: db_sekolah
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
  `id_log` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int unsigned DEFAULT NULL,
  `aksi` varchar(100) NOT NULL,
  `ip_addr` varchar(45) DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `idx_log_user` (`id_user`),
  KEY `idx_log_waktu` (`waktu`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES (1,NULL,'LOGIN_GAGAL:guru:admin','::1','2026-11-10 01:57:05'),(2,NULL,'LOGIN_GAGAL:guru:guru01','::1','2026-11-10 01:57:28'),(3,2,'LOGIN_SUKSES:guru','::1','2026-11-10 01:58:40'),(4,2,'LOGIN_SUKSES:guru','::1','2026-11-23 13:11:28'),(5,2,'LOGOUT','::1','2026-11-23 13:46:54'),(6,10,'REGISTER','::1','2026-11-23 14:03:23'),(7,11,'REGISTER','::1','2026-11-23 14:03:45'),(8,NULL,'LOGIN_GAGAL:siswa:EnikePakpahan','::1','2026-11-23 16:35:41'),(9,NULL,'LOGIN_GAGAL:siswa:EnikePakpahan','::1','2026-11-23 16:36:31'),(10,NULL,'LOGIN_GAGAL:siswa:EnikePakpahan','::1','2026-11-23 16:36:39'),(11,14,'REGISTER','::1','2026-11-23 16:37:51'),(12,14,'LOGIN_SUKSES:siswa','::1','2026-11-23 16:38:09'),(13,14,'LOGOUT','::1','2026-11-23 16:39:23'),(14,15,'REGISTER','::1','2026-11-23 16:40:38'),(15,15,'LOGIN_SUKSES:siswa','::1','2026-11-23 16:40:54');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guru`
--

DROP TABLE IF EXISTS `guru`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guru` (
  `id_guru` int unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int unsigned NOT NULL,
  `nip` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_guru`),
  UNIQUE KEY `id_user` (`id_user`),
  UNIQUE KEY `nip` (`nip`),
  CONSTRAINT `fk_guru_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guru`
--

LOCK TABLES `guru` WRITE;
/*!40000 ALTER TABLE `guru` DISABLE KEYS */;
INSERT INTO `guru` VALUES (1,2,'198001012005011001','Budi Santoso','budi@sman-nusantara.sch.id',NULL),(2,3,'198507152010012002','Rina Wijaya','rina@sman-nusantara.sch.id',NULL);
/*!40000 ALTER TABLE `guru` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jadwal`
--

DROP TABLE IF EXISTS `jadwal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jadwal` (
  `id_jadwal` int unsigned NOT NULL AUTO_INCREMENT,
  `id_kelas` int unsigned NOT NULL,
  `id_mapel` int unsigned NOT NULL,
  `id_guru` int unsigned NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `ruang` varchar(30) NOT NULL,
  PRIMARY KEY (`id_jadwal`),
  KEY `id_kelas` (`id_kelas`),
  KEY `id_mapel` (`id_mapel`),
  KEY `id_guru` (`id_guru`),
  KEY `idx_jadwal_hari` (`hari`),
  CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id_mapel`),
  CONSTRAINT `jadwal_ibfk_3` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jadwal`
--

LOCK TABLES `jadwal` WRITE;
/*!40000 ALTER TABLE `jadwal` DISABLE KEYS */;
INSERT INTO `jadwal` VALUES (1,1,1,1,'Senin','07:00:00','08:30:00','R.101'),(2,1,2,2,'Senin','08:30:00','10:00:00','R.101'),(3,1,4,2,'Senin','10:15:00','11:45:00','Lab Komputer'),(4,1,3,1,'Selasa','07:00:00','08:30:00','R.101'),(5,1,1,1,'Selasa','08:30:00','10:00:00','R.101'),(6,1,2,2,'Selasa','10:15:00','11:45:00','R.101'),(7,1,4,2,'Rabu','07:00:00','09:15:00','Lab Komputer'),(8,1,1,1,'Rabu','09:30:00','11:00:00','R.101'),(9,1,2,2,'Kamis','07:00:00','08:30:00','R.101'),(10,1,3,1,'Kamis','08:30:00','10:00:00','R.101'),(11,1,1,1,'Kamis','10:15:00','11:45:00','R.101'),(12,1,4,2,'Jumat','07:00:00','08:30:00','Lab Komputer'),(13,1,3,1,'Jumat','08:30:00','10:00:00','R.101'),(14,2,1,1,'Senin','07:00:00','08:30:00','R.201'),(15,2,2,2,'Rabu','07:00:00','08:30:00','R.201'),(16,2,4,2,'Kamis','10:15:00','11:45:00','Lab Komputer');
/*!40000 ALTER TABLE `jadwal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kelas`
--

DROP TABLE IF EXISTS `kelas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kelas` (
  `id_kelas` int unsigned NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(20) NOT NULL,
  `tingkat` tinyint unsigned NOT NULL,
  `tahun_ajaran` varchar(9) NOT NULL,
  PRIMARY KEY (`id_kelas`),
  UNIQUE KEY `uq_kelas` (`nama_kelas`,`tahun_ajaran`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kelas`
--

LOCK TABLES `kelas` WRITE;
/*!40000 ALTER TABLE `kelas` DISABLE KEYS */;
INSERT INTO `kelas` VALUES (1,'X IPA 1',10,'2026/2027'),(2,'X IPS 1',10,'2026/2027'),(3,'XI IPA 1',11,'2026/2027');
/*!40000 ALTER TABLE `kelas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mapel`
--

DROP TABLE IF EXISTS `mapel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mapel` (
  `id_mapel` int unsigned NOT NULL AUTO_INCREMENT,
  `kode_mapel` varchar(10) NOT NULL,
  `nama_mapel` varchar(100) NOT NULL,
  PRIMARY KEY (`id_mapel`),
  UNIQUE KEY `kode_mapel` (`kode_mapel`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mapel`
--

LOCK TABLES `mapel` WRITE;
/*!40000 ALTER TABLE `mapel` DISABLE KEYS */;
INSERT INTO `mapel` VALUES (1,'MTK','Matematika'),(2,'BIN','Bahasa Indonesia'),(3,'BIG','Bahasa Inggris'),(4,'INF','Informatika');
/*!40000 ALTER TABLE `mapel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nilai`
--

DROP TABLE IF EXISTS `nilai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nilai` (
  `id_nilai` int unsigned NOT NULL AUTO_INCREMENT,
  `id_siswa` int unsigned NOT NULL,
  `id_mapel` int unsigned NOT NULL,
  `id_guru` int unsigned NOT NULL,
  `semester` tinyint unsigned NOT NULL,
  `nilai_angka` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id_nilai`),
  UNIQUE KEY `uq_nilai` (`id_siswa`,`id_mapel`,`semester`),
  KEY `id_mapel` (`id_mapel`),
  KEY `id_guru` (`id_guru`),
  CONSTRAINT `nilai_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE,
  CONSTRAINT `nilai_ibfk_2` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id_mapel`),
  CONSTRAINT `nilai_ibfk_3` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`),
  CONSTRAINT `nilai_chk_1` CHECK ((`nilai_angka` between 0 and 100))
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nilai`
--

LOCK TABLES `nilai` WRITE;
/*!40000 ALTER TABLE `nilai` DISABLE KEYS */;
INSERT INTO `nilai` VALUES (1,1,1,1,1,93.00),(2,1,1,1,2,87.60),(3,1,3,1,1,87.80),(4,1,3,1,2,85.20),(5,1,2,2,1,97.80),(6,1,2,2,2,78.50),(7,1,4,2,1,79.20),(8,1,4,2,2,90.40),(9,2,1,1,1,80.10),(10,2,1,1,2,76.30),(11,2,3,1,1,84.10),(12,2,3,1,2,68.90),(13,2,2,2,1,94.50),(14,2,2,2,2,87.70),(15,2,4,2,1,66.90),(16,2,4,2,2,95.10),(17,3,1,1,1,81.30),(18,3,1,1,2,70.40),(19,3,3,1,1,72.90),(20,3,3,1,2,70.70),(21,3,2,2,1,90.30),(22,3,2,2,2,67.40),(23,3,4,2,1,82.80),(24,3,4,2,2,72.20),(25,4,1,1,1,97.00),(26,4,1,1,2,66.60),(27,4,3,1,1,72.00),(28,4,3,1,2,97.20),(29,4,2,2,1,96.90),(30,4,2,2,2,97.10),(31,4,4,2,1,79.60),(32,4,4,2,2,84.70),(33,5,1,1,1,68.20),(34,5,1,1,2,67.10),(35,5,3,1,1,79.10),(36,5,3,1,2,89.60),(37,5,2,2,1,96.70),(38,5,2,2,2,93.10),(39,5,4,2,1,79.90),(40,5,4,2,2,95.50),(41,6,1,1,1,69.00),(42,6,1,1,2,75.80),(43,6,3,1,1,67.50),(44,6,3,1,2,86.90),(45,6,2,2,1,68.00),(46,6,2,2,2,78.40),(47,6,4,2,1,89.40),(48,6,4,2,2,74.60);
/*!40000 ALTER TABLE `nilai` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengumuman`
--

DROP TABLE IF EXISTS `pengumuman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengumuman` (
  `id_pengumuman` int unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int unsigned NOT NULL,
  `judul` varchar(150) NOT NULL,
  `isi` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pengumuman`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `pengumuman_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengumuman`
--

LOCK TABLES `pengumuman` WRITE;
/*!40000 ALTER TABLE `pengumuman` DISABLE KEYS */;
INSERT INTO `pengumuman` VALUES (1,1,'Selamat Datang di Website Sekolah','Website resmi SMA Nusantara kini aktif. Guru dan siswa dapat masuk melalui menu login masing-masing.','2026-11-10 01:58:15'),(2,1,'Jadwal Ujian Tengah Semester','UTS semester ganjil dimulai minggu depan.\nSiswa wajib membawa kartu ujian dan alat tulis.','2026-11-10 01:58:15'),(3,1,'Pengumpulan Nilai oleh Guru','Bapak/Ibu guru diharapkan menginput nilai ke sistem paling lambat akhir bulan ini.','2026-11-10 01:58:15'),(4,1,'Pemeliharaan Sistem','Backup database otomatis berjalan setiap pukul 02.00 WIB.','2026-11-10 01:58:15');
/*!40000 ALTER TABLE `pengumuman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `siswa`
--

DROP TABLE IF EXISTS `siswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `siswa` (
  `id_siswa` int unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int unsigned NOT NULL,
  `id_kelas` int unsigned DEFAULT NULL,
  `nis` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_siswa`),
  UNIQUE KEY `id_user` (`id_user`),
  UNIQUE KEY `nis` (`nis`),
  KEY `fk_siswa_kelas` (`id_kelas`),
  CONSTRAINT `fk_siswa_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE SET NULL,
  CONSTRAINT `fk_siswa_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siswa`
--

LOCK TABLES `siswa` WRITE;
/*!40000 ALTER TABLE `siswa` DISABLE KEYS */;
INSERT INTO `siswa` VALUES (1,4,1,'2026001','Siti Aminah','P',NULL,NULL),(2,5,1,'2026002','Ahmad Fauzi','L',NULL,NULL),(3,6,1,'2026003','Dewi Lestari','P',NULL,NULL),(4,7,1,'2026004','Rizky Pratama','L',NULL,NULL),(5,8,1,'2026005','Nadia Putri','P',NULL,NULL),(6,9,2,'2026006','Bagas Saputra','L',NULL,NULL),(11,14,NULL,'12345678','Enike LMP',NULL,NULL,NULL),(12,15,NULL,'87654321','Asep P',NULL,NULL,NULL);
/*!40000 ALTER TABLE `siswa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','guru','siswa') NOT NULL DEFAULT 'siswa',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$36M8Ma2E6xNmiP4Bi29Ppufm36tO.VbhaTp43dTwG19PDe/DKGTSq','admin','2026-11-10 01:58:14',NULL),(2,'guru01','$2y$10$5Wmnd8CfA2VguXmv3bsDbOX2XOoB0NJQvWGB91OzKZ3f.oNDc8ukW','guru','2026-11-10 01:58:14','2026-11-23 20:11:28'),(3,'guru02','$2y$10$bEWP8.2GgJkYYwGb3ztiGuZku9s8hV7fctT8orw807lvpwlXuBMye','guru','2026-11-10 01:58:14',NULL),(4,'siswa01','$2y$10$4dyfSOrOz962kWKVvxFm6eHZVUmSff.xS0S2wuV4u2W9cxnlh1dky','siswa','2026-11-10 01:58:14',NULL),(5,'siswa02','$2y$10$dNrhS4Z3n5cg7obP6mSg8OKyhAJgX3F5LNS/iMbeYd1w8R9ywAaNu','siswa','2026-11-10 01:58:14',NULL),(6,'siswa03','$2y$10$yxM.nDh0AI1F4VCjWikjuuQcwdGqsPzYFLAZl2moZ4t4OtYuecVPa','siswa','2026-11-10 01:58:14',NULL),(7,'siswa04','$2y$10$Z8JRSgGbHhl3x2fn5PzMXernmHSRNZ/3BPWL5gfTMRbzniu.eN/Wu','siswa','2026-11-10 01:58:14',NULL),(8,'siswa05','$2y$10$0ingXdvBOIyqZmebWVh01.wYDHiCa8X2cl2TWxtgLfhFIbcLxuLFK','siswa','2026-11-10 01:58:15',NULL),(9,'siswa06','$2y$10$LVa0UixRRkaAym7CLncbbeVVTy9j8IhA6KBbqdyr6Fagdi0ksc1Pi','siswa','2026-11-10 01:58:15',NULL),(14,'EnikePakpahan','$2y$10$8fzIdGmEOd1MiGd3/mMEleBi/kVl3Cg0RHkKfXU5AJcggiedUp/BS','siswa','2026-11-23 16:37:51','2026-11-23 23:38:09'),(15,'Asep','$2y$10$RMx7asLeX1OQANo9jeCmFO2GiYbyxv.CYYTxM9psB9K4aZ/A7tPY2','siswa','2026-11-23 16:40:38','2026-11-23 23:40:54');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'db_sekolah'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-23 23:48:01
