-- --------------------------------------------------------

--
-- Table structure for table `new_admin_menu`
--

DROP TABLE IF EXISTS `new_admin_menu`;
CREATE TABLE IF NOT EXISTS `new_admin_menu` (
  `am_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `am_parent_id` int(11) unsigned DEFAULT NULL,
  `am_name` varchar(255) NOT NULL,
  `am_route` varchar(255) DEFAULT NULL,
  `am_order` int(10) unsigned NOT NULL DEFAULT 0,
  `am_created_at` datetime DEFAULT current_timestamp(),
  `am_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`am_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `new_admin_menu_permission`
--

DROP TABLE IF EXISTS `new_admin_menu_permission`;
CREATE TABLE IF NOT EXISTS `new_admin_menu_permission` (
  `mb_id` varchar(60) NOT NULL,
  `admin_menu_id` int(11) NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT 0,
  `write` tinyint(1) NOT NULL DEFAULT 0,
  `delete` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`mb_id`,`admin_menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `new_banner`
--

DROP TABLE IF EXISTS `new_banner`;
CREATE TABLE IF NOT EXISTS `new_banner` (
  `bn_id` int(11) NOT NULL AUTO_INCREMENT,
  `bn_title` varchar(255) NOT NULL DEFAULT '',
  `bn_image` varchar(255) NOT NULL DEFAULT '',
  `bn_mobile_image` varchar(255) NOT NULL DEFAULT '',
  `bn_alt` varchar(255) NOT NULL DEFAULT '',
  `bn_url` varchar(255) NOT NULL DEFAULT '',
  `bn_position` varchar(50) NOT NULL DEFAULT '',
  `bn_target` varchar(50) NOT NULL DEFAULT '',
  `bn_start_datetime` datetime DEFAULT NULL,
  `bn_end_datetime` datetime DEFAULT NULL,
  `bn_order` int(11) NOT NULL DEFAULT 0,
  `bn_status` tinyint(4) NOT NULL DEFAULT 0,
  `bn_hit` int(11) NOT NULL DEFAULT 0,
  `bn_created_at` datetime DEFAULT current_timestamp(),
  `bn_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`bn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `new_config`
--

DROP TABLE IF EXISTS `new_config`;
CREATE TABLE IF NOT EXISTS `new_config` (
  `cf_id` int(11) NOT NULL AUTO_INCREMENT,
  `cf_site_title` varchar(255) NOT NULL DEFAULT '',
  `cf_site_description` text NOT NULL,
  `cf_site_keyword` text NOT NULL,
  `cf_admin` varchar(100) NOT NULL DEFAULT '',
  `cf_privacy_officer_name` varchar(100) NOT NULL DEFAULT '',
  `cf_privacy_officer_email` varchar(100) NOT NULL DEFAULT '',
  `cf_use_shop` tinyint(4) NOT NULL DEFAULT 0,
  `cf_use_community` tinyint(4) NOT NULL DEFAULT 0,
  `cf_company_name` varchar(255) NOT NULL DEFAULT '',
  `cf_biz_reg_no` varchar(20) NOT NULL DEFAULT '',
  `cf_ceo_name` varchar(100) NOT NULL DEFAULT '',
  `cf_main_phone_number` varchar(20) NOT NULL DEFAULT '',
  `cf_fax_number` varchar(20) DEFAULT NULL,
  `cf_ecom_reg_no` varchar(50) NOT NULL DEFAULT '',
  `cf_add_telecom_no` varchar(50) DEFAULT NULL,
  `cf_biz_zip_code` varchar(10) NOT NULL DEFAULT '',
  `cf_biz_address` varchar(255) NOT NULL DEFAULT '',
  `cf_biz_address_detail` varchar(255) DEFAULT NULL,
  `cf_biz_address_etc` varchar(255) DEFAULT NULL,
  `cf_possible_ip` text NOT NULL,
  `cf_intercept_ip` text NOT NULL,
  `cf_add_script` text NOT NULL,
  `cf_add_css` text NOT NULL,
  `cf_add_meta` text NOT NULL,
  `cf_1_subj` varchar(255) NOT NULL DEFAULT '',
  `cf_2_subj` varchar(255) NOT NULL DEFAULT '',
  `cf_3_subj` varchar(255) NOT NULL DEFAULT '',
  `cf_4_subj` varchar(255) NOT NULL DEFAULT '',
  `cf_5_subj` varchar(255) NOT NULL DEFAULT '',
  `cf_6_subj` varchar(255) NOT NULL DEFAULT '',
  `cf_7_subj` varchar(255) NOT NULL DEFAULT '',
  `cf_8_subj` varchar(255) NOT NULL DEFAULT '',
  `cf_9_subj` varchar(255) NOT NULL DEFAULT '',
  `cf_10_subj` varchar(255) NOT NULL DEFAULT '',
  `cf_1` varchar(255) NOT NULL DEFAULT '',
  `cf_2` varchar(255) NOT NULL DEFAULT '',
  `cf_3` varchar(255) NOT NULL DEFAULT '',
  `cf_4` varchar(255) NOT NULL DEFAULT '',
  `cf_5` varchar(255) NOT NULL DEFAULT '',
  `cf_6` varchar(255) NOT NULL DEFAULT '',
  `cf_7` varchar(255) NOT NULL DEFAULT '',
  `cf_8` varchar(255) NOT NULL DEFAULT '',
  `cf_9` varchar(255) NOT NULL DEFAULT '',
  `cf_10` varchar(255) NOT NULL DEFAULT '',
  `cf_theme` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`cf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `new_content`
--

DROP TABLE IF EXISTS `new_content`;
CREATE TABLE IF NOT EXISTS `new_content` (
  `code` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` longtext NOT NULL DEFAULT '',
  `seo_title` varchar(255) NOT NULL DEFAULT '',
  `mobile_content` longtext NOT NULL DEFAULT '',
  `hit` int(11) NOT NULL DEFAULT 0,
  `head_include_file` varchar(255) NOT NULL DEFAULT '',
  `foot_include_file` varchar(255) NOT NULL DEFAULT '',
  `head_image` varchar(255) NOT NULL DEFAULT '',
  `foot_image` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`code`),
  KEY `seo_title` (`seo_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `new_member`
--

DROP TABLE IF EXISTS `new_member`;
CREATE TABLE IF NOT EXISTS `new_member` (
  `mb_no` int(11) NOT NULL AUTO_INCREMENT,
  `mb_id` varchar(100) NOT NULL,
  `mb_password` varchar(255) NOT NULL,
  `mb_name` varchar(100) NOT NULL,
  `mb_nick` varchar(100) NOT NULL,
  `mb_nick_date` date DEFAULT NULL,
  `mb_email` varchar(255) NOT NULL,
  `mb_homepage` varchar(255) DEFAULT NULL,
  `mb_level` tinyint(4) NOT NULL DEFAULT 0,
  `mb_sex` ENUM('M', 'F') NOT NULL,
  `mb_birth` date DEFAULT NULL,
  `mb_tel` varchar(20) DEFAULT NULL,
  `mb_hp` varchar(20) DEFAULT NULL,
  `mb_certify` varchar(20) DEFAULT NULL,
  `mb_adult` tinyint(1) NOT NULL DEFAULT 0,
  `mb_dupinfo` varchar(255) DEFAULT NULL,
  `mb_zip` varchar(6) DEFAULT NULL,
  `mb_addr1` varchar(255) DEFAULT NULL,
  `mb_addr2` varchar(255) DEFAULT NULL,
  `mb_addr3` varchar(255) DEFAULT NULL,
  `mb_addr_jibeon` varchar(255) DEFAULT NULL,
  `mb_signature` text DEFAULT NULL,
  `mb_recommend` varchar(100) DEFAULT NULL,
  `mb_point` int(11) NOT NULL DEFAULT 0,
  `mb_last_login_at` datetime DEFAULT NULL,
  `mb_last_login_ip` varchar(45) DEFAULT NULL,
  `mb_signup_ip` varchar(45) DEFAULT NULL,
  `mb_leave_date` datetime DEFAULT NULL,
  `mb_intercept_date` datetime DEFAULT NULL,
  `mb_email_verified_at` datetime DEFAULT NULL,
  `mb_email_verified_code` varchar(255) DEFAULT NULL,
  `mb_memo` text DEFAULT NULL,
  `mb_lost_certify` varchar(255) DEFAULT NULL,
  `mb_mailling` tinyint(1) NOT NULL DEFAULT 0,
  `mb_sms` tinyint(1) NOT NULL DEFAULT 0,
  `mb_open` tinyint(1) NOT NULL DEFAULT 0,
  `mb_open_date` date DEFAULT NULL,
  `mb_memo_call` varchar(255) DEFAULT NULL,
  `mb_memo_cnt` int(11) NOT NULL DEFAULT 0,
  `mb_scrap_cnt` int(11) NOT NULL DEFAULT 0,
  `mb_1` varchar(255) DEFAULT NULL,
  `mb_2` varchar(255) DEFAULT NULL,
  `mb_3` varchar(255) DEFAULT NULL,
  `mb_4` varchar(255) DEFAULT NULL,
  `mb_5` varchar(255) DEFAULT NULL,
  `mb_6` varchar(255) DEFAULT NULL,
  `mb_7` varchar(255) DEFAULT NULL,
  `mb_8` varchar(255) DEFAULT NULL,
  `mb_9` varchar(255) DEFAULT NULL,
  `mb_10` varchar(255) DEFAULT NULL,
  `mb_created_at` datetime DEFAULT current_timestamp(),
  `mb_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`mb_no`),
  UNIQUE KEY `mb_id` (`mb_id`),
  KEY `mb_last_login_at` (`mb_last_login_at`),
  KEY `mb_created_at` (`mb_created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- --------------------------------------------------------

--
-- Table structure for table `new_member_config`
--

DROP TABLE IF EXISTS `new_member_config`;
CREATE TABLE IF NOT EXISTS `new_member_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `use_address` tinyint(1) DEFAULT NULL,
  `required_address` tinyint(1) DEFAULT NULL,
  `use_signature` tinyint(1) DEFAULT NULL,
  `required_signature` tinyint(1) DEFAULT NULL,
  `use_telephone` tinyint(1) DEFAULT NULL,
  `required_telephone` tinyint(1) DEFAULT NULL,
  `use_phone` tinyint(1) DEFAULT NULL,
  `required_phone` tinyint(1) DEFAULT NULL,
  `signup_level` int(11) DEFAULT NULL,
  `signup_point` int(11) DEFAULT NULL,
  `use_member_image` tinyint(1) DEFAULT NULL,
  `upload_permission_level` int(11) DEFAULT NULL,
  `member_image_size` int(11) DEFAULT NULL,
  `member_image_width` int(11) DEFAULT NULL,
  `member_image_height` int(11) DEFAULT NULL,
  `use_recommend` tinyint(1) DEFAULT NULL,
  `recommend_point` int(11) DEFAULT NULL,
  `prohibit_word` text DEFAULT NULL,
  `prohibit_domain` text DEFAULT NULL,
  `signup_terms` text DEFAULT NULL,
  `privacy_policy` text DEFAULT NULL,
  `retention_period` int(11) DEFAULT NULL,
  `use_email_certify` tinyint(1) DEFAULT NULL,
  `use_authentication` tinyint(1) DEFAULT NULL,
  `is_auth_production` tinyint(1) DEFAULT NULL,
  `authentication_required` tinyint(1) DEFAULT NULL,
  `auth_service` varchar(255) DEFAULT NULL,
  `cert_service` tinyint(1) DEFAULT NULL,
  `cert_kg_mid` varchar(255) DEFAULT NULL,
  `cert_kg_cd` varchar(255) DEFAULT NULL,
  `cert_kcp_cd` varchar(255) DEFAULT NULL,
  `cert_limit` int(11) DEFAULT NULL,
  `use_point` tinyint(1) DEFAULT NULL,
  `point_term` int(11) DEFAULT NULL,
  `login_point` int(11) DEFAULT NULL,
  `memo_send_point` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `new_menu`
--

DROP TABLE IF EXISTS `new_menu`;
CREATE TABLE IF NOT EXISTS `new_menu` (
  `me_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `me_parent_id` int(20) unsigned DEFAULT NULL,
  `me_name` varchar(255) NOT NULL DEFAULT '',
  `me_link` varchar(255) NOT NULL DEFAULT '',
  `me_target` varchar(255) NOT NULL DEFAULT '',
  `me_order` int(11) NOT NULL DEFAULT 0,
  `me_use` tinyint(4) NOT NULL DEFAULT 0,
  `me_mobile_use` tinyint(4) NOT NULL DEFAULT 0,
  `am_created_at` datetime DEFAULT current_timestamp(),
  `am_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`me_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `new_popup`
--

DROP TABLE IF EXISTS `new_popup`;
CREATE TABLE IF NOT EXISTS `new_popup` (
  `pu_id` int(11) NOT NULL AUTO_INCREMENT,
  `pu_division` varchar(10) NOT NULL DEFAULT 'both',
  `pu_begin_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pu_end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pu_disable_hours` int(11) NOT NULL DEFAULT '0',
  `pu_left` int(11) NOT NULL DEFAULT '0',
  `pu_top` int(11) NOT NULL DEFAULT '0',
  `pu_height` int(11) NOT NULL DEFAULT '0',
  `pu_width` int(11) NOT NULL DEFAULT '0',
  `pu_auto_height` tinyint(4) NOT NULL DEFAULT '0',
  `pu_subject` text NOT NULL,
  `pu_content` text NOT NULL,
  `pu_mobile_content` text NOT NULL,
  `pu_created_at` datetime DEFAULT current_timestamp(),
  `pu_updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`pu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `new_qa_config`
--
DROP TABLE IF EXISTS `new_qa_config`;
CREATE TABLE IF NOT EXISTS `new_qa_config` (
  `qa_id` int(11) NOT NULL AUTO_INCREMENT,
  `qa_title` varchar(255) NOT NULL DEFAULT '',
  `qa_category` varchar(255) NOT NULL DEFAULT '',
  `qa_use_email` tinyint(4) NOT NULL DEFAULT 0,
  `qa_req_email` tinyint(4) NOT NULL DEFAULT 0,
  `qa_use_hp` tinyint(4) NOT NULL DEFAULT 0,
  `qa_req_hp` tinyint(4) NOT NULL DEFAULT 0,
  `qa_use_sms` tinyint(4) NOT NULL DEFAULT 0,
  `qa_send_number` varchar(255) NOT NULL DEFAULT '0',
  `qa_use_editor` tinyint(4) NOT NULL DEFAULT 0,
  `qa_image_width` int(11) NOT NULL DEFAULT 0,
  `qa_upload_size` int(11) NOT NULL DEFAULT 0,
  `qa_insert_content` text NOT NULL DEFAULT '',
  PRIMARY KEY (`qa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `new_social_provider`
--
DROP TABLE IF EXISTS `new_social_provider`;
CREATE TABLE IF NOT EXISTS `new_social_provider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_name` varchar(100) NOT NULL COMMENT '서비스 이름 (예: 네이버, 페이스북)',
  `provider_key` varchar(50) NOT NULL COMMENT '서비스 키 (예: naver, facebook)',
  `client_id` varchar(255) DEFAULT NULL COMMENT 'Client ID',
  `client_secret` varchar(255) DEFAULT NULL COMMENT 'Client Secret',
  `redirect_url` varchar(255) DEFAULT NULL COMMENT 'Redirect URL',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '소셜 로그인 활성화 여부',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider_key` (`provider_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `new_social_provider_config`
--
DROP TABLE IF EXISTS `new_social_provider_config`;
CREATE TABLE IF NOT EXISTS `new_social_provider_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_key` varchar(50) NOT NULL COMMENT '참조용 서비스 키 (예: naver, facebook)',
  `config_name` varchar(100) NOT NULL COMMENT '추가 설정 이름 (예: apikey)',
  `config_value` varchar(255) DEFAULT NULL COMMENT '추가 설정 값',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider_key` (`provider_key`,`config_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;