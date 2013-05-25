-- MySQL dump 10.13  Distrib 5.1.66, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: sklad
-- ------------------------------------------------------
-- Server version	5.1.66-0+squeeze1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES (3,'koren'),(8,'koren/a'),(10,'koren/a/a'),(11,'koren/a/c'),(9,'koren/a/d'),(12,'koren/a/o'),(13,'novy/x/y/z'),(1,'picovinky'),(2,'Tezka technika, Traktory, etc..');
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group`
--

DROP TABLE IF EXISTS `group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_key` char(23) COLLATE utf8_czech_ci NOT NULL,
  `group_value` char(23) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`group_id`,`group_key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group`
--

LOCK TABLES `group` WRITE;
/*!40000 ALTER TABLE `group` DISABLE KEYS */;
INSERT INTO `group` VALUES (0,'default','rw'),(0,'name','admin'),(2,'name','employee');
/*!40000 ALTER TABLE `group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `location`
--

DROP TABLE IF EXISTS `location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `location` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `location_name` varchar(23) COLLATE utf8_czech_ci NOT NULL,
  `location_author` int(11) unsigned DEFAULT NULL,
  `location_description` text COLLATE utf8_czech_ci,
  PRIMARY KEY (`location_id`),
  UNIQUE KEY `location_name` (`location_name`),
  KEY `location_author` (`location_author`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `location`
--

LOCK TABLES `location` WRITE;
/*!40000 ALTER TABLE `location` DISABLE KEYS */;
INSERT INTO `location` VALUES (0,'UNIVERSE',14,'Tady je všechno co nikde neni :-)'),(4,'aaatest',0,'');
/*!40000 ALTER TABLE `location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `producer`
--

DROP TABLE IF EXISTS `producer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `producer` (
  `producer_id` int(11) NOT NULL AUTO_INCREMENT,
  `producer_name` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `producer_note` varchar(512) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`producer_id`),
  UNIQUE KEY `producer_name` (`producer_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producer`
--

LOCK TABLES `producer` WRITE;
/*!40000 ALTER TABLE `producer` DISABLE KEYS */;
INSERT INTO `producer` VALUES (1,'C.C.M.E','Czech Company Making Everything'),(2,'AchAchne Labz','Ach ne');
/*!40000 ALTER TABLE `producer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room`
--

DROP TABLE IF EXISTS `room`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `room` (
  `room_id` int(11) NOT NULL AUTO_INCREMENT,
  `room_name` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `room_author` int(11) NOT NULL DEFAULT '0',
  `room_descript` text COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`room_id`),
  UNIQUE KEY `room_name` (`room_name`),
  KEY `user_id` (`room_author`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room`
--

LOCK TABLES `room` WRITE;
/*!40000 ALTER TABLE `room` DISABLE KEYS */;
INSERT INTO `room` VALUES (1,'BarvyLaky',0,'LakyBarvy');
/*!40000 ALTER TABLE `room` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `status`
--

DROP TABLE IF EXISTS `status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `status_name` varchar(16) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `status_name` (`status_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `status`
--

LOCK TABLES `status` WRITE;
/*!40000 ALTER TABLE `status` DISABLE KEYS */;
INSERT INTO `status` VALUES (0,'deleted'),(4,'destroyed'),(2,'placed'),(3,'sold'),(1,'stored');
/*!40000 ALTER TABLE `status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction`
--

DROP TABLE IF EXISTS `transaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction` (
  `transaction_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_time` datetime NOT NULL,
  `transaction_from` varchar(23) COLLATE utf8_czech_ci NOT NULL,
  `transaction_to` varchar(23) COLLATE utf8_czech_ci NOT NULL,
  `transaction_amount` int(11) NOT NULL,
  `transaction_author` int(11) NOT NULL,
  `transaction_comment` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`transaction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction`
--

LOCK TABLES `transaction` WRITE;
/*!40000 ALTER TABLE `transaction` DISABLE KEYS */;
INSERT INTO `transaction` VALUES (29,'2013-05-25 06:10:58','cash','cash',0,23,'Created account \"cash\"'),(30,'2013-05-25 06:10:59','material','material',0,23,'Created account \"material\"'),(31,'2013-05-25 06:11:00','pohonne_hmoty','pohonne_hmoty',0,23,'Created account \"pohone_hmoty\"'),(32,'2013-05-25 06:11:01','gandalf','gandalf',0,23,'Created account \"gandalf\"'),(33,'2013-05-25 06:11:02','kasasro','kasasro',0,23,'Created account \"kasasro\"'),(34,'2013-05-25 06:11:03','trezor','trezor',0,23,'Created account \"trezor\"'),(35,'2013-05-25 06:11:04','harvie','harvie',0,23,'Created account \"harvie\"'),(36,'2013-05-25 06:11:05','cash','kasasro',23000,23,'zakaznik zaplatil za net'),(37,'2013-05-25 06:11:06','kasasro','trezor',20000,23,'prevod velke castky do trezoru'),(38,'2013-05-25 06:11:07','kasasro','harvie',250,23,'harvie si pučil na oběd'),(39,'2013-05-25 06:11:08','harvie','gandalf',100,23,'harvie převedl část svého dluhu na gandalfa'),(40,'2013-05-25 06:11:09','harvie','kasasro',50,23,'Harvie zaplatil část dluhu do kasy'),(41,'2013-05-25 06:11:10','gandalf','kasasro',100,23,'gandalf zaplatil svuj dluh do kasy'),(42,'2013-05-25 06:11:11','harvie','harvie',23,23,'harvie z nudy převedl 23 kč sám sobě'),(43,'2013-05-25 06:11:12','kasasro','material',500,23,'Platba za i4wifi dobírku'),(44,'2013-05-25 06:11:13','cash','kasasro',600,23,'Výběr z bankomatu do kasy'),(51,'2013-05-25 06:11:20','kasasro','pohonne_hmoty',700,23,'nafta do chlachola'),(52,'2013-05-25 19:12:22','trezor','cash',2500,23,'test'),(53,'2013-05-25 19:21:45','manko','manko',0,23,'Created account \"manko\"'),(54,'2013-05-25 19:22:49','kasasro','manko',1,23,'v kase nam chybi 1kč... priznejte se...'),(55,'2013-05-25 20:02:59','manko','kasasro',1,23,'chybějící 1kč byla nalezena :)'),(56,'2013-05-25 21:14:02','harvie','kasasro',50,23,'platba dluhu'),(57,'2013-05-25 21:14:38','rumcajs','rumcajs',0,23,'Created account \"rumcajs\"');
/*!40000 ALTER TABLE `transaction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `user_permit` enum('user','admin') COLLATE utf8_czech_ci NOT NULL DEFAULT 'user',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendor`
--

DROP TABLE IF EXISTS `vendor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendor` (
  `vendor_id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_name` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `vendor_note` varchar(256) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`vendor_id`),
  UNIQUE KEY `vendor_name` (`vendor_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor`
--

LOCK TABLES `vendor` WRITE;
/*!40000 ALTER TABLE `vendor` DISABLE KEYS */;
INSERT INTO `vendor` VALUES (1,'C.C.S.E','Czech Company Selling Everything'),(2,'Trolo Cybernetix','trololo');
/*!40000 ALTER TABLE `vendor` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-05-25 21:18:38

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
