/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-12.2.2-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: teranga_azur_db
-- ------------------------------------------------------
-- Server version	12.2.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `activites`
--

DROP TABLE IF EXISTS `activites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_activite` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `duree_heures` int(11) NOT NULL,
  `prix_par_personne` decimal(10,2) NOT NULL,
  `lieu_depart` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activites`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `activites` WRITE;
/*!40000 ALTER TABLE `activites` DISABLE KEYS */;
INSERT INTO `activites` VALUES
(1,'Sortie pêche en mer','Partez en mer avec nos pêcheurs locaux pour une matinée de pêche traditionnelle au large de Mbour. Prise garantie ou non, l\'expérience est inoubliable !',4,25000.00,'Port de Mbour','2026-05-01 13:01:54',NULL,NULL),
(2,'Tour en pirogue sur le Saloum','Explorez les mangroves du Delta du Saloum en pirogue traditionnelle. Observation d\'oiseaux rares et découverte des villages de pêcheurs.',6,30000.00,'Foundiougne','2026-05-01 13:01:54',NULL,NULL),
(3,'Visite de la Réserve de Bandia','Safari dans la réserve naturelle de Bandia : girafes, rhinocéros, buffles, et de nombreuses espèces endémiques vous attendent.',5,40000.00,'Bandia, Mbour','2026-05-01 13:01:54',NULL,NULL),
(4,'Coucher de soleil à cheval','Balade à cheval sur la plage au coucher du soleil, un moment magique et romantique face à l\'Atlantique.',2,20000.00,'Saly Portudal','2026-05-01 13:01:54',NULL,NULL),
(5,'Initiation à la cuisine sénégalaise','Apprenez à préparer le thiéboudienne, le yassa et autres plats emblématiques avec une cuisinière locale.',3,15000.00,'Saly Portudal','2026-05-01 13:01:54',NULL,NULL),
(6,'Excursion Ile de Gorée','Visite de l\'île historique de Gorée, classée au patrimoine mondial de l\'UNESCO. Transfert depuis Dakar inclus.',8,45000.00,'Dakar','2026-05-01 13:01:54',NULL,NULL),
(7,'Quad & buggy sur les plages','Aventure tout-terrain sur les plages et dunes de la Petite Côte en quad ou buggy.',2,35000.00,'Saly Portudal','2026-05-01 13:01:54',NULL,NULL),
(8,'Plongée sous-marine','Découvrez les fonds marins de la Petite Côte avec nos moniteurs PADI certifiés. Matériel inclus.',3,50000.00,'Mbour','2026-05-01 13:01:54',NULL,NULL);
/*!40000 ALTER TABLE `activites` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `pays` varchar(100) DEFAULT NULL,
  `role` enum('client','admin') DEFAULT 'client',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES
(1,'Demo','Utilisateur','demo@teranga-azur.sn','$2y$10$placeholder_hash','+221 77 000 00 00','Sénégal','client','2026-05-01 13:01:54'),
(2,'Admin','Teranga','admin@teranga-azur.sn','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','+221 77 000 00 01','Sénégal','admin','2026-05-01 13:01:54');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `images_activites`
--

DROP TABLE IF EXISTS `images_activites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `images_activites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activite_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `ordre` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `activite_id` (`activite_id`),
  CONSTRAINT `1` FOREIGN KEY (`activite_id`) REFERENCES `activites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `images_activites`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `images_activites` WRITE;
/*!40000 ALTER TABLE `images_activites` DISABLE KEYS */;
INSERT INTO `images_activites` VALUES
(1,8,'images/activites/activite_8_69f4c2fb62fe8.webp',0,'2026-05-01 15:12:59'),
(2,6,'images/activites/activite_6_69f4c350a99f9.jpg',0,'2026-05-01 15:14:24'),
(3,2,'images/activites/activite_2_69f4d0665ad45.webp',0,'2026-05-01 16:10:14'),
(4,3,'images/activites/activite_3_69f4d09d7efec.png',0,'2026-05-01 16:11:09'),
(5,4,'images/activites/activite_4_69f4d1296ddc8.webp',0,'2026-05-01 16:13:29'),
(6,5,'images/activites/activite_5_69f4d1571cd29.webp',0,'2026-05-01 16:14:15'),
(7,7,'images/activites/activite_7_69f4d1837b983.jpg',0,'2026-05-01 16:14:59'),
(8,1,'images/activites/activite_1_69f4d1b1a1427.webp',0,'2026-05-01 16:15:45');
/*!40000 ALTER TABLE `images_activites` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `images_villas`
--

DROP TABLE IF EXISTS `images_villas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `images_villas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `villa_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `ordre` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `villa_id` (`villa_id`),
  CONSTRAINT `1` FOREIGN KEY (`villa_id`) REFERENCES `villas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `images_villas`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `images_villas` WRITE;
/*!40000 ALTER TABLE `images_villas` DISABLE KEYS */;
INSERT INTO `images_villas` VALUES
(5,2,'images/villas/villa_2_69f4c0511b59f.png',0),
(6,1,'images/villas/villa_1_69f4c0db7d872.png',0),
(7,5,'images/villas/villa_5_69f4c124ef260.png',0);
/*!40000 ALTER TABLE `images_villas` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `options_choisies`
--

DROP TABLE IF EXISTS `options_choisies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `options_choisies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_villa_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantite` int(11) DEFAULT 1,
  `nb_jours` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `reservation_villa_id` (`reservation_villa_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `1` FOREIGN KEY (`reservation_villa_id`) REFERENCES `reservations_villas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `2` FOREIGN KEY (`service_id`) REFERENCES `services_optionnels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `options_choisies`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `options_choisies` WRITE;
/*!40000 ALTER TABLE `options_choisies` DISABLE KEYS */;
/*!40000 ALTER TABLE `options_choisies` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `item_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `date_arrivee` date NOT NULL,
  `date_depart` date NOT NULL,
  `nombre_nuits` int(11) DEFAULT 0,
  `nombre_personnes` int(11) DEFAULT 1,
  `prix_total` decimal(10,2) NOT NULL,
  `statut` varchar(50) DEFAULT 'en_attente',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `reservations_activites`
--

DROP TABLE IF EXISTS `reservations_activites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations_activites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `activite_id` int(11) NOT NULL,
  `date_activite` date NOT NULL,
  `nb_participants` int(11) NOT NULL,
  `prix_total_activite` decimal(10,2) NOT NULL,
  `statut` varchar(50) DEFAULT 'En attente',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `activite_id` (`activite_id`),
  CONSTRAINT `1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `2` FOREIGN KEY (`activite_id`) REFERENCES `activites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations_activites`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `reservations_activites` WRITE;
/*!40000 ALTER TABLE `reservations_activites` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservations_activites` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `reservations_villas`
--

DROP TABLE IF EXISTS `reservations_villas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations_villas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `villa_id` int(11) NOT NULL,
  `date_arrivee` date NOT NULL,
  `date_depart` date NOT NULL,
  `nb_voyageurs` int(11) NOT NULL,
  `prix_total_sejour` decimal(12,2) NOT NULL,
  `statut` varchar(50) DEFAULT 'En attente',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `villa_id` (`villa_id`),
  CONSTRAINT `1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `2` FOREIGN KEY (`villa_id`) REFERENCES `villas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations_villas`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `reservations_villas` WRITE;
/*!40000 ALTER TABLE `reservations_villas` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservations_villas` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `services_optionnels`
--

DROP TABLE IF EXISTS `services_optionnels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `services_optionnels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_service` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `prix_journalier` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services_optionnels`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `services_optionnels` WRITE;
/*!40000 ALTER TABLE `services_optionnels` DISABLE KEYS */;
INSERT INTO `services_optionnels` VALUES
(1,'Chef cuisinier privé','Un chef expérimenté prépare vos repas dans la villa, avec menus personnalisés.',50000.00),
(2,'Transfert aéroport','Navette privée depuis/vers l\'aéroport de Dakar. Véhicule climatisé.',35000.00),
(3,'Service de ménage quotidien','Nettoyage complet de la villa chaque matin par notre équipe.',15000.00),
(4,'Location de voiture avec chauffeur','Berline climatisée avec chauffeur bilingue pour vos déplacements.',45000.00),
(5,'Baby-sitting certifié','Garde d\'enfants par une nurse diplômée et expérimentée.',20000.00);
/*!40000 ALTER TABLE `services_optionnels` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `villas`
--

DROP TABLE IF EXISTS `villas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `villas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `prix_par_nuit` decimal(10,2) NOT NULL,
  `capacite_max` int(11) NOT NULL,
  `chambres` int(11) NOT NULL,
  `piscine` tinyint(1) DEFAULT 0,
  `localisation` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `villas`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `villas` WRITE;
/*!40000 ALTER TABLE `villas` DISABLE KEYS */;
INSERT INTO `villas` VALUES
(1,'Villa Océane Prestige','Magnifique villa de luxe face à l\'Atlantique, avec piscine à débordement et vue panoramique sur l\'océan. Décorations africaines contemporaines, salon ouvert sur la terrasse et cuisine entièrement équipée.',350000.00,12,5,1,'Saly Portudal, Mbour','2026-05-01 13:01:54',NULL,NULL),
(2,'Villa Teranga Royale','Grande villa familiale au cœur de la Petite Côte, entourée d\'un jardin tropical luxuriant. Piscine privée, barbecue extérieur et accès direct à la plage de sable blanc.',280000.00,16,7,1,'Ngaparou, Mbour','2026-05-01 13:01:54',NULL,NULL),
(5,'Villa Bleu Azur','Villa contemporaine aux tons bleus et blancs, à 50 mètres de la plage. Piscine chauffée, salle de sport et service de conciergerie 24h/24.',420000.00,14,6,1,'Saly Portudal, Mbour','2026-05-01 13:01:54',NULL,NULL);
/*!40000 ALTER TABLE `villas` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-05-01 19:02:05
