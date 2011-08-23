-- MySQL dump 10.11
--
-- Host: localhost    Database: sklad
-- ------------------------------------------------------
-- Server version	5.0.51a-24+lenny5

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
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `category` (
  `category_id` int(11) NOT NULL auto_increment,
  `category_name` varchar(64) collate utf8_czech_ci NOT NULL,
  PRIMARY KEY  (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES (1,'picovinky'),(2,'Tezka technika, Traktory, etc..');
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item`
--

DROP TABLE IF EXISTS `item`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `item` (
  `item_id` int(11) NOT NULL auto_increment,
  `model_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `item_serial` varchar(128) collate utf8_czech_ci NOT NULL,
  `item_quantity` int(11) NOT NULL default '1',
  `room_id` int(11) NOT NULL default '1',
  `status_id` int(11) NOT NULL default '1',
  `item_price_in` decimal(9,2) NOT NULL default '0.00',
  `item_price_out` decimal(9,2) default NULL,
  `item_customer` int(11) default NULL,
  `item_note` varchar(512) collate utf8_czech_ci default NULL,
  `item_author` int(11) NOT NULL,
  `item_valid_till` timestamp NOT NULL default '0000-00-00 00:00:00',
  `item_valid_from` timestamp NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`item_id`,`item_valid_till`),
  UNIQUE KEY `item_serial_item_valid_till` (`item_serial`,`item_valid_till`),
  KEY `vendor_id` (`vendor_id`),
  KEY `model_id` (`model_id`),
  KEY `status_id` (`status_id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `item_ibfk_6` FOREIGN KEY (`vendor_id`) REFERENCES `vendor` (`vendor_id`),
  CONSTRAINT `item_ibfk_7` FOREIGN KEY (`model_id`) REFERENCES `model` (`model_id`),
  CONSTRAINT `item_ibfk_8` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`),
  CONSTRAINT `item_ibfk_9` FOREIGN KEY (`room_id`) REFERENCES `room` (`room_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `item`
--

LOCK TABLES `item` WRITE;
/*!40000 ALTER TABLE `item` DISABLE KEYS */;
INSERT INTO `item` VALUES (9,3,2,'SATAN',0,1,1,'0.10','0.00',NULL,NULL,0,'0000-00-00 00:00:00','2011-08-06 02:37:43'),(25,1,1,'sdaTEST3',3,1,3,'23.00','0.00',0,'',23,'0000-00-00 00:00:00','2011-08-20 00:13:26'),(25,1,1,'sdaTEST3',3,1,3,'0.00','0.00',NULL,NULL,23,'2011-08-20 00:13:26','2011-08-06 03:07:37'),(26,2,1,'ABC123',900,1,3,'0.00','0.00',NULL,NULL,23,'0000-00-00 00:00:00','2011-08-08 03:57:55'),(27,2,1,'deleteme8',900,1,1,'0.00','0.00',NULL,NULL,23,'0000-00-00 00:00:00','2011-08-09 01:51:43'),(27,2,1,'deleteme',900,1,3,'0.00','0.00',NULL,NULL,23,'2011-08-09 00:01:10','2011-08-09 00:01:10'),(27,2,1,'deleteme2',900,1,3,'0.00','0.00',NULL,NULL,23,'2011-08-09 00:01:23','2011-08-09 00:01:23'),(27,2,1,'deleteme3',900,1,3,'0.00','0.00',NULL,NULL,23,'2011-08-09 00:02:04','2011-08-09 00:02:04'),(27,2,1,'deleteme4',900,1,3,'0.00','0.00',NULL,NULL,23,'2011-08-09 00:26:07','2011-08-09 00:02:04'),(27,2,1,'deleteme5',900,1,3,'0.00','0.00',NULL,NULL,23,'2011-08-09 00:29:09','2011-08-09 00:26:07'),(27,2,1,'deleteme6',900,1,3,'0.00','0.00',NULL,NULL,23,'2011-08-09 00:31:04','2011-08-09 00:29:09'),(27,2,1,'deleteme7',900,1,3,'0.00','0.00',NULL,NULL,23,'2011-08-09 00:31:50','2011-08-09 00:31:04'),(27,2,1,'deleteme8',900,1,3,'0.00','0.00',NULL,NULL,23,'2011-08-09 01:39:17','2011-08-09 00:31:50'),(27,3,1,'deleteme8',0,1,1,'0.00','0.00',NULL,NULL,23,'2011-08-09 01:51:13','2011-08-09 01:40:07'),(27,2,1,'deleteme8',0,1,1,'0.00','0.00',NULL,NULL,23,'2011-08-09 01:51:43','2011-08-09 01:51:13'),(31,2,1,'seriáál',1,1,1,'0.00','0.00',NULL,NULL,23,'0000-00-00 00:00:00','2011-08-09 02:36:26');
/*!40000 ALTER TABLE `item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model`
--

DROP TABLE IF EXISTS `model`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `model` (
  `model_id` int(11) NOT NULL auto_increment,
  `model_name` varchar(64) collate utf8_czech_ci NOT NULL,
  `producer_id` int(11) NOT NULL default '0',
  `category_id` int(11) NOT NULL default '0',
  `model_barcode` varchar(128) collate utf8_czech_ci NOT NULL,
  `model_price_out` decimal(9,2) default NULL,
  `model_descript` varchar(1024) collate utf8_czech_ci NOT NULL,
  PRIMARY KEY  (`model_id`),
  UNIQUE KEY `model_barcode` (`model_barcode`),
  KEY `category_id` (`category_id`),
  KEY `producer_id` (`producer_id`),
  CONSTRAINT `model_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`),
  CONSTRAINT `model_ibfk_2` FOREIGN KEY (`producer_id`) REFERENCES `producer` (`producer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `model`
--

LOCK TABLES `model` WRITE;
/*!40000 ALTER TABLE `model` DISABLE KEYS */;
INSERT INTO `model` VALUES (1,'Rushkoff: Klub extáze',1,1,'9788086096599','23.00','prvni vec s carovym kodem co sem videl'),(2,'Bitevní tank',1,2,'BT23','1.00','autoradio v cene'),(3,'Karel Gott - kompletní diskografie',2,2,'4792207502505','666.00','možno použít i k sebeobranným účelům');
/*!40000 ALTER TABLE `model` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `producer`
--

DROP TABLE IF EXISTS `producer`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `producer` (
  `producer_id` int(11) NOT NULL auto_increment,
  `producer_name` varchar(64) collate utf8_czech_ci NOT NULL,
  `producer_note` varchar(512) collate utf8_czech_ci NOT NULL,
  PRIMARY KEY  (`producer_id`),
  UNIQUE KEY `producer_name` (`producer_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
SET character_set_client = @saved_cs_client;

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
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `room` (
  `room_id` int(11) NOT NULL auto_increment,
  `room_name` varchar(64) collate utf8_czech_ci NOT NULL,
  `room_author` int(11) NOT NULL default '0',
  `room_descript` text collate utf8_czech_ci NOT NULL,
  PRIMARY KEY  (`room_id`),
  UNIQUE KEY `room_name` (`room_name`),
  KEY `user_id` (`room_author`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
SET character_set_client = @saved_cs_client;

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
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `status` (
  `status_id` int(11) NOT NULL auto_increment,
  `status_name` varchar(16) collate utf8_czech_ci NOT NULL,
  PRIMARY KEY  (`status_id`),
  UNIQUE KEY `status_name` (`status_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `status`
--

LOCK TABLES `status` WRITE;
/*!40000 ALTER TABLE `status` DISABLE KEYS */;
INSERT INTO `status` VALUES (0,'deleted'),(4,'destroyed'),(2,'placed'),(3,'saled'),(1,'stored');
/*!40000 ALTER TABLE `status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL default '0',
  `user_permit` enum('user','admin') collate utf8_czech_ci NOT NULL default 'user',
  PRIMARY KEY  (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
SET character_set_client = @saved_cs_client;

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
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `vendor` (
  `vendor_id` int(11) NOT NULL auto_increment,
  `vendor_name` varchar(64) collate utf8_czech_ci NOT NULL,
  `vendor_note` varchar(256) collate utf8_czech_ci NOT NULL,
  PRIMARY KEY  (`vendor_id`),
  UNIQUE KEY `vendor_name` (`vendor_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
SET character_set_client = @saved_cs_client;

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

-- Dump completed on 2011-08-23  1:30:58
