-- MySQL dump 10.13  Distrib 8.3.0, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: vitegourmand
-- ------------------------------------------------------
-- Server version	8.3.0

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
-- Table structure for table `allergens`
--

DROP TABLE IF EXISTS `allergens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `allergens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_67F79FB4EA750E8` (`label`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `allergens`
--

LOCK TABLES `allergens` WRITE;
/*!40000 ALTER TABLE `allergens` DISABLE KEYS */;
INSERT INTO `allergens` (`id`, `label`) VALUES (5,'Arachides');
INSERT INTO `allergens` (`id`, `label`) VALUES (9,'Céleri');
INSERT INTO `allergens` (`id`, `label`) VALUES (2,'Crustacés');
INSERT INTO `allergens` (`id`, `label`) VALUES (8,'Fruits à coque');
INSERT INTO `allergens` (`id`, `label`) VALUES (16,'Gingembre');
INSERT INTO `allergens` (`id`, `label`) VALUES (1,'Gluten');
INSERT INTO `allergens` (`id`, `label`) VALUES (7,'Lait');
INSERT INTO `allergens` (`id`, `label`) VALUES (13,'Lupin');
INSERT INTO `allergens` (`id`, `label`) VALUES (14,'Mollusques');
INSERT INTO `allergens` (`id`, `label`) VALUES (10,'Moutarde');
INSERT INTO `allergens` (`id`, `label`) VALUES (15,'Noix de cajou');
INSERT INTO `allergens` (`id`, `label`) VALUES (3,'Oeufs');
INSERT INTO `allergens` (`id`, `label`) VALUES (4,'Poisson');
INSERT INTO `allergens` (`id`, `label`) VALUES (11,'Sésame');
INSERT INTO `allergens` (`id`, `label`) VALUES (6,'Soja');
INSERT INTO `allergens` (`id`, `label`) VALUES (12,'Sulfites');
/*!40000 ALTER TABLE `allergens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dish_allergens`
--

DROP TABLE IF EXISTS `dish_allergens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dish_allergens` (
  `dish_id` int NOT NULL,
  `allergen_id` int NOT NULL,
  PRIMARY KEY (`dish_id`,`allergen_id`),
  KEY `IDX_BD8EDBE56E775A4A` (`allergen_id`),
  CONSTRAINT `FK_BD8EDBE5148EB0CB` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_BD8EDBE56E775A4A` FOREIGN KEY (`allergen_id`) REFERENCES `allergens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dish_allergens`
--

LOCK TABLES `dish_allergens` WRITE;
/*!40000 ALTER TABLE `dish_allergens` DISABLE KEYS */;
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (1,1);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (11,1);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (12,1);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (14,1);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (16,1);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (17,1);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (18,1);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (19,1);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (21,1);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (2,2);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (12,2);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (13,2);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (1,3);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (8,3);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (12,3);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (17,3);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (18,3);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (19,3);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (21,3);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (9,4);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (19,6);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (20,6);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (3,7);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (8,7);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (9,7);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (10,7);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (15,7);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (16,7);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (17,7);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (18,7);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (19,7);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (21,7);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (3,8);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (5,8);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (7,8);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (12,8);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (13,8);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (18,8);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (21,8);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (4,9);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (10,9);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (12,9);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (13,9);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (16,9);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (19,9);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (1,10);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (6,10);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (11,11);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (5,12);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (6,12);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (7,12);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (8,12);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (9,12);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (12,12);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (14,12);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (20,12);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (10,13);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (2,14);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (10,14);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (8,16);
INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES (19,16);
/*!40000 ALTER TABLE `dish_allergens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dishes`
--

DROP TABLE IF EXISTS `dishes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dishes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_584DD35D2B36786B` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dishes`
--

LOCK TABLES `dishes` WRITE;
/*!40000 ALTER TABLE `dishes` DISABLE KEYS */;
INSERT INTO `dishes` (`id`, `title`) VALUES (6,'Asperges blanches des Landes, vinaigrette à l\'orange');
INSERT INTO `dishes` (`id`, `title`) VALUES (16,'Blanquette de veau à l\'ancienne, riz pilaf');
INSERT INTO `dishes` (`id`, `title`) VALUES (14,'Brochettes de poulet mariné, taboulé aux herbes fraîches');
INSERT INTO `dishes` (`id`, `title`) VALUES (21,'Bûche pâtissière praliné-noisette');
INSERT INTO `dishes` (`id`, `title`) VALUES (11,'Buddha bowl quinoa, falafels, houmous de betterave');
INSERT INTO `dishes` (`id`, `title`) VALUES (17,'Cannelés de Bordeaux');
INSERT INTO `dishes` (`id`, `title`) VALUES (12,'Chapon farci aux marrons, jus au vin de Pomerol');
INSERT INTO `dishes` (`id`, `title`) VALUES (2,'Douzaine d\'huîtres du Bassin d\'Arcachon');
INSERT INTO `dishes` (`id`, `title`) VALUES (8,'Entrecôte bordelaise sauce aux cèpes');
INSERT INTO `dishes` (`id`, `title`) VALUES (5,'Foie gras mi-cuit maison, chutney de figues');
INSERT INTO `dishes` (`id`, `title`) VALUES (19,'Fondant au chocolat, coeur coulant');
INSERT INTO `dishes` (`id`, `title`) VALUES (4,'Gaspacho de tomates anciennes, basilic');
INSERT INTO `dishes` (`id`, `title`) VALUES (13,'Gigot d\'agneau rôti aux herbes, tian de légumes');
INSERT INTO `dishes` (`id`, `title`) VALUES (10,'Gratin de légumes rôtis, polenta crémeuse au parmesan');
INSERT INTO `dishes` (`id`, `title`) VALUES (15,'Magret de canard au miel, écrasé de pommes de terre');
INSERT INTO `dishes` (`id`, `title`) VALUES (20,'Mousse au chocolat à l\'aquafaba');
INSERT INTO `dishes` (`id`, `title`) VALUES (9,'Pavé de bar de ligne, risotto crémeux au safran');
INSERT INTO `dishes` (`id`, `title`) VALUES (7,'Salade landaise (gésiers, magret fumé, pignons)');
INSERT INTO `dishes` (`id`, `title`) VALUES (1,'Tartare de boeuf aux échalotes confites');
INSERT INTO `dishes` (`id`, `title`) VALUES (18,'Tarte fine aux poires et amandes');
INSERT INTO `dishes` (`id`, `title`) VALUES (22,'Tartiflette savoyard');
INSERT INTO `dishes` (`id`, `title`) VALUES (3,'Velouté de potimarron aux châtaignes');
/*!40000 ALTER TABLE `dishes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_dishes`
--

DROP TABLE IF EXISTS `menu_dishes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_dishes` (
  `menu_id` int NOT NULL,
  `dish_id` int NOT NULL,
  `dish_type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_menu_dish` (`menu_id`,`dish_id`),
  KEY `IDX_8B0A8B85148EB0CB` (`dish_id`),
  CONSTRAINT `FK_8B0A8B85148EB0CB` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`),
  CONSTRAINT `FK_8B0A8B85CCD7E912` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_dishes`
--

LOCK TABLES `menu_dishes` WRITE;
/*!40000 ALTER TABLE `menu_dishes` DISABLE KEYS */;
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (1,1,'entrée',1);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (1,8,'plat_principal',2);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (1,17,'dessert',3);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (2,2,'entrée',4);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (2,9,'plat_principal',5);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (2,18,'dessert',6);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (3,3,'entrée',7);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (3,10,'plat_principal',8);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (3,19,'dessert',9);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (4,4,'entrée',10);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (4,11,'plat_principal',11);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (4,20,'dessert',12);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (5,5,'entrée',13);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (5,12,'plat_principal',14);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (5,21,'dessert',15);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (6,6,'entrée',16);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (6,13,'plat_principal',17);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (6,19,'dessert',18);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (7,7,'entrée',19);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (7,14,'plat_principal',20);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (7,18,'dessert',21);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (8,1,'entrée',22);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (8,15,'plat_principal',23);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (8,17,'dessert',24);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (9,3,'entrée',28);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (9,16,'plat_principal',29);
INSERT INTO `menu_dishes` (`menu_id`, `dish_id`, `dish_type`, `id`) VALUES (9,19,'dessert',30);
/*!40000 ALTER TABLE `menu_dishes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_images`
--

DROP TABLE IF EXISTS `menu_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `menu_id` int NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3358E6B2CCD7E912` (`menu_id`),
  CONSTRAINT `FK_3358E6B2CCD7E912` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_images`
--

LOCK TABLES `menu_images` WRITE;
/*!40000 ALTER TABLE `menu_images` DISABLE KEYS */;
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (1,1,'/uploads/menus/tartare-00.webp','Classique Bordelais');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (2,1,'/uploads/menus/tartare-02.webp','Classique Bordelais');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (3,1,'/uploads/menus/entrecôte-bordelaise-00.webp','Classique Bordelais');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (4,1,'/uploads/menus/entrecôte-bordelaise-02.webp','Classique Bordelais');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (5,1,'/uploads/menus/cannelés-00.webp','Classique Bordelais');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (6,7,'/uploads/menus/salade-landaise-00.webp','Été Terrasse');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (7,7,'/uploads/menus/salade-landaise-01.webp','Été Terrasse');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (8,7,'/uploads/menus/brochettes-01.webp','Été Terrasse');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (9,7,'/uploads/menus/brochettes-00.webp','Été Terrasse');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (11,7,'/uploads/menus/tarte-aux-poires-01.webp','Été Terrasse');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (12,5,'/uploads/menus/foie-gras-01.webp','Fêtes du réveillon');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (13,5,'/uploads/menus/chapon-01.webp','Fêtes du réveillon');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (14,5,'/uploads/menus/chapon-03.webp','Fêtes du réveillon');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (16,5,'/uploads/menus/chapon-00.webp','Fêtes du réveillon');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (17,5,'/uploads/menus/bûche-02.webp','Fêtes du réveillon');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (18,2,'/uploads/menus/huitres-01.webp','Océan d\'Arcachon');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (19,2,'/uploads/menus/huitres-02.webp','Océan d\'Arcachon');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (20,2,'/uploads/menus/pavé-risotto-00.webp','Océan d\'Arcachon');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (21,2,'/uploads/menus/pavé-risotto-01.webp','Océan d\'Arcachon');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (22,2,'/uploads/menus/tarte-aux-poires-01_1.webp','Océan d\'Arcachon');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (23,6,'/uploads/menus/asperges-00.webp','Pâques Printanier');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (24,6,'/uploads/menus/asperges-02.webp','Pâques Printanier');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (25,6,'/uploads/menus/agneau-roti-00.webp','Pâques Printanier');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (26,6,'/uploads/menus/agneau-roti-02.webp','Pâques Printanier');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (27,6,'/uploads/menus/fondant-01.webp','Pâques Printanier');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (28,9,'/uploads/menus/vélouté-01.webp','Petit Budget - Bistrot');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (29,9,'/uploads/menus/vélouté-02.webp','Petit Budget - Bistrot');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (30,9,'/uploads/menus/blanquette-00.webp','Petit Budget - Bistrot');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (31,9,'/uploads/menus/blanquette-02.webp','Petit Budget - Bistrot');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (32,9,'/uploads/menus/fondant-00.webp','Petit Budget - Bistrot');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (33,4,'/uploads/menus/gaspacho-00.webp','Vegan Nature');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (34,4,'/uploads/menus/gaspacho-01.webp','Vegan Nature');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (35,4,'/uploads/menus/bowl-00.webp','Vegan Nature');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (36,4,'/uploads/menus/bowl-01.webp','Vegan Nature');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (37,4,'/uploads/menus/mousse-chocolat-02.webp','Vegan Nature');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (38,3,'/uploads/menus/vélouté-00.webp','Végétarien Gourmand');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (39,3,'/uploads/menus/vélouté-02_1.webp','Végétarien Gourmand');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (40,3,'/uploads/menus/gratin-legumes-00.webp','Végétarien Gourmand');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (41,3,'/uploads/menus/gratin-legumes-01.webp','Végétarien Gourmand');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (42,3,'/uploads/menus/fondant-01_1.webp','Végétarien Gourmand');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (43,8,'/uploads/menus/tartare-00_1.webp','Vendanges - Automne');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (44,8,'/uploads/menus/tartare-01.webp','Vendanges - Automne');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (45,8,'/uploads/menus/magret-canard-01.webp','Vendanges - Automne');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (46,8,'/uploads/menus/magret-canard-02.webp','Vendanges - Automne');
INSERT INTO `menu_images` (`id`, `menu_id`, `image_path`, `alt_text`) VALUES (47,8,'/uploads/menus/cannelés-02.webp','Vendanges - Automne');
/*!40000 ALTER TABLE `menu_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_general_ci,
  `regime_id` int NOT NULL,
  `price_per_person` decimal(6,2) NOT NULL,
  `min_people` int NOT NULL,
  `remaining_quantity` int DEFAULT NULL,
  `advance_order_days` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_727508CF2B36786B` (`title`),
  KEY `IDX_727508CF35E7D534` (`regime_id`),
  CONSTRAINT `FK_727508CF35E7D534` FOREIGN KEY (`regime_id`) REFERENCES `regimes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
INSERT INTO `menus` (`id`, `title`, `description`, `regime_id`, `price_per_person`, `min_people`, `remaining_quantity`, `advance_order_days`) VALUES (1,'Classique Bordelais','Saveurs authentiques du terroir bordelais. Viande à conserver entre 0-4°C. Service sous 2h après livraison.',1,35.00,8,95,3);
INSERT INTO `menus` (`id`, `title`, `description`, `regime_id`, `price_per_person`, `min_people`, `remaining_quantity`, `advance_order_days`) VALUES (2,'Océan d\'Arcachon','Produits frais de la marée. Livraison le jour-même obligatoire. Conservation 4h max. Saison : septembre à avril.',1,42.00,10,63,4);
INSERT INTO `menus` (`id`, `title`, `description`, `regime_id`, `price_per_person`, `min_people`, `remaining_quantity`, `advance_order_days`) VALUES (3,'Végétarien Gourmand','Légumes de saison. Réchauffage four 15min à 180°C. Conservation 48h réfrigéré.',2,28.00,6,80,2);
INSERT INTO `menus` (`id`, `title`, `description`, `regime_id`, `price_per_person`, `min_people`, `remaining_quantity`, `advance_order_days`) VALUES (4,'Vegan Nature','Fraîcheur végétale. À consommer dans les 24h. Conservation 4°C. Idéal printemps/été.',3,26.00,6,5,3);
INSERT INTO `menus` (`id`, `title`, `description`, `regime_id`, `price_per_person`, `min_people`, `remaining_quantity`, `advance_order_days`) VALUES (5,'Fêtes du réveillon','Menu festif exceptionnel. Commande avant le 10 décembre impératif. Livraison 23-24 déc. Réchauffage plat principal 45min four.',1,58.00,12,20,15);
INSERT INTO `menus` (`id`, `title`, `description`, `regime_id`, `price_per_person`, `min_people`, `remaining_quantity`, `advance_order_days`) VALUES (6,'Pâques Printanier','Célébration du printemps. Livraison weekend de Pâques. Gigot à réchauffer 20min. Saison : mars-avril.',1,38.00,8,110,7);
INSERT INTO `menus` (`id`, `title`, `description`, `regime_id`, `price_per_person`, `min_people`, `remaining_quantity`, `advance_order_days`) VALUES (7,'Été Terrasse','Idéal pour événements extérieurs. Plats servis froids/tièdes. Conservation 4°C. Saison : juin à septembre.',1,32.00,10,77,2);
INSERT INTO `menus` (`id`, `title`, `description`, `regime_id`, `price_per_person`, `min_people`, `remaining_quantity`, `advance_order_days`) VALUES (8,'Vendanges - Automne','Menu festif pour célébrations vigneronnes. Produits du terroir. Saison : septembre-octobre.',1,45.00,15,30,10);
INSERT INTO `menus` (`id`, `title`, `description`, `regime_id`, `price_per_person`, `min_people`, `remaining_quantity`, `advance_order_days`) VALUES (9,'Petit Budget - Bistrot','Rapport qualité/prix exceptionnel. Idéal événements associatifs/professionnels. Livraison standard.',1,22.00,6,121,2);
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_menus`
--

DROP TABLE IF EXISTS `order_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `menu_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price_per_person` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F1EB7C328D9F6D38` (`order_id`),
  KEY `IDX_F1EB7C32CCD7E912` (`menu_id`),
  CONSTRAINT `order_menus_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_menus_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_menus`
--

LOCK TABLES `order_menus` WRITE;
/*!40000 ALTER TABLE `order_menus` DISABLE KEYS */;
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (1,1,4,6,26.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (2,2,3,13,28.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (3,3,8,15,45.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (4,4,9,17,22.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (6,6,7,21,32.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (7,7,5,12,58.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (9,9,6,8,38.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (10,9,9,6,22.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (11,10,1,10,35.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (12,10,7,10,32.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (13,11,1,20,35.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (14,12,4,6,26.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (15,13,8,25,45.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (16,13,9,15,22.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (25,19,9,6,22.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (26,20,2,17,42.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (27,21,9,15,22.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (28,22,1,8,35.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (40,28,3,11,28.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (45,30,6,8,38.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (46,25,9,10,22.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (47,31,4,12,26.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (49,32,2,20,37.80);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (50,5,4,12,23.40);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (52,33,5,18,52.20);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (53,34,2,22,37.80);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (54,35,6,14,34.20);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (56,36,1,14,31.50);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (57,37,3,6,28.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (58,38,7,22,28.80);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (59,39,8,15,45.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (60,40,9,9,22.00);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (61,41,6,22,34.20);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (62,42,4,12,23.40);
INSERT INTO `order_menus` (`id`, `order_id`, `menu_id`, `quantity`, `price_per_person`) VALUES (63,43,6,20,34.20);
/*!40000 ALTER TABLE `order_menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `order_date` datetime NOT NULL,
  `delivery_date` date NOT NULL,
  `delivery_time` time NOT NULL,
  `delivery_address` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `equipment_loan` tinyint(1) NOT NULL,
  `equipment_returned` tinyint(1) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E52FFDEEA76ED395` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (1,8,'2026-02-14 15:34:00','2026-03-14','19:30:00','4 Rue Jean Veyri, 33700 Mérignac',280.00,8.48,288.48,0,0,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (2,8,'2026-02-19 10:43:19','2026-03-18','10:30:00','4 Rue Jean Veyri, 33700 Mérignac',156.00,8.48,164.48,0,0,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (3,9,'2026-02-20 14:25:38','2026-03-21','18:00:00','108 Rue Abbé de l\'Épée, 33000 Bordeaux',327.60,5.00,332.60,1,1,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (4,10,'2026-02-24 14:30:00','2026-03-25','12:00:00','10 Pl. du 14 Juillet, 33130 Bègles',675.00,5.00,680.00,1,1,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (5,11,'2026-02-25 14:37:07','2026-03-27','18:00:00','4 Rue Alain Fournier 33150 Cenon',280.80,8.06,288.86,1,1,'annulée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (6,10,'2026-02-26 15:48:49','2026-03-27','17:30:00','10 Pl. du 14 Juillet, 33130 Bègles',280.80,5.00,285.80,0,0,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (7,13,'2026-02-27 09:28:28','2026-03-28','13:00:00','4 Rue Saint-Pierre, 33760 Saint-Pierre-de-Bat',604.80,24.46,629.26,1,1,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (9,9,'2026-03-02 21:52:19','2026-04-03','17:00:00','108 Rue Abbé de l\'Épée, 33000 Bordeaux',436.00,5.00,441.00,0,0,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (10,13,'2026-03-03 23:00:13','2026-04-04','10:00:00','4 Rue Saint-Pierre, 33760 Saint-Pierre-de-Bat',670.00,24.46,694.46,1,1,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (11,14,'2026-03-05 14:57:17','2026-04-08','10:30:00','1 Pl. Jacques Prévert, 33240 Saint-Gervais',280.00,17.98,297.98,0,0,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (12,20,'2026-03-09 15:10:30','2026-04-07','18:00:00','16 Av. Pasteur, 33450 Saint-Loubès',156.00,13.52,169.52,0,0,'annulée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (13,16,'2026-03-11 15:17:12','2026-04-10','10:00:00','159 Av. de la Marne, 33700 Mérignac',1032.00,5.00,1037.00,1,1,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (19,9,'2026-03-13 19:43:46','2026-04-11','17:30:00','108 Rue Abbé de l\'Épée, 33000 Bordeaux',132.00,5.00,137.00,0,0,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (20,13,'2026-03-14 23:14:32','2026-04-11','09:00:00','4 Rue Saint-Pierre, 33760 Saint-Pierre-de-Bat',420.00,24.47,444.47,0,0,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (21,19,'2026-03-16 10:13:37','2026-04-14','10:00:00','Rue Maurice Ravel 33160 Saint-Médard-en-Jalles',297.00,14.14,311.14,0,0,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (22,17,'2026-03-18 10:16:15','2026-04-15','10:00:00','2 Rue Pelleport 33800 Bordeaux',280.00,5.00,285.00,0,0,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (25,17,'2026-03-20 13:59:03','2026-04-18','08:30:00','23 Rue Pelleport 33800 Bordeaux',220.00,5.00,225.00,0,0,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (28,15,'2026-03-21 17:59:03','2026-04-17','10:00:00','12 Rue Pasteur 33200 Bordeaux',308.00,5.00,313.00,0,0,'terminée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (30,16,'2026-03-24 11:45:02','2026-04-22','08:30:00','153bis Rue Peydavant 33400 Talence',304.00,5.00,309.00,0,0,'en attente de retour de matériel');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (31,20,'2026-03-26 12:21:00','2026-04-24','09:00:00','47 Rue Jules Ferry 33200 Bordeaux',280.80,5.00,285.80,0,0,'en attente de retour de matériel');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (32,12,'2026-04-01 21:40:20','2026-04-25','10:00:00','47 Rue Jules Ferry 33200 Bordeaux',756.00,5.00,761.00,1,1,'livrée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (33,14,'2026-04-02 17:30:31','2026-04-28','09:00:00','22 Rue Thiers 33500 Libourne',939.60,21.33,960.93,1,1,'livrée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (34,21,'2026-04-08 08:34:31','2026-04-30','10:00:00','Rue Saint Barthélemy 40160 Parentis-en-Born',831.60,44.45,876.05,1,1,'livrée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (35,22,'2026-04-06 13:40:04','2026-04-29','19:00:00','23 Rue Ferdinand Buisson 33140 Villenave-d\'Ornon',478.80,9.96,488.76,0,0,'livrée');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (36,12,'2026-04-10 15:44:02','2026-05-05','18:00:00','4 Place du centre 85360 La Tranche-sur-Mer',441.00,111.42,552.42,0,0,'en cours de livraison');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (37,8,'2026-04-12 11:17:10','2026-05-02','11:00:00','153bis Rue Peydavant 33400 Talence',168.00,5.00,173.00,0,0,'en cours de livraison');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (38,11,'2026-04-17 10:27:07','2026-05-15','11:00:00','2 Rue des Douves 17120 Mortagne-sur-Gironde',633.60,48.27,681.87,1,1,'en préparation');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (39,15,'2026-04-14 14:32:59','2026-05-09','09:00:00','Place de la Bourse 33000 Bordeaux',675.00,5.00,680.00,1,1,'en préparation');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (40,16,'2026-04-24 19:37:35','2026-05-28','08:30:00','18 Avenue Thiers 33100 Bordeaux',198.00,5.00,203.00,0,0,'en attente');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (41,18,'2026-04-27 10:40:05','2026-05-30','09:00:00','127 Boulevard Denfert Rochereau 16100 Cognac',752.40,62.64,815.04,1,1,'en attente');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (42,21,'2026-04-29 17:42:43','2026-06-10','18:00:00','Rue Victor Schoelcher 33270 Floirac',280.80,5.00,285.80,0,0,'en attente');
INSERT INTO `orders` (`id`, `user_id`, `order_date`, `delivery_date`, `delivery_time`, `delivery_address`, `subtotal`, `delivery_fee`, `total_amount`, `equipment_loan`, `equipment_returned`, `status`) VALUES (43,19,'2026-04-30 00:33:08','2026-06-06','10:00:00','22 Avenue du Général de Castelnau 33700 Mérignac',684.00,9.13,693.13,0,0,'annulée');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `used_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_TOKEN` (`token`),
  KEY `IDX_USER` (`user_id`),
  CONSTRAINT `FK_PRT_USER` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
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
-- Table structure for table `regimes`
--

DROP TABLE IF EXISTS `regimes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `regimes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_42177456EA750E8` (`label`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regimes`
--

LOCK TABLES `regimes` WRITE;
/*!40000 ALTER TABLE `regimes` DISABLE KEYS */;
INSERT INTO `regimes` (`id`, `label`) VALUES (1,'Classique');
INSERT INTO `regimes` (`id`, `label`) VALUES (4,'Sans gluten');
INSERT INTO `regimes` (`id`, `label`) VALUES (3,'Vegan');
INSERT INTO `regimes` (`id`, `label`) VALUES (2,'Végétarien');
/*!40000 ALTER TABLE `regimes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B63E2EC75E237E06` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`id`, `name`, `description`) VALUES (1,'admin','Administrateur système');
INSERT INTO `roles` (`id`, `name`, `description`) VALUES (2,'staff_member','Membre de Vite&Gourmand');
INSERT INTO `roles` (`id`, `name`, `description`) VALUES (3,'client','Client commandant des menus');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `lastname` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `city` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `postal_code` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1483A5E9E7927C74` (`email`),
  KEY `IDX_1483A5E9D60322AC` (`role_id`),
  CONSTRAINT `FK_1483A5E9D60322AC` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (1,'José','Garcia','jose.garcia@gmail.com','$2y$13$L5EmUEnyqSaLN/l.ivNEBuTyTGY2NzaYna.6nY/PQHUz9M3V7xTgu','0756526212','37 Rue Thiac','Bordeaux','33000',1);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (2,'Julie','Séguigne','julie.seguigne@live.com','$2y$13$H42nUv1JENl4WSpQKkGqwueVNC/9yh3Xe9kCajT9nJ6Dqduu5a5DS','0556526212','37 Rue Thiac','Bordeaux','33000',1);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (3,'Maxime','Daros','maxime.daros@gmail.com','$2y$13$8f2jXTLGgrAlrFe/kaVgF.MsxluRO8bP3v0vTFdthjW77gfwE7LJS','0722546789','13 Rue Charlevoix de Villers','Bordeaux','33000',2);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (4,'Nathalie','Bernard','nathalie.bernard@gmail.com','$2y$13$Z1KJ0hNSIxZaAwKwsEIPvu4sWqtu6hLJ2h4CnS4f4x0fYqZog11iK','0634117994','85 Rue nationale','Saint-André-de-Cubzac','33240',2);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (5,'Samir','Benali','samir.benali@gmail.com','$2y$13$/2elXaHYvUWPO4h8GWwhnut/WITtRcFUQCld9fgEYjD4qjgA9H9yi','0754331442','16 Av. Pasteur','Saint-Loubès','33450',2);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (6,'Yael','Kalfa','yael.kalfa@gmail.com','$2y$13$2kgAU5WRYkpbcfE/I9U4cepzw4UfmHqSjCcRIPr2PHnOPD.Ep2fSK','0622174165','207 Cr de la Somme','Bordeaux','33800',2);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (7,'Juliette','Lété','juliette.lete@live.com','$2y$13$G08HgxBcV3bt74vMfv1ATuTa8emkl4CExENzTBt37NgZ0JWf7zNya','0534213370','70 Rue Principale','Lamarque','33460 ',2);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (8,'Emilie','Favre','emilie.favre@yahoo.com','$2y$13$LZriIQBXOZvyDHIs9y3zfuPYOqwmhogPWPvFWD2tayQpbKNBQyEJe','0765331441','153 bis Rue Peydavant','Talence','33400',3);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (9,'Valentina','Galbano','valentina.galbano@gmail.com','$2y$13$FoZg1bQp7Re/Xj6P9pSHZuNjVmrpHwWVN72Bl0lSTF8tKwrgKlxzW','0534317191','2 Rue Maurice Ravel','Saint-Médard-en-Jalles','33160',3);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (10,'Pep','Jordi','pep.jordi@hotmail.com','$2y$13$ek5cwD.1.XbH1uFHciucmOuxGO8nRpP/57gyrwk3unVma7q454.p6','0627174165','47 Rue Jules Ferry','Bordeaux','33200',3);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (11,'Didier','Fronton','didier.fronton@gmail.com','$2y$13$g6PBIeDcyWk1GOVu04mp7ONGw4lzilZC2lz6/l5znDTcUqk3FDMVC','0767731321','69 Av. Jean Cordier','Pessac','33600',3);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (12,'Melissa','Harrison','melisa.harrison@hotmail.com','$2y$13$uruidlQcGqAEDTt5hbNA5eyprCqYUt4uJWQo0HoVre56LGxA0iZOK','0535574199','4 Pl. du Centre','La Tranche-sur-Mer','85360',3);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (13,'Florence','Rosand','florence.rosand@gmail.com','$2y$13$IqwNEULLCjzxqNSGm/UEo.LdpqAFugw53IdHNOo3kpjHdODkrUfWG','0534112155','18 Rue du Grand Jeannon','Biscarrosse','40600',3);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (14,'Alice','Martin','alice.martin@live.com','$2y$13$1Uds18ZDTgGtzndDsWG9M.Zv6Z23JcqjPCGJeYFw5xIvx3ZDPjYJS','0544216566','12 Rue Sainte-Catherine','Bordeaux','33000',3);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (15,'Hugo','Bernard','hugo.bernard@gmail.com','$2y$13$URwjbF8z6gDL/uJshRfvGu0ae9SCGC4iWPMWwQaW2h1PpRok9ksrO','0752047198','5 Place de la Bourse','Bordeaux','33000',3);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (16,'Emma','Dubois','emma.dubois@hotmail.com','$2y$13$H9YXxiT6OdcEZDFybOvw0uzbndu3ltvVXeuNvQosbkxl1G8WwxEZq','0634567890','18 Avenue Thierrs','Bordeaux','33100',3);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (17,'David','Lévy','david.levy@yahoo.com','$2y$13$hjbVp3F2uVR9F6cdB5iAOO7oQgvV/nrDo/ToZbLJdjk0Hz2TBSpA.','0611423344','14 Rue Judaïque','Bordeaux','33000',3);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (18,'Chloé','Moreau','chloe.moreau@gmail.com','$2y$13$keUjwYnaK2r//GCYQJdFZu0AjlTiEpzoLiBYksk/ATxv1MPWtcnFS','0656789012','7 Rue Jean Jaurès','Pessac','33600',3);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (19,'Lucas','Lefevre','lucas.lefevre@live.com','$2y$13$e6A7OE9G0KgfkOWBfhwXwOnhviYDrCFWpGaf3qx/Rw1ucDgIHuAxW','0645678901','22 Rue du Général de Gaulle','Mérignac','33700',3);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (20,'Sarah','Cohen','sarah.cohen@gmail.com','$2y$13$kfu1P8z.ZQH2238AyNOI0u2idvMQ4FV0hynSWcbIMAABABubfYAUO','0622334455','8 Rue du Mirail','Bordeaux','33000',3);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (21,'Manon','Pereira','manon.pereira@gmail.com','$2y$13$lVT3PqNtGfM4YgvN6oTsPu3b2w4WHmhYWGkR7Jnzn2HBneaZhu2H.','0590123456','27 Rue Victor Hugo','Floirac','33270',3);
INSERT INTO `users` (`id`, `name`, `lastname`, `email`, `password`, `phone`, `address`, `city`, `postal_code`, `role_id`) VALUES (22,'Ousmane','Petit','ousmane.petit@gmail.com','$2y$13$ACwbDOGoiGHypP6WXqBeHeDqYZaIbLAHZ4Pi8rf9Ct7W/TAq/OOeK','0667890123','11 Avenue de la Libération','Cenon','33150',3);
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

-- Dump completed on 2026-05-04 10:12:37
