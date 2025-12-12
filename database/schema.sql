/*M!999999\- enable the sandbox mode */ 
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(100) NOT NULL COMMENT 'login, logout, create_person, update_person, etc',
  `subject_type` varchar(100) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Datos adicionales en JSON' CHECK (json_valid(`properties`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `activity_log_user_id_index` (`user_id`),
  KEY `activity_log_action_index` (`action`),
  KEY `activity_log_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) unsigned DEFAULT NULL,
  `family_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(50) NOT NULL COMMENT 'BIRT, DEAT, MARR, DIV, BURI, BAPM, etc',
  `date` date DEFAULT NULL,
  `date_approx` tinyint(1) NOT NULL DEFAULT 0,
  `place` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `events_person_id_index` (`person_id`),
  KEY `events_family_id_index` (`family_id`),
  KEY `events_type_index` (`type`),
  CONSTRAINT `events_family_id_foreign` FOREIGN KEY (`family_id`) REFERENCES `families` (`id`) ON DELETE CASCADE,
  CONSTRAINT `events_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `families`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `families` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `gedcom_id` varchar(50) DEFAULT NULL COMMENT 'ID para compatibilidad GEDCOM (@F1@, etc)',
  `husband_id` bigint(20) unsigned DEFAULT NULL,
  `wife_id` bigint(20) unsigned DEFAULT NULL,
  `marriage_date` date DEFAULT NULL,
  `marriage_date_approx` tinyint(1) NOT NULL DEFAULT 0,
  `marriage_place` varchar(255) DEFAULT NULL,
  `divorce_date` date DEFAULT NULL,
  `divorce_place` varchar(255) DEFAULT NULL,
  `status` enum('married','divorced','widowed','separated','partners','annulled') NOT NULL DEFAULT 'married',
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `families_husband_id_index` (`husband_id`),
  KEY `families_wife_id_index` (`wife_id`),
  KEY `idx_families_spouses` (`husband_id`,`wife_id`),
  KEY `families_created_by_foreign` (`created_by`),
  CONSTRAINT `families_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `families_husband_id_foreign` FOREIGN KEY (`husband_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `families_wife_id_foreign` FOREIGN KEY (`wife_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `family_children`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `family_children` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `family_id` bigint(20) unsigned NOT NULL,
  `person_id` bigint(20) unsigned NOT NULL,
  `child_order` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT 'Orden de nacimiento',
  `relationship_type` enum('biological','adopted','foster','step') NOT NULL DEFAULT 'biological',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_family_child` (`family_id`,`person_id`),
  KEY `family_children_person_id_index` (`person_id`),
  CONSTRAINT `family_children_family_id_foreign` FOREIGN KEY (`family_id`) REFERENCES `families` (`id`) ON DELETE CASCADE,
  CONSTRAINT `family_children_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invitations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inviter_id` bigint(20) unsigned NOT NULL COMMENT 'Usuario que invita',
  `person_id` bigint(20) unsigned NOT NULL COMMENT 'Persona a la que se invita',
  `email` varchar(255) NOT NULL,
  `token` varchar(100) NOT NULL,
  `status` enum('pending','sent','accepted','declined','expired') NOT NULL DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invitations_token_unique` (`token`),
  KEY `invitations_email_index` (`email`),
  KEY `invitations_status_index` (`status`),
  KEY `invitations_inviter_id_foreign` (`inviter_id`),
  KEY `invitations_person_id_foreign` (`person_id`),
  CONSTRAINT `invitations_inviter_id_foreign` FOREIGN KEY (`inviter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invitations_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mediable_type` varchar(100) NOT NULL COMMENT 'App\\Models\\Person o App\\Models\\User',
  `mediable_id` bigint(20) unsigned NOT NULL,
  `type` enum('image','document','link') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(10) unsigned DEFAULT NULL COMMENT 'Tamano en bytes',
  `mime_type` varchar(100) DEFAULT NULL,
  `external_url` varchar(1000) DEFAULT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Foto principal del perfil',
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_media_mediable` (`mediable_type`,`mediable_id`),
  KEY `media_type_index` (`type`),
  KEY `media_created_by_index` (`created_by`),
  CONSTRAINT `media_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint(20) unsigned DEFAULT NULL COMMENT 'NULL para mensajes del sistema',
  `recipient_id` bigint(20) unsigned NOT NULL,
  `type` enum('invitation','consent_request','relationship_found','general','system','person_claim','person_merge') NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `related_person_id` bigint(20) unsigned DEFAULT NULL,
  `action_required` tinyint(1) NOT NULL DEFAULT 0,
  `action_status` enum('pending','accepted','denied','expired') DEFAULT NULL,
  `action_taken_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `messages_recipient_id_index` (`recipient_id`),
  KEY `idx_messages_unread` (`recipient_id`,`read_at`),
  KEY `messages_type_index` (`type`),
  KEY `messages_sender_id_index` (`sender_id`),
  KEY `idx_messages_action` (`recipient_id`,`action_required`,`action_status`),
  KEY `messages_related_person_id_foreign` (`related_person_id`),
  CONSTRAINT `messages_recipient_id_foreign` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_related_person_id_foreign` FOREIGN KEY (`related_person_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `persons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `gedcom_id` varchar(50) DEFAULT NULL COMMENT 'ID para compatibilidad GEDCOM (@I1@, etc)',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Si la persona tiene cuenta en el sistema',
  `first_name` varchar(100) NOT NULL,
  `patronymic` varchar(100) NOT NULL COMMENT 'Apellido paterno',
  `matronymic` varchar(100) DEFAULT NULL COMMENT 'Apellido materno',
  `nickname` varchar(100) DEFAULT NULL,
  `gender` enum('M','F','U') NOT NULL DEFAULT 'U' COMMENT 'M=Masculino, F=Femenino, U=Desconocido',
  `birth_date` date DEFAULT NULL,
  `birth_date_approx` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Fecha aproximada',
  `birth_place` varchar(255) DEFAULT NULL,
  `birth_country` varchar(100) DEFAULT NULL,
  `death_date` date DEFAULT NULL,
  `death_date_approx` tinyint(1) NOT NULL DEFAULT 0,
  `death_place` varchar(255) DEFAULT NULL,
  `death_country` varchar(100) DEFAULT NULL,
  `is_living` tinyint(1) NOT NULL DEFAULT 1,
  `is_minor` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Menor de 18 anos',
  `residence_place` varchar(255) DEFAULT NULL,
  `residence_country` varchar(100) DEFAULT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL COMMENT 'Email de contacto',
  `phone` varchar(50) DEFAULT NULL,
  `has_ethnic_heritage` tinyint(1) NOT NULL DEFAULT 0,
  `heritage_region` enum('central','dalmatia','slavonia','istria','other','unknown') DEFAULT NULL,
  `origin_town` varchar(255) DEFAULT NULL COMMENT 'Poblacion de origen',
  `migration_decade` varchar(20) DEFAULT NULL COMMENT 'Decada de migracion',
  `migration_destination` varchar(100) DEFAULT NULL COMMENT 'Primer pais de destino',
  `photo_path` varchar(500) DEFAULT NULL,
  `privacy_level` enum('private','family','community','public') NOT NULL DEFAULT 'family',
  `consent_status` enum('pending','approved','denied','not_required') NOT NULL DEFAULT 'not_required',
  `consent_requested_at` timestamp NULL DEFAULT NULL,
  `consent_responded_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `persons_user_id_index` (`user_id`),
  KEY `idx_persons_names` (`first_name`,`patronymic`),
  KEY `idx_persons_heritage` (`has_ethnic_heritage`,`heritage_region`),
  KEY `persons_is_living_index` (`is_living`),
  KEY `persons_created_by_index` (`created_by`),
  KEY `persons_updated_by_foreign` (`updated_by`),
  FULLTEXT KEY `idx_persons_fulltext` (`first_name`,`patronymic`,`matronymic`),
  CONSTRAINT `persons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `persons_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `persons_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `surname_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `surname_variants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) unsigned NOT NULL,
  `original_surname` varchar(100) NOT NULL COMMENT 'Apellido original',
  `variant_1` varchar(100) DEFAULT NULL COMMENT 'Primera variante',
  `variant_2` varchar(100) DEFAULT NULL COMMENT 'Segunda variante',
  `notes` text DEFAULT NULL COMMENT 'Notas sobre el cambio',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `surname_variants_person_id_index` (`person_id`),
  KEY `idx_sv_surnames` (`original_surname`,`variant_1`,`variant_2`),
  CONSTRAINT `surname_variants_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tree_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tree_access` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` bigint(20) unsigned NOT NULL COMMENT 'Dueno del arbol',
  `accessor_id` bigint(20) unsigned NOT NULL COMMENT 'Usuario con acceso',
  `access_level` enum('view_basic','view_full','edit') NOT NULL DEFAULT 'view_basic',
  `include_documents` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Puede ver documentos e imagenes',
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tree_access` (`owner_id`,`accessor_id`),
  KEY `tree_access_accessor_id_index` (`accessor_id`),
  CONSTRAINT `tree_access_accessor_id_foreign` FOREIGN KEY (`accessor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tree_access_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `person_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Referencia a la persona que representa este usuario',
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `language` enum('es','en') NOT NULL DEFAULT 'es',
  `privacy_level` enum('direct_family','extended_family','selected_users','community') NOT NULL DEFAULT 'direct_family',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `confirmation_code` varchar(10) DEFAULT NULL,
  `first_login_completed` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `login_attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_person_id_index` (`person_id`),
  CONSTRAINT `users_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

/*M!999999\- enable the sandbox mode */ 
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2014_10_12_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2014_10_12_100000_create_password_reset_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2024_01_01_000001_create_persons_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2024_01_01_000002_create_families_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2024_01_01_000003_create_family_children_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2024_01_01_000004_create_surname_variants_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2024_01_01_000005_create_media_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2024_01_01_000006_create_messages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2024_01_01_000007_create_invitations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2024_01_01_000008_create_tree_access_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2024_01_01_000009_create_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2024_01_01_000010_create_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2024_01_01_000011_create_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2024_01_01_000012_add_foreign_keys',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_12_11_182400_add_is_admin_to_users_table',2);
