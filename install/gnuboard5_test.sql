-- --------------------------------------------------------

--
-- Table structure for table `g5_auth`
--

DROP TABLE IF EXISTS `g5_auth`;
CREATE TABLE IF NOT EXISTS `g5_auth` (
  `mb_id` varchar(20) NOT NULL default '',
  `au_menu` varchar(50) NOT NULL default '',
  `au_auth` set('r','w','d') NOT NULL default '',
  PRIMARY KEY  (`mb_id`,`au_menu`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
