-- MySQL dump 10.13  Distrib 8.0.46, for Linux (aarch64)
--
-- Host: localhost    Database: mini_erp_direksi
-- ------------------------------------------------------
-- Server version	8.0.46

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
-- Table structure for table `aging_piutang`
--

DROP TABLE IF EXISTS `aging_piutang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aging_piutang` (
  `id_aging` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_dept` bigint unsigned NOT NULL,
  `periode` char(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` enum('hutang','piutang') COLLATE utf8mb4_unicode_ci NOT NULL,
  `bucket_umur` enum('0-30','31-60','61-90','>90') COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` decimal(18,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_aging`),
  UNIQUE KEY `uq_aging` (`id_dept`,`periode`,`jenis`,`bucket_umur`),
  KEY `idx_aging_periode` (`id_dept`,`periode`),
  CONSTRAINT `aging_piutang_id_dept_foreign` FOREIGN KEY (`id_dept`) REFERENCES `departemen` (`id_dept`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aging_piutang`
--

LOCK TABLES `aging_piutang` WRITE;
/*!40000 ALTER TABLE `aging_piutang` DISABLE KEYS */;
INSERT INTO `aging_piutang` VALUES (1,2,'2026-01','piutang','0-30',90000000.00),(2,2,'2026-01','hutang','0-30',120000000.00);
/*!40000 ALTER TABLE `aging_piutang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_user`
--

DROP TABLE IF EXISTS `app_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_user` (
  `id_user` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_role` bigint unsigned NOT NULL,
  `status_2fa` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `failed_login` tinyint NOT NULL DEFAULT '0',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `app_user_email_unique` (`email`),
  KEY `app_user_id_role_foreign` (`id_role`),
  CONSTRAINT `app_user_id_role_foreign` FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_user`
--

LOCK TABLES `app_user` WRITE;
/*!40000 ALTER TABLE `app_user` DISABLE KEYS */;
INSERT INTO `app_user` VALUES (1,'Budi Santoso','budi@perusahaan.co.id','$2y$12$1pK9ebqildgbB8HKkF8P0.O36/SERZ3Jo03BH57CgsaX5/lgdqF..',1,'aktif',0,0,1,'2026-06-08 05:04:44'),(2,'Sari Wijaya','sari@perusahaan.co.id','$2y$12$UZSwYZEc8qZQrGkP9E8b7OG0GPoOBb.QTf/gFGJd1.Xn78bdaQExm',2,'aktif',0,0,1,'2026-06-08 05:04:44'),(3,'Lina Hartono','lina@perusahaan.co.id','$2y$12$kPEQ4m91J5rqvbG9BzR/sup34Y0I/9fRpb1ueHelXLd/xsqZB4zY2',3,'aktif',0,0,1,'2026-06-08 05:04:44'),(4,'Andi Pratama','andi@perusahaan.co.id','$2y$12$WrhWoYyl7lF7dMU5jb8RL.Cx6V7hQb7kOXsRtAgG/.kHr4J5jjyi.',4,'aktif',0,0,1,'2026-06-08 05:04:44');
/*!40000 ALTER TABLE `app_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval`
--

DROP TABLE IF EXISTS `approval`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval` (
  `id_approval` bigint unsigned NOT NULL AUTO_INCREMENT,
  `no_po` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` bigint unsigned NOT NULL,
  `keputusan` enum('setuju','tolak') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perangkat_ip` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_link` enum('aktif','terpakai','kadaluarsa') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `waktu_putusan` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_approval`),
  UNIQUE KEY `approval_link_token_unique` (`link_token`),
  KEY `approval_no_po_foreign` (`no_po`),
  KEY `approval_id_user_foreign` (`id_user`),
  KEY `idx_appr_status` (`status_link`,`waktu_putusan`),
  CONSTRAINT `approval_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `app_user` (`id_user`),
  CONSTRAINT `approval_no_po_foreign` FOREIGN KEY (`no_po`) REFERENCES `purchase_order` (`no_po`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval`
--

LOCK TABLES `approval` WRITE;
/*!40000 ALTER TABLE `approval` DISABLE KEYS */;
/*!40000 ALTER TABLE `approval` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
  `id_log` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint unsigned DEFAULT NULL,
  `aksi` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modul` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_sebelum` json DEFAULT NULL,
  `data_sesudah` json DEFAULT NULL,
  `perangkat_ip` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `idx_audit_user` (`id_user`,`created_at`),
  KEY `idx_audit_modul` (`modul`,`created_at`),
  CONSTRAINT `audit_log_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `app_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES (1,1,'login','Auth',NULL,NULL,'192.168.65.1','2026-06-08 08:41:15'),(2,1,'login','Auth',NULL,NULL,'192.168.65.1','2026-06-08 09:11:44'),(3,1,'login','Auth',NULL,NULL,'192.168.65.1','2026-06-08 09:18:35'),(4,1,'login','Auth',NULL,NULL,'192.168.65.1','2026-06-08 09:47:25'),(5,1,'login','Auth',NULL,NULL,'192.168.65.1','2026-06-09 00:28:11'),(6,1,'login','Auth',NULL,NULL,'192.168.65.1','2026-06-23 11:20:24'),(7,1,'login','Auth',NULL,NULL,'172.19.0.1','2026-07-03 03:51:36');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`erp`@`%`*/ /*!50003 TRIGGER `trg_audit_no_update` BEFORE UPDATE ON `audit_log` FOR EACH ROW BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'audit_log bersifat permanen: UPDATE tidak diizinkan.';
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`erp`@`%`*/ /*!50003 TRIGGER `trg_audit_no_delete` BEFORE DELETE ON `audit_log` FOR EACH ROW BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'audit_log bersifat permanen: DELETE tidak diizinkan.';
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delegasi`
--

DROP TABLE IF EXISTS `delegasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `delegasi` (
  `id_delegasi` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user_asal` bigint unsigned NOT NULL,
  `id_user_ganti` bigint unsigned NOT NULL,
  `jenis_transaksi` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_delegasi`),
  KEY `delegasi_id_user_asal_foreign` (`id_user_asal`),
  KEY `delegasi_id_user_ganti_foreign` (`id_user_ganti`),
  CONSTRAINT `delegasi_id_user_asal_foreign` FOREIGN KEY (`id_user_asal`) REFERENCES `app_user` (`id_user`),
  CONSTRAINT `delegasi_id_user_ganti_foreign` FOREIGN KEY (`id_user_ganti`) REFERENCES `app_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delegasi`
--

LOCK TABLES `delegasi` WRITE;
/*!40000 ALTER TABLE `delegasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `delegasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departemen`
--

DROP TABLE IF EXISTS `departemen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departemen` (
  `id_dept` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_dept` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kepala_dept` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_dept`),
  UNIQUE KEY `departemen_nama_dept_unique` (`nama_dept`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departemen`
--

LOCK TABLES `departemen` WRITE;
/*!40000 ALTER TABLE `departemen` DISABLE KEYS */;
INSERT INTO `departemen` VALUES (1,'Procurement','Andi'),(2,'Keuangan','Lina'),(3,'Produksi','Joko');
/*!40000 ALTER TABLE `departemen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ekspor_log`
--

DROP TABLE IF EXISTS `ekspor_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ekspor_log` (
  `id_ekspor` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint unsigned NOT NULL,
  `jenis_laporan` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `periode` char(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `format` enum('pdf','excel') COLLATE utf8mb4_unicode_ci NOT NULL,
  `waktu_unduh` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_ekspor`),
  KEY `ekspor_log_id_user_foreign` (`id_user`),
  CONSTRAINT `ekspor_log_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `app_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ekspor_log`
--

LOCK TABLES `ekspor_log` WRITE;
/*!40000 ALTER TABLE `ekspor_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `ekspor_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hak_akses`
--

DROP TABLE IF EXISTS `hak_akses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hak_akses` (
  `id_akses` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint unsigned NOT NULL,
  `id_modul` bigint unsigned NOT NULL,
  `level` enum('lihat','ubah','setujui') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lihat',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_akses`),
  UNIQUE KEY `uq_hakakses` (`id_user`,`id_modul`),
  KEY `hak_akses_id_modul_foreign` (`id_modul`),
  CONSTRAINT `hak_akses_id_modul_foreign` FOREIGN KEY (`id_modul`) REFERENCES `modul` (`id_modul`),
  CONSTRAINT `hak_akses_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `app_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hak_akses`
--

LOCK TABLES `hak_akses` WRITE;
/*!40000 ALTER TABLE `hak_akses` DISABLE KEYS */;
/*!40000 ALTER TABLE `hak_akses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kinerja_operasional`
--

DROP TABLE IF EXISTS `kinerja_operasional`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kinerja_operasional` (
  `id_kinerja` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_dept` bigint unsigned NOT NULL,
  `periode` char(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` enum('pembelian','gudang','penjualan','produksi','sdm') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai_aktual` decimal(15,2) DEFAULT NULL,
  `target` decimal(15,2) DEFAULT NULL,
  `skor` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id_kinerja`),
  UNIQUE KEY `uq_kinerja` (`id_dept`,`periode`,`kategori`),
  CONSTRAINT `kinerja_operasional_id_dept_foreign` FOREIGN KEY (`id_dept`) REFERENCES `departemen` (`id_dept`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kinerja_operasional`
--

LOCK TABLES `kinerja_operasional` WRITE;
/*!40000 ALTER TABLE `kinerja_operasional` DISABLE KEYS */;
INSERT INTO `kinerja_operasional` VALUES (1,1,'2026-01','pembelian',92.00,90.00,92.00),(2,3,'2026-01','produksi',78.00,95.00,78.00);
/*!40000 ALTER TABLE `kinerja_operasional` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `laporan_keuangan`
--

DROP TABLE IF EXISTS `laporan_keuangan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `laporan_keuangan` (
  `id_laporan` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_dept` bigint unsigned NOT NULL,
  `periode` char(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pemasukan` decimal(18,2) NOT NULL DEFAULT '0.00',
  `pengeluaran` decimal(18,2) NOT NULL DEFAULT '0.00',
  `laba` decimal(18,2) GENERATED ALWAYS AS ((`pemasukan` - `pengeluaran`)) STORED,
  `persen_anggaran` decimal(5,2) DEFAULT NULL,
  `indikator_warna` enum('hijau','kuning','merah') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_laporan`),
  UNIQUE KEY `uq_lapkeu` (`id_dept`,`periode`),
  CONSTRAINT `laporan_keuangan_id_dept_foreign` FOREIGN KEY (`id_dept`) REFERENCES `departemen` (`id_dept`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `laporan_keuangan`
--

LOCK TABLES `laporan_keuangan` WRITE;
/*!40000 ALTER TABLE `laporan_keuangan` DISABLE KEYS */;
INSERT INTO `laporan_keuangan` (`id_laporan`, `id_dept`, `periode`, `pemasukan`, `pengeluaran`, `persen_anggaran`, `indikator_warna`) VALUES (1,2,'2026-01',1200000000.00,880000000.00,72.00,'kuning');
/*!40000 ALTER TABLE `laporan_keuangan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_01_01_100001_create_role_table',1),(5,'2026_01_01_100002_create_app_user_table',1),(6,'2026_01_01_100003_create_modul_table',1),(7,'2026_01_01_100004_create_hak_akses_table',1),(8,'2026_01_01_100005_create_departemen_table',1),(9,'2026_01_01_100006_create_supplier_table',1),(10,'2026_01_01_100007_create_purchase_order_table',1),(11,'2026_01_01_100008_create_po_item_table',1),(12,'2026_01_01_100009_create_approval_table',1),(13,'2026_01_01_100010_create_delegasi_table',1),(14,'2026_01_01_100011_create_laporan_keuangan_table',1),(15,'2026_01_01_100012_create_kinerja_operasional_table',1),(16,'2026_01_01_100013_create_notifikasi_table',1),(17,'2026_01_01_100014_create_audit_log_table',1),(18,'2026_01_01_100015_create_ekspor_log_table',1),(19,'2026_01_01_100016_create_otp_token_table',1),(20,'2026_01_01_100017_create_aging_piutang_table',1),(21,'2026_01_01_100018_create_usulan_anggaran_table',1),(22,'2026_01_01_100019_create_audit_immutable_triggers',1),(23,'2026_06_08_054801_create_personal_access_tokens_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modul`
--

DROP TABLE IF EXISTS `modul`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modul` (
  `id_modul` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_modul` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_modul`),
  UNIQUE KEY `modul_nama_modul_unique` (`nama_modul`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modul`
--

LOCK TABLES `modul` WRITE;
/*!40000 ALTER TABLE `modul` DISABLE KEYS */;
INSERT INTO `modul` VALUES (1,'Dashboard','Executive dashboard'),(2,'Approval','Persetujuan PO besar'),(3,'Keuangan','Laporan keuangan'),(4,'Operasional','Kinerja operasional'),(5,'Ekspor','Ekspor laporan');
/*!40000 ALTER TABLE `modul` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifikasi`
--

DROP TABLE IF EXISTS `notifikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifikasi` (
  `id_notif` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint unsigned NOT NULL,
  `jenis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pesan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level_kritis` enum('info','warning','kritis') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'warning',
  `sumber_modul` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_notif`),
  KEY `idx_notif_user` (`id_user`,`dibaca`),
  CONSTRAINT `notifikasi_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `app_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifikasi`
--

LOCK TABLES `notifikasi` WRITE;
/*!40000 ALTER TABLE `notifikasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifikasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `otp_token`
--

DROP TABLE IF EXISTS `otp_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `otp_token` (
  `id_otp` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint unsigned NOT NULL,
  `kode_otp` char(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dibuat_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expired_at` datetime NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_otp`),
  KEY `idx_otp_user` (`id_user`,`expired_at`),
  CONSTRAINT `otp_token_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `app_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `otp_token`
--

LOCK TABLES `otp_token` WRITE;
/*!40000 ALTER TABLE `otp_token` DISABLE KEYS */;
INSERT INTO `otp_token` VALUES (1,1,'382069','2026-06-08 08:39:27','2026-06-08 08:44:27',1),(2,1,'076895','2026-06-08 09:11:33','2026-06-08 09:16:33',1),(3,1,'288926','2026-06-08 09:18:22','2026-06-08 09:23:22',1),(4,1,'711487','2026-06-08 09:47:15','2026-06-08 09:52:15',1),(5,1,'626568','2026-06-09 00:27:59','2026-06-09 00:32:59',1),(6,1,'323718','2026-06-23 11:20:15','2026-06-23 11:25:15',1),(7,1,'582207','2026-07-03 03:51:11','2026-07-03 03:56:11',1);
/*!40000 ALTER TABLE `otp_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\AppUser',1,'api-token','008928f86de20ee63cefdb66a9975fade2bf78c263cba4a945b24e6a102b8b79','[\"*\"]','2026-06-08 08:43:10',NULL,'2026-06-08 08:41:15','2026-06-08 08:43:10'),(5,'App\\Models\\AppUser',1,'api-token','fde608ac37e96770bd3af240f2d2984ca133558efbc6f5815a892c12521383a9','[\"*\"]','2026-06-09 00:28:25',NULL,'2026-06-09 00:28:11','2026-06-09 00:28:25'),(6,'App\\Models\\AppUser',1,'api-token','c6ebc5386523e8982a6ee37443c4d5d1c655320ccfe389c21e9a790d65440876','[\"*\"]','2026-06-23 11:21:33',NULL,'2026-06-23 11:20:24','2026-06-23 11:21:33'),(7,'App\\Models\\AppUser',1,'api-token','a9f2278f61d9299ce7462710d51e84d9b694f906447e6937248c00b8744495a3','[\"*\"]','2026-07-03 03:51:36',NULL,'2026-07-03 03:51:36','2026-07-03 03:51:36');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `po_item`
--

DROP TABLE IF EXISTS `po_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `po_item` (
  `no_po` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_item` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` int NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  PRIMARY KEY (`no_po`,`nama_item`),
  CONSTRAINT `po_item_no_po_foreign` FOREIGN KEY (`no_po`) REFERENCES `purchase_order` (`no_po`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `po_item`
--

LOCK TABLES `po_item` WRITE;
/*!40000 ALTER TABLE `po_item` DISABLE KEYS */;
INSERT INTO `po_item` VALUES ('PO-001','Kertas A4',10,50000.00),('PO-001','Tinta',5,200000.00),('PO-002','Laptop',3,15000000.00);
/*!40000 ALTER TABLE `po_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order`
--

DROP TABLE IF EXISTS `purchase_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_order` (
  `no_po` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal` date NOT NULL,
  `nilai_po` decimal(15,2) NOT NULL,
  `id_supplier` bigint unsigned NOT NULL,
  `id_dept` bigint unsigned NOT NULL,
  `status_po` enum('draft','menunggu','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `dibuat_oleh` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`no_po`),
  KEY `purchase_order_id_supplier_foreign` (`id_supplier`),
  KEY `purchase_order_id_dept_foreign` (`id_dept`),
  KEY `idx_po_status` (`status_po`),
  KEY `idx_po_tanggal` (`tanggal`),
  CONSTRAINT `purchase_order_id_dept_foreign` FOREIGN KEY (`id_dept`) REFERENCES `departemen` (`id_dept`),
  CONSTRAINT `purchase_order_id_supplier_foreign` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order`
--

LOCK TABLES `purchase_order` WRITE;
/*!40000 ALTER TABLE `purchase_order` DISABLE KEYS */;
INSERT INTO `purchase_order` VALUES ('PO-001','2026-01-05',5000000.00,1,1,'menunggu','Andi','2026-06-08 05:04:44'),('PO-002','2026-01-06',45000000.00,2,1,'menunggu','Andi','2026-06-08 05:04:44');
/*!40000 ALTER TABLE `purchase_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role` (
  `id_role` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level_akses` enum('lihat','ubah','setujui') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lihat',
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `role_nama_role_unique` (`nama_role`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (1,'Direktur Utama','setujui','Pantau KPI & setujui PO besar'),(2,'Wakil Direktur','setujui','Pengganti approval'),(3,'Manager Keuangan','ubah','Suplai data P&L & cash flow'),(4,'Manager IT','ubah','Kelola keamanan & hak akses');
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('6pd8bVr3oxFtheH8dClm3WnV1xbKxtdwBAzxO5t6',NULL,'192.168.65.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:151.0) Gecko/20100101 Firefox/151.0','eyJfdG9rZW4iOiJXQzJlUlI0UjVselQ0dDZUQWZQeFl0aFI1WWtsQkRWV0JsSHQ3MXJPIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDgxIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1780921816),('8PqEs0JFDZwzFaM42LfZcDKWA3bXVUHVgTOFnCVU',NULL,'192.168.65.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:151.0) Gecko/20100101 Firefox/151.0','eyJfdG9rZW4iOiJaYmNCeVZQTDMya2h3YmZxY1lIMEdzTm41Y2FTelY0a3liWHFnSThLIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDgxIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1780898483),('bOB6Fs1WhpVxxOazLgBEedFPBCCJUoz40RFpbUkz',NULL,'172.19.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:152.0) Gecko/20100101 Firefox/152.0','eyJfdG9rZW4iOiJ2R1hUUmU5MHZENWV0ZHRkc0xISlhqcTdQaFFHRmlQcTJaQnI2VGdnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDgxIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1783050631),('V6xvnljS4MI9nRbGRolYPRKUiV3SbW2MJVF6r1wH',NULL,'192.168.65.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:152.0) Gecko/20100101 Firefox/152.0','eyJfdG9rZW4iOiJ1OWV6SWhVSElnZGRTZlFCYmROWm1WaFMwRVRLcHNid2t0U2IwM0VjIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDgxIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1782213602);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier`
--

DROP TABLE IF EXISTS `supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier` (
  `id_supplier` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_supplier` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kontak` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skor_supplier` decimal(4,2) DEFAULT NULL,
  PRIMARY KEY (`id_supplier`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier`
--

LOCK TABLES `supplier` WRITE;
/*!40000 ALTER TABLE `supplier` DISABLE KEYS */;
INSERT INTO `supplier` VALUES (1,'CV Maju','Jl. A No.1',NULL,4.50),(2,'PT Sentosa Abadi','Jl. B No.2',NULL,3.80);
/*!40000 ALTER TABLE `supplier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usulan_anggaran`
--

DROP TABLE IF EXISTS `usulan_anggaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usulan_anggaran` (
  `id_usulan` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_dept` bigint unsigned NOT NULL,
  `periode` char(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plafon_diajukan` decimal(18,2) NOT NULL,
  `diajukan_oleh` bigint unsigned NOT NULL,
  `disetujui_oleh` bigint unsigned DEFAULT NULL,
  `status` enum('menunggu','approved','revisi') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `catatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waktu_putusan` datetime DEFAULT NULL,
  PRIMARY KEY (`id_usulan`),
  UNIQUE KEY `uq_usulan` (`id_dept`,`periode`),
  KEY `usulan_anggaran_diajukan_oleh_foreign` (`diajukan_oleh`),
  KEY `usulan_anggaran_disetujui_oleh_foreign` (`disetujui_oleh`),
  KEY `idx_usulan_status` (`status`,`periode`),
  CONSTRAINT `usulan_anggaran_diajukan_oleh_foreign` FOREIGN KEY (`diajukan_oleh`) REFERENCES `app_user` (`id_user`),
  CONSTRAINT `usulan_anggaran_disetujui_oleh_foreign` FOREIGN KEY (`disetujui_oleh`) REFERENCES `app_user` (`id_user`),
  CONSTRAINT `usulan_anggaran_id_dept_foreign` FOREIGN KEY (`id_dept`) REFERENCES `departemen` (`id_dept`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usulan_anggaran`
--

LOCK TABLES `usulan_anggaran` WRITE;
/*!40000 ALTER TABLE `usulan_anggaran` DISABLE KEYS */;
INSERT INTO `usulan_anggaran` VALUES (1,3,'2026-02',850000000.00,1,NULL,'menunggu',NULL,NULL);
/*!40000 ALTER TABLE `usulan_anggaran` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-03  6:22:15
