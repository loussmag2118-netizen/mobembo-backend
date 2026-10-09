-- MySQL dump 10.13  Distrib 9.6.0, for Win64 (x86_64)
--
-- Host: 136.108.6.227    Database: mobembo_line_mobile
-- ------------------------------------------------------
-- Server version	8.4.11-google

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
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ '';

--
-- Table structure for table `agences`
--

DROP TABLE IF EXISTS `agences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom_agence` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` enum('Actif','Bloqué') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Actif',
  `date_partenariat` date NOT NULL,
  `superadmin_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agences_nom_agence_unique` (`nom_agence`),
  KEY `agences_superadmin_id_foreign` (`superadmin_id`),
  CONSTRAINT `agences_superadmin_id_foreign` FOREIGN KEY (`superadmin_id`) REFERENCES `super_admins` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agences`
--

LOCK TABLES `agences` WRITE;
/*!40000 ALTER TABLE `agences` DISABLE KEYS */;
/*!40000 ALTER TABLE `agences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bus`
--

DROP TABLE IF EXISTS `bus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agence_id` bigint unsigned NOT NULL,
  `immatriculation` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modele` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacite_totale` int NOT NULL,
  `statut` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Disponible',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bus_agence_immat_unique` (`agence_id`,`immatriculation`),
  KEY `bus_statut_index` (`statut`),
  CONSTRAINT `bus_agence_id_foreign` FOREIGN KEY (`agence_id`) REFERENCES `agences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bus`
--

LOCK TABLES `bus` WRITE;
/*!40000 ALTER TABLE `bus` DISABLE KEYS */;
/*!40000 ALTER TABLE `bus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
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
  `expiration` int NOT NULL,
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
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
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
-- Table structure for table `gares`
--

DROP TABLE IF EXISTS `gares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gares` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agence_id` bigint unsigned NOT NULL,
  `nom_gare` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ville` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quartier` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_gare_agence` (`agence_id`,`nom_gare`,`ville`),
  CONSTRAINT `gares_agence_id_foreign` FOREIGN KEY (`agence_id`) REFERENCES `agences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gares`
--

LOCK TABLES `gares` WRITE;
/*!40000 ALTER TABLE `gares` DISABLE KEYS */;
/*!40000 ALTER TABLE `gares` ENABLE KEYS */;
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
  `attempts` tinyint unsigned NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_07_07_134353_create_super_admins_table',1),(5,'2026_07_07_134354_create_agences_table',1),(6,'2026_07_07_134354_create_utilisateurs_table',1),(7,'2026_07_07_134355_create_bus_table',1),(8,'2026_07_07_134355_create_gares_table',1),(9,'2026_07_07_134356_1_create_vague_departs_table',999),(10,'2026_07_07_134356_create_trajets_table',1000),(11,'2026_07_07_134357_create_planification_voyages_table',1001),(12,'2026_07_07_134358_create_tickets_table',1001);
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
-- Table structure for table `planification_voyages`
--

DROP TABLE IF EXISTS `planification_voyages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `planification_voyages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agence_id` bigint unsigned NOT NULL,
  `trajet_id` bigint unsigned NOT NULL,
  `bus_id` bigint unsigned NOT NULL,
  `vague_depart_id` bigint unsigned NOT NULL,
  `date_voyage` date NOT NULL,
  `nombre_tickets_total` int DEFAULT NULL,
  `places_disponibles` int NOT NULL DEFAULT '0',
  `statut_voyage` enum('Programmation','En cours','Terminé','Annulé') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Programmation',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_planification_bus_date` (`bus_id`,`date_voyage`,`vague_depart_id`),
  KEY `planification_voyages_agence_id_foreign` (`agence_id`),
  KEY `planification_voyages_trajet_id_foreign` (`trajet_id`),
  KEY `planification_voyages_vague_depart_id_foreign` (`vague_depart_id`),
  KEY `planification_voyages_date_voyage_index` (`date_voyage`),
  KEY `planification_voyages_statut_voyage_index` (`statut_voyage`),
  CONSTRAINT `planification_voyages_agence_id_foreign` FOREIGN KEY (`agence_id`) REFERENCES `agences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `planification_voyages_bus_id_foreign` FOREIGN KEY (`bus_id`) REFERENCES `bus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `planification_voyages_trajet_id_foreign` FOREIGN KEY (`trajet_id`) REFERENCES `trajets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `planification_voyages_vague_depart_id_foreign` FOREIGN KEY (`vague_depart_id`) REFERENCES `vague_departs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_places_disponibles` CHECK ((`places_disponibles` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `planification_voyages`
--

LOCK TABLES `planification_voyages` WRITE;
/*!40000 ALTER TABLE `planification_voyages` DISABLE KEYS */;
/*!40000 ALTER TABLE `planification_voyages` ENABLE KEYS */;
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
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `super_admins`
--

DROP TABLE IF EXISTS `super_admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `super_admins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `super_admins_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `super_admins`
--

LOCK TABLES `super_admins` WRITE;
/*!40000 ALTER TABLE `super_admins` DISABLE KEYS */;
/*!40000 ALTER TABLE `super_admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agence_id` bigint unsigned NOT NULL,
  `planification_voyage_id` bigint unsigned DEFAULT NULL,
  `agent_vendeur_id` bigint unsigned DEFAULT NULL,
  `agent_controleur_id` bigint unsigned DEFAULT NULL,
  `utilisateur_id` bigint unsigned DEFAULT NULL,
  `gare_arrivee_id` bigint unsigned DEFAULT NULL,
  `reference_ticket` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qr_code_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_passager` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom_passager` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone_passager` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville_depart` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville_arrivee` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quartier` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom_trajet` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_bus` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horaire` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom_vague` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre_places_reservees` int NOT NULL DEFAULT '1',
  `montant_total` decimal(10,2) NOT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `statut_paiement` enum('En_Attente','Paye','Echoue','Rembourse') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'En_Attente',
  `statut_embarquement` enum('En_Attente','Present','Absent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'En_Attente',
  `present` tinyint(1) DEFAULT NULL,
  `mode_paiement` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone_paiement` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_paiement` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_paiement` datetime DEFAULT NULL,
  `date_reservation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tickets_reference_ticket_unique` (`reference_ticket`),
  UNIQUE KEY `tickets_qr_code_token_unique` (`qr_code_token`),
  KEY `tickets_agent_vendeur_id_foreign` (`agent_vendeur_id`),
  KEY `tickets_agent_controleur_id_foreign` (`agent_controleur_id`),
  KEY `tickets_utilisateur_id_foreign` (`utilisateur_id`),
  KEY `tickets_gare_arrivee_id_foreign` (`gare_arrivee_id`),
  KEY `tickets_agence_id_index` (`agence_id`),
  KEY `tickets_planification_voyage_id_index` (`planification_voyage_id`),
  KEY `tickets_statut_paiement_index` (`statut_paiement`),
  KEY `tickets_statut_embarquement_index` (`statut_embarquement`),
  KEY `tickets_date_paiement_index` (`date_paiement`),
  CONSTRAINT `tickets_agence_id_foreign` FOREIGN KEY (`agence_id`) REFERENCES `agences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tickets_agent_controleur_id_foreign` FOREIGN KEY (`agent_controleur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_agent_vendeur_id_foreign` FOREIGN KEY (`agent_vendeur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_gare_arrivee_id_foreign` FOREIGN KEY (`gare_arrivee_id`) REFERENCES `gares` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_planification_voyage_id_foreign` FOREIGN KEY (`planification_voyage_id`) REFERENCES `planification_voyages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_utilisateur_id_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_nombre_places` CHECK ((`nombre_places_reservees` > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trajets`
--

DROP TABLE IF EXISTS `trajets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trajets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agence_id` bigint unsigned NOT NULL,
  `ville_depart` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ville_arrivee` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gare_depart_id` bigint unsigned NOT NULL,
  `gare_arrivee_id` bigint unsigned NOT NULL,
  `bus_id` bigint unsigned DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_trajet_agence` (`agence_id`,`gare_depart_id`,`gare_arrivee_id`),
  KEY `trajets_gare_depart_id_foreign` (`gare_depart_id`),
  KEY `trajets_gare_arrivee_id_foreign` (`gare_arrivee_id`),
  KEY `trajets_bus_id_index` (`bus_id`),
  CONSTRAINT `trajets_agence_id_foreign` FOREIGN KEY (`agence_id`) REFERENCES `agences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trajets_bus_id_foreign` FOREIGN KEY (`bus_id`) REFERENCES `bus` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trajets_gare_arrivee_id_foreign` FOREIGN KEY (`gare_arrivee_id`) REFERENCES `gares` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trajets_gare_depart_id_foreign` FOREIGN KEY (`gare_depart_id`) REFERENCES `gares` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trajets`
--

LOCK TABLES `trajets` WRITE;
/*!40000 ALTER TABLE `trajets` DISABLE KEYS */;
/*!40000 ALTER TABLE `trajets` ENABLE KEYS */;
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
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utilisateurs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agence_id` bigint unsigned NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('Admin','Agent') COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_acces_agent` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` enum('actif','suspendu') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'actif',
  `compte_confirme` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `utilisateurs_telephone_unique` (`telephone`),
  UNIQUE KEY `utilisateurs_email_unique` (`email`),
  UNIQUE KEY `utilisateurs_code_acces_agent_unique` (`code_acces_agent`),
  KEY `utilisateurs_agence_id_foreign` (`agence_id`),
  CONSTRAINT `utilisateurs_agence_id_foreign` FOREIGN KEY (`agence_id`) REFERENCES `agences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateurs`
--

LOCK TABLES `utilisateurs` WRITE;
/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;
/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vague_departs`
--

DROP TABLE IF EXISTS `vague_departs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vague_departs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agence_id` bigint unsigned NOT NULL,
  `trajet_id` bigint unsigned DEFAULT NULL,
  `heure_depart` time NOT NULL,
  `nom_vague` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vague_departs_agence_id_foreign` (`agence_id`),
  CONSTRAINT `vague_departs_agence_id_foreign` FOREIGN KEY (`agence_id`) REFERENCES `agences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vague_departs`
--

LOCK TABLES `vague_departs` WRITE;
/*!40000 ALTER TABLE `vague_departs` DISABLE KEYS */;
/*!40000 ALTER TABLE `vague_departs` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-09 11:42:38
