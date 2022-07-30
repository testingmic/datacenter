-- MariaDB dump 10.19  Distrib 10.4.24-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: datacenterbank
-- ------------------------------------------------------
-- Server version	10.4.24-MariaDB

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
-- Table structure for table `constituency`
--

DROP TABLE IF EXISTS `constituency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `constituency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(6) DEFAULT 'GH',
  `region_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=296 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `constituency`
--
--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(6) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `capital` varchar(64) DEFAULT NULL,
  `flag` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` VALUES (1,'GH','Ghana',NULL,NULL,NULL);
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `districts`
--

DROP TABLE IF EXISTS `districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `districts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(6) DEFAULT NULL,
  `district_code` varchar(16) DEFAULT NULL,
  `region_id` varchar(16) DEFAULT NULL,
  `counstituency_id` varchar(16) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `district_category` varchar(64) DEFAULT NULL,
  `district_capital` varchar(64) DEFAULT NULL,
  `chief_executive` varchar(255) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=253 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `districts`
--
--
-- Table structure for table `health_diseases`
--

DROP TABLE IF EXISTS `health_diseases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `health_diseases` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(300) DEFAULT NULL,
  `name_slug` varchar(255) DEFAULT NULL,
  `generic_name` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `causes` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `medications` varchar(2000) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive','in-review','unverified') DEFAULT 'in-review',
  PRIMARY KEY (`id`),
  KEY `name_slug` (`name_slug`),
  KEY `status` (`status`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=321 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `health_facilities`
--

DROP TABLE IF EXISTS `health_facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `health_facilities` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `country_code` varchar(16) DEFAULT NULL,
  `region_id` varchar(16) DEFAULT NULL,
  `constituency_id` varchar(16) DEFAULT NULL,
  `district_id` varchar(16) DEFAULT NULL,
  `district_name` varchar(64) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `name_slug` varchar(255) DEFAULT NULL,
  `facility_type` varchar(32) DEFAULT NULL,
  `services` text DEFAULT NULL,
  `specialty` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `ghanapostgps` varchar(32) DEFAULT NULL,
  `contact` varchar(500) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `status` enum('active','inactive','in-review','unverified') NOT NULL DEFAULT 'in-review',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `country_code` (`country_code`),
  KEY `region_id` (`region_id`),
  KEY `district_id` (`district_id`),
  KEY `constituency_id` (`constituency_id`),
  KEY `facility_type` (`facility_type`),
  KEY `ghanapostgps` (`ghanapostgps`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3536 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `health_professionals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `health_professionals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `facility_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `name_slug` varchar(255) DEFAULT NULL,
  `specialty` varchar(2000) DEFAULT NULL,
  `awards` varchar(2000) DEFAULT NULL,
  `description` varchar(2000) DEFAULT NULL,
  `status` enum('active','inactive','in-review','unverified') NOT NULL DEFAULT 'in-review',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `health_professionals`
--

LOCK TABLES `health_professionals` WRITE;
/*!40000 ALTER TABLE `health_professionals` DISABLE KEYS */;
INSERT INTO `health_professionals` VALUES (1,'1','Emmanuel Obeng',NULL,'Software Developer',NULL,NULL,'active','2022-07-29 00:04:51','2022-07-29 00:04:51'),(2,'1','Angelina Obeng','angelina-obeng','Seamstress',NULL,NULL,'active','2022-07-29 00:04:51','2022-07-29 00:04:51');
/*!40000 ALTER TABLE `health_professionals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(6) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `capital` varchar(64) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regions`
--

LOCK TABLES `regions` WRITE;
/*!40000 ALTER TABLE `regions` DISABLE KEYS */;
INSERT INTO `regions` VALUES (1,'GH','Western','Sekondi-Takoradi\n',NULL),(2,'GH','Western North','Sefwi Wiawso',NULL),(3,'GH','Central','Cape Coast',NULL),(4,'GH','Greater Accra','Accra',NULL),(5,'GH','Volta','Ho',NULL),(6,'GH','Bono East','Techiman',NULL),(7,'GH','Oti','Dambai',NULL),(8,'GH','Savannah','Damango',NULL),(9,'GH','Eastern','Koforidua',NULL),(10,'GH','Northern','Tamale',NULL),(11,'GH','Ashanti','Kumasi',NULL),(12,'GH','North East','Nareligu',NULL),(13,'GH','Bono','Goaso',NULL),(14,'GH','Upper West','Wa',NULL),(15,'GH','Ahafo','Sunyani',NULL),(16,'GH','Upper East','Bolgatanga',NULL);
/*!40000 ALTER TABLE `regions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `permissions` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Emmanuel Obeng','testpassword',NULL,'$2y$10$542KJLKdkdflkk.mNb4L94Yo4x/V9fVgJhARz6LeGbTpbS','active','{\"health/facilities\":{\"list\": 1}}','2022-07-30 23:32:57','2021-02-03 23:35:00','2021-02-03 23:35:00');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_tokens`
--

DROP TABLE IF EXISTS `users_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(32) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expired_at` datetime DEFAULT NULL,
  `expiry_timestamp` varchar(16) DEFAULT NULL,
  `status` varchar(12) DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_tokens`
--

LOCK TABLES `users_tokens` WRITE;
/*!40000 ALTER TABLE `users_tokens` DISABLE KEYS */;
INSERT INTO `users_tokens` VALUES (1,'1','MTpwVTRldHEwenFnN0lqMVZwRTlNU0xvQ0ZKYnZlNk9BWGlTMzlSczV0Nm1Ecmc4RmNhbVlmc01rWHVsTEh2TnhE','2022-07-29 08:07:04','2022-07-29 08:07:00','1659125220','inactive'),(2,'1','MTpVUExKZGFnTjhZVnlLTXZFdWFrNm9IU0lRMFdwaXgwOUJ5c1BtelFlNGZiYkZ6bFhUaWpNT21zZGozMTJXQUFa','2022-07-29 08:15:12','2022-07-29 20:15:12','1659125712','inactive'),(3,'1','MTpwbHRudHhPTXhqU0Nzcjg5U3cydXZHTkVJQVpXUFV5d0o2Sm4xYVdwbHZoUmJ5UWhrMW9UVkRLMzRaOTB6Z2VC','2022-07-29 22:51:17','2022-07-30 10:51:17','1659178277','inactive'),(4,'1','MTozNXFqbWRJd1RZZzJieHRIZkNxWjRqRmkwRWMwRzdtOVJGd2N5c08xT0k4TmVLTEJSYWZvbk5NZ1RsNmt2VkdK','2022-07-30 22:54:46','2022-07-31 10:54:46','1659264886','active');
/*!40000 ALTER TABLE `users_tokens` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-07-30 23:38:08
