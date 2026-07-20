-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: laravel_monitoring
-- ------------------------------------------------------
-- Server version	8.4.3

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
INSERT INTO `cache` VALUES ('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba','i:1;',1784515421),('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer','i:1784515421;',1784515421);
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
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_06_22_022749_create_services_table',1),(5,'2026_06_22_022759_create_contacts_table',1),(6,'2026_06_22_022808_create_service_logs_table',1),(7,'2026_06_22_022814_create_smoke_devices_table',1),(8,'2026_06_22_022820_create_smoke_logs_table',1),(9,'2026_06_23_064147_add_last_status_notified_to_smoke_devices',2),(10,'2026_06_26_163506_add_deleted_at_to_contacts_table',2),(11,'2026_06_28_083725_add_username_to_users_table',2),(12,'2026_06_28_085256_create_personal_access_tokens_table',2),(13,'2026_06_29_035630_add_status_change_fields_to_service_logs_table',3),(14,'2026_07_01_210429_add_action_to_service_logs_table',4),(15,'2026_07_03_001854_fix_smoke_logs_foreign_key',5),(16,'2026_07_04_143846_add_check_columns_to_services_and_logs',6),(17,'2026_07_15_093317_add_wa_interval_to_services_table',7),(18,'2026_07_15_201355_add_wa_interval_to_services_table',8),(19,'2026_07_19_035614_add_interval_fields_to_services_table',9),(20,'2026_07_19_142959_remove_threshold_from_smoke_devices',10),(21,'2026_07_19_143114_remove_threshold_from_smoke_devices',10);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (3,'App\\Models\\User',4,'api-token','776611f0cd57197539dbfda52fd817f9270f948fa30252775cb4f227f25d0080','[\"*\"]','2026-07-10 13:57:14',NULL,'2026-07-10 13:34:29','2026-07-10 13:57:14');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_logs`
--

DROP TABLE IF EXISTS `service_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `response_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `response_time` double DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checked_at` timestamp NULL DEFAULT NULL,
  `is_status_change` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Menandakan apakah ini adalah perubahan status',
  `previous_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Status sebelumnya sebelum perubahan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_logs_is_status_change_index` (`is_status_change`),
  KEY `service_logs_service_id_is_status_change_index` (`service_id`,`is_status_change`),
  CONSTRAINT `service_logs_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=605 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_logs`
--

LOCK TABLES `service_logs` WRITE;
/*!40000 ALTER TABLE `service_logs` DISABLE KEYS */;
INSERT INTO `service_logs` VALUES (596,108,'UP','200',0.27,'Service berjalan normal - Pengguna bisa akses','-','2026-07-19 15:10:16',0,NULL,'2026-07-19 15:09:29','2026-07-19 15:10:16'),(597,108,'DOWN','404',0.27,'Halaman tidak ditemukan - Pengguna TIDAK bisa akses','Periksa URL endpoint','2026-07-19 15:35:27',0,NULL,'2026-07-19 15:10:37','2026-07-19 15:35:27'),(598,108,'UP','200',0.3,'Service berjalan normal - Pengguna bisa akses','-','2026-07-19 15:35:51',0,NULL,'2026-07-19 15:35:51','2026-07-19 15:35:51'),(599,108,'DOWN','503',0.3,'Service Unavailable - Pengguna TIDAK bisa akses','Cek maintenance / scale up resource','2026-07-19 15:38:24',0,NULL,'2026-07-19 15:36:30','2026-07-19 15:38:24'),(600,108,'UP','403',0.29,'Forbidden - Pengguna perlu izin - Masih bisa akses','Cek izin akses','2026-07-19 15:40:06',0,NULL,'2026-07-19 15:38:48','2026-07-19 15:40:06'),(601,108,'DOWN','503',0.18,'Service Unavailable - Pengguna TIDAK bisa akses','Cek maintenance / scale up resource','2026-07-19 15:43:02',0,NULL,'2026-07-19 15:41:49','2026-07-19 15:43:02'),(602,108,'UP','200',0.17,'Service berjalan normal - Pengguna bisa akses','-','2026-07-19 15:44:02',0,NULL,'2026-07-19 15:44:02','2026-07-19 15:44:02'),(603,108,'DOWN','503',0.24,'Service Unavailable - Pengguna TIDAK bisa akses','Cek maintenance / scale up resource','2026-07-19 16:00:34',0,NULL,'2026-07-19 15:45:02','2026-07-19 16:00:34'),(604,108,'UP','201',0.29,'Service berjalan normal - Pengguna bisa akses','-','2026-07-19 16:01:38',0,NULL,'2026-07-19 16:01:03','2026-07-19 16:01:38');
/*!40000 ALTER TABLE `service_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('http','ping') COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_status` enum('UP','WARNING','DOWN','UNKNOWN') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UNKNOWN',
  `last_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_response_time` double DEFAULT NULL,
  `last_message` text COLLATE utf8mb4_unicode_ci,
  `last_check_at` timestamp NULL DEFAULT NULL,
  `last_wa_sent_at` timestamp NULL DEFAULT NULL,
  `last_wa_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_interval_checked_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu terakhir pengecekan interval',
  `last_interval_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Status service saat interval terakhir',
  `interval_wa_sent_in_this_cycle` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Apakah WA sudah terkirim di interval ini?',
  `wa_interval_minutes` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (108,'test','http://103.151.63.68:8024/','http','UP','201',0.29,'Service berjalan normal - Pengguna bisa akses','2026-07-19 16:01:38','2026-07-19 16:01:03',NULL,'2026-07-19 16:01:03','UP',0,3,'2026-07-19 15:09:29','2026-07-19 16:01:38');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
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
INSERT INTO `sessions` VALUES ('EtcOknlKCzTgmiKYu9vJPbeFkbnb7Pxx9t6dRe8S',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJMNDVnbk1kbU9XNHNZZlhQdzM0VnJGbWlLY0UxSDBIbWZYNGV3b1hYIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDAifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJkYXNoYm9hcmQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1784514459),('icCn67s1L6QD8O0OUFmAo7TTw8payDypiB3HiV5v',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJQcEd3VzhGSVVOVmwxSndnRk0zTEdiSENWb0ZwR2k3eVJXaW05R2U0IiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDAifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJkYXNoYm9hcmQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1784514458),('k51lEzMypT9W74QZw04bRp4SVOtytwdhTwo1DtLS',4,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJLQmFqZ2ZsVDhxekliQVF3UHVDRXEzaEY5NktEREY4ZW41RHBPQU5TIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9zbW9rZS1kZXRlY3RvciIsInJvdXRlIjoic21va2UifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6NH0=',1784515381);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smoke_devices`
--

DROP TABLE IF EXISTS `smoke_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smoke_devices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `smoke_value` double NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NORMAL',
  `device_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OFFLINE',
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_status_notified` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NORMAL',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smoke_devices`
--

LOCK TABLES `smoke_devices` WRITE;
/*!40000 ALTER TABLE `smoke_devices` DISABLE KEYS */;
INSERT INTO `smoke_devices` VALUES (1,'ESP32-Smoke','Ruang Server',1000,'DANGER','ONLINE','2026-07-20 02:42:42',1,'2026-07-01 15:59:24','2026-07-20 02:42:42','NORMAL');
/*!40000 ALTER TABLE `smoke_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smoke_logs`
--

DROP TABLE IF EXISTS `smoke_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smoke_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `smoke_device_id` bigint unsigned NOT NULL,
  `smoke_value` int NOT NULL,
  `status` enum('NORMAL','WARNING','DANGER','OFFLINE','ONLINE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `smoke_logs_smoke_device_id_foreign` (`smoke_device_id`),
  CONSTRAINT `smoke_logs_smoke_device_id_foreign` FOREIGN KEY (`smoke_device_id`) REFERENCES `smoke_devices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smoke_logs`
--

LOCK TABLES `smoke_logs` WRITE;
/*!40000 ALTER TABLE `smoke_logs` DISABLE KEYS */;
INSERT INTO `smoke_logs` VALUES (1,1,600,'NORMAL','✅ Kondisi aman (600 ppm)','2026-07-10 14:56:12','2026-07-10 14:59:04'),(2,1,800,'WARNING','⚠️ Asap terdeteksi! 700 ppm - Waspada!','2026-07-10 14:59:29','2026-07-11 06:48:01'),(3,1,4000,'DANGER','🔥 Asap tinggi! 1000 ppm - Segera periksa!','2026-07-11 06:58:48','2026-07-11 07:10:42'),(4,1,100,'NORMAL','✅ Kondisi aman (100 ppm)','2026-07-11 07:28:58','2026-07-11 07:28:58'),(5,1,100,'OFFLINE','⚠️ ESP OFFLINE selama 4.9338843 menit - Cek power, WiFi, dan koneksi internet!','2026-07-11 07:33:56','2026-07-11 07:43:38'),(6,1,800,'WARNING','⚠️ Asap terdeteksi! 800 ppm - Waspada!','2026-07-11 07:50:29','2026-07-11 07:50:29'),(7,1,700,'OFFLINE','⚠️ ESP OFFLINE selama 8.1490160833333 menit - Cek power, WiFi, dan koneksi internet!','2026-07-11 07:58:39','2026-07-11 08:04:07'),(8,1,1000,'DANGER','🔥 Asap tinggi! 1000 ppm - Segera periksa!','2026-07-11 08:04:53','2026-07-11 08:04:53'),(9,1,700,'WARNING','⚠️ Asap terdeteksi! 700 ppm - Waspada!','2026-07-11 08:06:42','2026-07-11 08:06:42'),(10,1,700,'OFFLINE','⚠️ ESP OFFLINE selama 7.8429279333333 menit - Cek power, WiFi, dan koneksi internet!','2026-07-11 08:14:33','2026-07-11 08:14:33'),(11,1,1000,'DANGER','🔥 Asap tinggi! 1000 ppm - Segera periksa!','2026-07-11 09:38:48','2026-07-11 09:38:48'),(12,1,200,'NORMAL','✅ Kondisi aman (100 ppm)','2026-07-11 09:39:34','2026-07-11 09:40:07'),(13,1,700,'WARNING','⚠️ Asap terdeteksi! 700 ppm - Waspada!','2026-07-11 09:40:45','2026-07-11 09:40:45'),(14,1,100,'NORMAL','✅ Kondisi aman (100 ppm)','2026-07-11 09:41:05','2026-07-11 09:41:05'),(15,1,1500,'DANGER','🔥 Asap tinggi! 1000 ppm - Segera periksa!','2026-07-11 13:54:59','2026-07-11 14:15:22'),(16,1,700,'WARNING','⚠️ Asap terdeteksi! 700 ppm - Waspada!','2026-07-11 18:07:50','2026-07-11 18:07:50'),(17,1,600,'NORMAL','✅ Kondisi aman (500 ppm)','2026-07-12 08:14:21','2026-07-12 08:17:21'),(18,1,700,'WARNING','⚠️ Asap terdeteksi! 700 ppm - Waspada!','2026-07-12 08:26:13','2026-07-12 08:26:13'),(19,1,1000,'DANGER','🔥 Asap tinggi! 1000 ppm - Segera periksa!','2026-07-12 08:49:51','2026-07-12 08:49:51'),(20,1,900,'WARNING','⚠️ Asap terdeteksi! 700 ppm - Waspada!','2026-07-12 08:50:40','2026-07-12 08:51:57'),(21,1,1000,'DANGER','🔥 Asap tinggi! 1000 ppm - Segera periksa!','2026-07-12 08:57:09','2026-07-12 08:57:09'),(22,1,700,'WARNING','⚠️ Asap terdeteksi! 700 ppm - Waspada!','2026-07-12 09:11:08','2026-07-12 09:11:08'),(23,1,1000,'DANGER','🔥 Asap tinggi! 1000 ppm - Segera periksa!','2026-07-12 09:11:41','2026-07-12 09:11:41'),(24,1,700,'WARNING','⚠️ Asap terdeteksi! 700 ppm - Waspada!','2026-07-12 09:11:56','2026-07-12 09:11:56'),(25,1,1000,'DANGER','🔥 Asap tinggi! 1000 ppm - Segera periksa!','2026-07-12 09:16:38','2026-07-12 09:16:38'),(26,1,800,'WARNING','⚠️ Asap terdeteksi! 700 ppm - Waspada!','2026-07-12 09:16:53','2026-07-12 09:41:05'),(27,1,1000,'DANGER','🔥 Asap tinggi! 1000 ppm - Segera periksa!','2026-07-12 09:41:17','2026-07-12 09:41:17'),(28,1,700,'WARNING','⚠️ Asap terdeteksi! 700 ppm - Waspada!','2026-07-12 09:50:42','2026-07-12 09:50:42'),(29,1,1000,'DANGER','🔥 Asap tinggi! 1000 ppm - Segera periksa!','2026-07-12 09:51:57','2026-07-13 02:06:00'),(30,1,700,'WARNING','⚠️ Asap terdeteksi! - Waspada!','2026-07-14 15:24:13','2026-07-14 15:24:28'),(31,1,2000,'DANGER','🔥 Asap tinggi!  - Segera periksa!','2026-07-19 06:58:32','2026-07-19 08:14:48'),(32,1,850,'WARNING','⚠️ Asap terdeteksi! ADC: 850','2026-07-20 02:29:06','2026-07-20 02:29:06'),(33,1,1000,'DANGER','🔥 Asap tinggi! ADC: 1000','2026-07-20 02:42:41','2026-07-20 02:42:41');
/*!40000 ALTER TABLE `smoke_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','Administrator','admin@monitoring.com',NULL,'$2y$12$Zt8LSE5Srwwj4QeaF3hxx.xrdkgt58AuVsHNOVPIAwW9QRozNKr6W',NULL,'2026-06-28 01:54:39','2026-06-28 01:54:39'),(3,'Dwianti','Administrator','dwianti@monitoring.com',NULL,'$2y$12$dNpiYKqy2.3MQLAQrZaEI.7h6y4Y2GQBzpWX9YEuBKSHB4HIOsc0C',NULL,'2026-06-28 02:16:19','2026-06-28 02:16:19'),(4,'ismi','Ismi','admin@example.com',NULL,'$2y$12$GW0E5A07oR.BSiivHRxuVes8630IBjmZM8ebi7mr5X4.ByGTFRsuy','zy4DIBoVhM0IfvZaEQ8ER7RmjqqBfPCEbOH87LHyCIQ8Q9y9osHdtQsDJXnj','2026-07-04 07:19:32','2026-07-04 07:19:32');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-20  9:53:02
