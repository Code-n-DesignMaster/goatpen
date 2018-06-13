# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.19-0ubuntu0.16.04.1)
# Database: goatpen
# Generation Time: 2017-12-29 22:25:48 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table campaigns
# ------------------------------------------------------------

DROP TABLE IF EXISTS `campaigns`;

CREATE TABLE `campaigns` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL DEFAULT '',
  `client` varchar(256) NOT NULL DEFAULT '',
  `budget` decimal(10,2) unsigned DEFAULT NULL,
  `tags` text,
  `deliverables` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

LOCK TABLES `campaigns` WRITE;
/*!40000 ALTER TABLE `campaigns` DISABLE KEYS */;

INSERT INTO `campaigns` (`id`, `name`, `client`, `budget`, `tags`, `deliverables`, `created_at`, `updated_at`)
VALUES
	(1,'Kwiff September','Kwiff',NULL,NULL,NULL,'2017-09-10 22:01:50','2017-09-10 22:01:50'),
	(2,'#BecauseSummer 2017','Malibu',NULL,NULL,NULL,'2017-09-10 22:02:07','2017-09-10 22:02:07'),
	(3,'Betbull September','Betbull',NULL,NULL,NULL,'2017-09-10 22:02:20','2017-09-10 22:02:20'),
	(4,'#GetLoud','Nordoff Robbins',NULL,NULL,NULL,'2017-09-10 22:02:40','2017-09-10 22:02:40'),
	(5,'#MadeBy 2017','Jacobs Creek',NULL,NULL,NULL,'2017-09-10 23:12:17','2017-09-10 23:12:17'),
	(6,'Simba Launch YouTube','Simba Sleep',NULL,NULL,NULL,'2017-09-10 23:12:31','2017-09-10 23:12:31'),
	(7,'#GetLoud Ticket Sales','Nordoff Robbins',NULL,NULL,NULL,'2017-09-10 23:12:56','2017-09-10 23:12:56'),
	(8,'Skepta Alexandra Palace','Apple Music',NULL,'[\"FESTIVAL\",\"LIFESTLE\",\"MUSIC\"]',NULL,'2017-09-10 23:13:16','2017-12-07 18:04:00'),
	(11,'BAND','BAND',NULL,NULL,NULL,'2017-11-22 10:24:35','2017-11-22 10:24:35'),
	(12,'Test','Client',NULL,NULL,NULL,'2017-11-25 00:13:14','2017-11-25 00:13:14'),
	(13,'What?','A Tribe Called Quest',25000.00,'[\"MUSIC\"]',NULL,'2017-12-13 23:52:32','2017-12-20 23:08:44');

/*!40000 ALTER TABLE `campaigns` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
