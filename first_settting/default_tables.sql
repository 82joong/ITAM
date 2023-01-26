-- MySQL dump 10.13  Distrib 5.6.43, for FreeBSD11.2 (amd64)
--
-- Host: localhost    Database: ci3_default
-- ------------------------------------------------------
-- Server version	5.1.44-log

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
-- Table structure for table `config_tb`
--

DROP TABLE IF EXISTS `config_tb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config_tb` (
  `cfg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cfg_cate` varchar(20) NOT NULL DEFAULT '',
  `cfg_ctrl` varchar(20) NOT NULL DEFAULT '',
  `cfg_var` varchar(20) NOT NULL DEFAULT '',
  `cfg_val` mediumtext NOT NULL,
  `cfg_mixed` char(1) NOT NULL DEFAULT 'N',
  `cfg_created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cfg_updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`cfg_id`),
  UNIQUE KEY `cfg_name` (`cfg_cate`,`cfg_ctrl`,`cfg_var`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config_tb`
--

LOCK TABLES `config_tb` WRITE;
/*!40000 ALTER TABLE `config_tb` DISABLE KEYS */;
INSERT INTO `config_tb` VALUES (29,'service','define','unit','$','N','2016-12-28 18:29:42','2019-07-02 18:03:29'),(30,'service','define','ADMIN_DIR','adminpanel','N','2017-01-09 13:33:22','2017-01-09 13:33:22'),(31,'service','define','ORDER_KR_MAX_WEIGHT','15','N','2017-01-09 13:33:22','2017-01-09 13:33:22'),(32,'service','define','ORDERABLE_NATIONS','[\"KR\",\"US\",\"SG\",\"CN\",\"JP\"]','Y','2017-01-09 13:33:22','2017-01-09 13:33:22'),(33,'service','define','ORDERABLE_NATION_TIT','{\"KR\":\"\\ud55c\\uad6d\",\"US\":\"\\ubbf8\\uad6d\",\"SG\":\"\\uc2f1\\uac00\\ud3f4\",\"CN\":\"\\uc911\\uad6d\",\"JP\":\"\\uc77c\\ubcf8\"}','Y','2017-01-09 13:33:22','2017-01-09 13:33:22');
/*!40000 ALTER TABLE `config_tb` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;


--
-- Table structure for table `config_tb`
--

DROP TABLE IF EXISTS `admin_tb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_tb` (
    `a_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `a_firstname` varchar(50) NOT NULL,
    `a_lastname` varchar(50) NOT NULL,
    `a_email` varchar(50) NOT NULL,
    `a_loginid` varchar(50) NOT NULL,
    `a_passwd` varchar(100) NOT NULL,
    `a_permission` mediumtext NOT NULL,
    `a_ip_filter` enum('YES','NO') DEFAULT 'NO',
    `a_allow_ips` text,
    `a_created_at` datetime NOT NULL,
    `a_updated_at` datetime NOT NULL,
    `a_lastlogin_at` datetime NOT NULL,
    `a_level` int(11) NOT NULL,
    PRIMARY KEY (`a_id`),
    UNIQUE KEY `a_loginid` (`a_loginid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8; 
/*!40101 SET character_set_client = @saved_cs_client */;

/* Admin Login ID / PW : admin / smartdev123!@# */
INSERT INTO `admin_tb` VALUES (1,'smart','dev','admin@sample.com','admin','f3b8da02a83837822775da35e0a2738c:jp','a:11:{s:10:\"pg_setting\";b:0;s:12:\"cost_setting\";b:0;s:20:\"staff_rest_time_view\";b:0;s:19:\"order_list_complete\";b:0;s:9:\"cost_edit\";b:0;s:15:\"direct_complete\";b:0;s:22:\"modify_tracking_number\";b:0;s:18:\"complete_to_closed\";b:0;s:20:\"change_income_status\";b:0;s:19:\"emp_status_exchange\";b:0;s:16:\"config_access_ip\";b:0;}','NO','','2020-05-26 17:17:38','2020-05-28 09:11:42','2020-05-28 09:11:42',9);



DROP TABLE IF EXISTS `admin_setting_tb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_setting_tb` (
    `as_id` int(11) NOT NULL AUTO_INCREMENT,
    `as_order_report_point1` int(11) NOT NULL,
    `as_order_report_point2` int(11) NOT NULL,
    `as_cart_kr_msg` text NOT NULL,
    `as_cart_nation_msg` text,
    `as_payment_method` varchar(30) NOT NULL DEFAULT 'PAYPAL',
    `as_payment_after_method` varchar(255) NOT NULL DEFAULT '',
    `as_allow_ips` text,
    `as_alarm_email` text NOT NULL,
    `as_hash_tag_filter` text NOT NULL,
    `as_hash_tag_limit` text NOT NULL,
    `as_over_rule_id` int(11) NOT NULL,
    `as_detail_rule_id` int(11) NOT NULL,
    `as_detail_rule_msg` varchar(255) NOT NULL,
    `as_skip_blog_urls` text NOT NULL,
    `as_cancelable_hour` varchar(10) NOT NULL,
    `as_gift_price` double(11,2) unsigned NOT NULL,
    `as_gift_quantity` int(11) NOT NULL,
    `as_level_info` mediumtext,
    `as_holiday` text,
    `as_kr_payment_currency` double(11,2) DEFAULT NULL,
    `as_mtail_usd_currency` double(11,2) NOT NULL,
    `as_created_at` datetime NOT NULL,
    `as_updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`as_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE `history_tb` (
    `h_id` int(11) NOT NULL AUTO_INCREMENT,
    `h_loginid` varchar(50) NOT NULL,
    `h_name` varchar(50) NOT NULL,
    `h_ip` varchar(20) NOT NULL,
    `h_act_table` varchar(30) NOT NULL,
    `h_act_mode` varchar(20) NOT NULL,
    `h_act_key` varchar(30) NOT NULL,
    `h_serialize` text NOT NULL,
    `h_created_at` datetime NOT NULL,
    PRIMARY KEY (`h_id`),
    KEY `h_act_table` (`h_act_table`,`h_act_key`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-07-04 12:24:27
