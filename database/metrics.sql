# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.19-0ubuntu0.16.04.1)
# Database: goatpen
# Generation Time: 2018-01-06 00:21:04 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table metrics
# ------------------------------------------------------------

DROP TABLE IF EXISTS `metrics`;

CREATE TABLE `metrics` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL DEFAULT '',
  `scope` varchar(16) NOT NULL DEFAULT '',
  `stats` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `automated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `type` varchar(16) NOT NULL DEFAULT 'Number',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

LOCK TABLES `metrics` WRITE;
/*!40000 ALTER TABLE `metrics` DISABLE KEYS */;

INSERT INTO `metrics` (`id`, `name`, `scope`, `stats`, `automated`, `type`, `created_at`, `updated_at`)
VALUES
	(1,'Page Likes','Influencer',0,0,'Number','2017-09-10 18:54:19','2017-09-13 23:14:56'),
	(2,'Followers','Influencer',0,0,'Number','2017-09-10 18:54:19','2017-09-13 23:14:56'),
	(3,'Subscribers','Influencer',0,0,'Number','2017-09-10 18:54:19','2017-09-13 23:14:56'),
	(5,'Reach','Campaign',0,0,'Number','2017-09-10 18:54:19','2017-09-13 23:14:56'),
	(6,'Total Engagements','Campaign',0,0,'Number','2017-09-10 18:54:19','2017-09-13 23:14:56'),
	(7,'Post Reactions','Campaign',0,0,'Number','2017-09-10 18:54:19','2017-09-13 23:14:56'),
	(8,'Post Comments','Campaign',0,0,'Number','2017-09-10 18:54:19','2017-09-13 23:14:56'),
	(9,'Downloads','Campaign',0,0,'Number','2017-09-10 18:54:19','2017-09-13 23:14:56'),
	(10,'Profile Engagement Rate','Campaign',0,1,'Percent','2017-09-10 18:54:19','2017-09-16 12:49:10'),
	(11,'Cost Per Click','Campaign',0,1,'Money','2017-09-10 18:54:19','2017-09-16 12:15:27'),
	(12,'Link Clicks','Campaign',0,0,'Number','2017-09-10 18:54:19','2017-09-13 23:14:56'),
	(13,'Impressions','Campaign',0,0,'Number','2017-09-10 18:54:19','2017-09-13 23:14:56'),
	(14,'Cost Per Impression','Campaign',0,1,'Money','2017-09-10 18:54:19','2017-09-16 12:15:29'),
	(15,'Video Views','Campaign',0,0,'Number','2017-09-10 18:54:19','2017-09-13 23:14:56'),
	(16,'Retweets','Campaign',0,0,'Number','2017-09-10 18:54:19','2017-09-13 23:14:56'),
	(18,'Replies','Campaign',0,0,'Number','2017-09-10 18:54:19','2017-09-13 23:14:56'),
	(19,'Viewthrough Rate','Campaign',0,1,'Percent','2017-09-10 18:54:19','2017-09-16 12:35:01'),
	(20,'Post Likes','Campaign',0,0,'Number','2017-09-10 18:54:19','2018-01-06 00:20:51'),
	(22,'Screenshots','Campaign',0,0,'Number','2017-09-10 18:54:19','2017-09-13 23:14:56'),
	(23,'Cost Per View','Campaign',0,1,'Money','2017-09-10 18:54:19','2017-09-16 12:15:30'),
	(24,'Cost Per Engagement','Campaign',0,1,'Money','2017-09-10 18:54:19','2017-09-16 12:15:28'),
	(25,'Depositors','Campaign',1,0,'Number','2017-09-10 18:54:19','2017-12-11 20:16:28'),
	(26,'Average Video Views','Influencer',0,0,'Number','2017-09-10 23:04:22','2017-09-13 23:14:56'),
	(27,'Average Engagements','Influencer',0,0,'Number','2017-09-10 23:04:41','2017-10-24 22:45:37'),
	(28,'Price Paid','Campaign',0,0,'Money','2017-09-13 09:01:10','2017-09-16 11:59:00'),
	(29,'Post Engagement Rate','Campaign',0,1,'Percent','2017-09-16 12:26:01','2017-09-16 12:26:01'),
	(30,'Cumulative Following','Campaign',0,0,'Number','2017-11-22 10:28:12','2017-12-08 14:20:16'),
	(31,'Story Views','Campaign',0,0,'Number','2017-11-22 10:28:29','2017-11-22 10:28:29'),
	(32,'Taps','Campaign',0,0,'Number','2017-11-22 10:28:39','2017-11-22 10:28:39'),
	(33,'Registrations','Campaign',1,0,'Number','2017-11-22 10:28:47','2017-12-11 20:16:16'),
	(34,'Installs','Campaign',1,0,'Number','2017-11-22 10:28:54','2017-12-11 19:39:12'),
	(35,'Shares','Campaign',0,0,'Number','2017-12-07 14:17:03','2017-12-07 14:17:03'),
	(37,'Price Per Install','Campaign',0,1,'Money','2017-12-08 12:07:29','2017-12-11 23:29:35'),
	(38,'Price Per Registration','Campaign',0,1,'Money','2017-12-08 12:08:26','2017-12-11 23:29:25'),
	(39,'Price Per Depositor','Campaign',0,1,'Money','2017-12-08 12:08:42','2017-12-11 23:29:26'),
	(40,'Cumulative Subscribers','Campaign',0,0,'Number','2017-12-08 17:23:56','2017-12-11 23:29:32'),
	(41,'Cost Per Install','Campaign',1,1,'Money','2017-12-11 23:29:59','2017-12-11 23:30:36'),
	(42,'Cost Per Registration','Campaign',1,1,'Money','2017-12-11 23:30:18','2017-12-11 23:30:34'),
	(43,'Cost Per Depositor','Campaign',1,1,'Money','2017-12-11 23:30:33','2017-12-11 23:30:33');

/*!40000 ALTER TABLE `metrics` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
