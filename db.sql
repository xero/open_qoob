--
-- Database: `qoob`
--

-- --------------------------------------------------------

--
-- Table structure for table `stats`
--

CREATE TABLE IF NOT EXISTS `stats` (
  `auto_id` int(255) NOT NULL AUTO_INCREMENT,
  `time` int(255) NOT NULL,
  `domain` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `uri` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `url_checksum` int(10) NOT NULL,
  `verb` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `ajax` int(1) NOT NULL,
  `status` int(3) NOT NULL DEFAULT '200',
  `referer` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `referer_domain` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `referer_checksum` int(10) NOT NULL,
  `browser` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `version` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `platform` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `useragent` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `ipaddress` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `hostname` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `location` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`auto_id`),
  KEY `url_checksum` (`url_checksum`,`referer_checksum`,`browser`,`platform`,`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;
