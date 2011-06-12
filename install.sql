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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
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
  `item_quantity` int(11) default NULL,
  `room_id` int(11) NOT NULL default '0',
  `status_id` int(11) NOT NULL default '0',
  `item_price_in` decimal(9,2) NOT NULL default '0.00',
  `item_price_out` decimal(9,2) default NULL,
  PRIMARY KEY  (`item_id`),
  UNIQUE KEY `item_serial` (`item_serial`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `item`
--

LOCK TABLES `item` WRITE;
/*!40000 ALTER TABLE `item` DISABLE KEYS */;
INSERT INTO `item` VALUES (1,1,1,'JGFHGA343',NULL,1,1,'13.00',NULL),(2,1,1,'OMG42',NULL,1,1,'7.00','25.00'),(3,2,0,'aaa232',NULL,0,0,'0.00',NULL),(9,3,2,'SATAN',0,1,1,'0.10','0.00'),(20,3,1,'editmeeeee',23,1,1,'0.00','0.00');
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
  UNIQUE KEY `model_barcode` (`model_barcode`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `model`
--

LOCK TABLES `model` WRITE;
/*!40000 ALTER TABLE `model` DISABLE KEYS */;
INSERT INTO `model` VALUES (1,'Rushkoff: Klub extáze',0,0,'9788086096599','23.00','prvni vec s carovym kodem co sem videl'),(2,'Rama MultiVita',1,1,'8593838925918','15.00','oblibeny ropny produkt'),(3,'Karel Gott - kompletní diskografie',2,2,'4792207502505','666.00','možno použít i k sebeobraně');
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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
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
-- Table structure for table `rights`
--

DROP TABLE IF EXISTS `rights`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `rights` (
  `user_id` int(11) NOT NULL default '0',
  `permit` enum('user','admin') collate utf8_czech_ci NOT NULL default 'user',
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `rights`
--

LOCK TABLES `rights` WRITE;
/*!40000 ALTER TABLE `rights` DISABLE KEYS */;
/*!40000 ALTER TABLE `rights` ENABLE KEYS */;
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
  `manager_id` int(11) NOT NULL default '0',
  `room_descript` text collate utf8_czech_ci NOT NULL,
  PRIMARY KEY  (`room_id`),
  UNIQUE KEY `room_name` (`room_name`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `status`
--

LOCK TABLES `status` WRITE;
/*!40000 ALTER TABLE `status` DISABLE KEYS */;
INSERT INTO `status` VALUES (1,'stored'),(2,'placed'),(3,'saled'),(4,'destroyed');
/*!40000 ALTER TABLE `status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction`
--

DROP TABLE IF EXISTS `transaction`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `transaction` (
  `transaction_id` int(11) NOT NULL auto_increment,
  `item_id` int(11) NOT NULL default '0',
  `transaction_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `status_id_in` int(11) NOT NULL default '0',
  `status_id_out` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`transaction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `transaction`
--

LOCK TABLES `transaction` WRITE;
/*!40000 ALTER TABLE `transaction` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaction` ENABLE KEYS */;
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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
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

-- Dump completed on 2011-06-12  3:18:45
