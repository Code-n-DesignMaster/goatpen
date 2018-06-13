
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
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL DEFAULT '',
  `email` varchar(256) NOT NULL DEFAULT '',
  `owner` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Will Woodward','will@willjw.co.uk',1,'2017-08-30 18:35:28','2017-10-05 23:51:48'),(2,'Harry Hugo','harry@goatagency.com',1,'2017-09-05 22:29:13','2017-10-05 23:48:49'),(3,'CJ Murray','cj@goatagency.com',0,'2017-09-05 22:30:54','2017-10-05 23:48:49'),(4,'Ollie Bond','ollie@goatagency.com',0,'2017-09-05 22:31:08','2017-10-05 23:48:49'),(5,'Frankie Hobbs','frankie@goatagency.com',0,'2017-09-05 22:31:21','2017-10-05 23:48:49'),(6,'Tom Bore','tom@goatagency.com',0,'2017-09-05 22:31:35','2017-10-05 23:48:49'),(7,'Ben Smith','ben@goatagency.com',0,'2017-09-05 22:32:06','2017-10-05 23:48:49'),(8,'Tim Hepper','tim@goatagency.com',0,'2017-09-05 22:32:17','2017-10-05 23:48:49'),(9,'Nanna Haggstrom','nanna@goatagency.com',0,'2017-09-05 22:32:44','2017-10-05 23:48:49'),(10,'Jack Sear','jack@goatagency.com',0,'2017-09-13 14:05:57','2017-10-05 23:48:49'),(11,'Hannah Ryan','hannah@goatagency.com',0,'2017-09-13 14:06:13','2017-10-05 23:48:49'),(13,'Giselle Elsom','giselle@goatagency.com',0,'2017-10-10 10:42:16','2017-10-10 10:42:16'),(14,'Callie Robertson','callie@goatagency.com',0,'2017-10-10 10:46:56','2017-10-10 10:46:56'),(15,'Emily Hall','emily@goatagency.com',0,'2017-10-11 11:41:43','2017-10-11 11:41:43');
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

