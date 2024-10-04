-- --------------------------------------------------------

--
-- Table structure for table `new_admin_menu`
--
DROP TABLE IF EXISTS `new_admin_menu`;
CREATE TABLE IF NOT EXISTS `new_admin_menu` (
  `am_id` int(11) unsigned unsigned NOT NULL AUTO_INCREMENT,
  `am_parent_id` int(11) unsigned unsigned DEFAULT NULL COMMENT '부모 ID',
  `am_name` varchar(255) NOT NULL COMMENT '관리자 메뉴 이름',
  `am_route` varchar(255) DEFAULT NULL COMMENT '라우터 이름',
  `am_order` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '정렬순서',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`am_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `new_admin_menu_permission`
--
DROP TABLE IF EXISTS `new_admin_menu_permission`;
CREATE TABLE IF NOT EXISTS `new_admin_menu_permission` (
  `mb_id` varchar(60) NOT NULL COMMENT '회원 아이디',
  `admin_menu_id` int(11) unsigned NOT NULL COMMENT '관리자 메뉴 ID',
  `read` tinyint(1) NOT NULL DEFAULT 0 COMMENT '읽기 권한',
  `write` tinyint(1) NOT NULL DEFAULT 0 COMMENT '쓰기 권한',
  `delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT '삭제 권한',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`mb_id`,`admin_menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `new_banner`
--
DROP TABLE IF EXISTS `new_banner`;
CREATE TABLE IF NOT EXISTS `new_banner` (
  `bn_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bn_title` varchar(255) NOT NULL DEFAULT '' COMMENT '배너 타이틀',
  `bn_image` varchar(255) NOT NULL DEFAULT '' COMMENT '이미지',
  `bn_mobile_image` varchar(255) DEFAULT '' COMMENT '모바일 이미지',
  `bn_alt` varchar(255) DEFAULT '' COMMENT '이미지 대체 텍스트',
  `bn_url` varchar(255) NOT NULL DEFAULT '' COMMENT '링크 URL',
  `bn_position` varchar(50) NOT NULL DEFAULT '' COMMENT '출력위치',
  `bn_target` varchar(50) NOT NULL DEFAULT '' COMMENT '새 탭',
  `bn_start_datetime` datetime DEFAULT NULL COMMENT '시작일시',
  `bn_end_datetime` datetime DEFAULT NULL COMMENT '종료일시',
  `bn_order` int(11) NOT NULL DEFAULT 0 COMMENT '전시순서',
  `bn_is_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '상태',
  `bn_hit` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '클릭 횟수',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`bn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `new_config`
--
DROP TABLE IF EXISTS `new_config`;
CREATE TABLE IF NOT EXISTS `new_config` (
  `cf_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cf_site_title` varchar(255) NOT NULL DEFAULT '' COMMENT '사이트 제목',
  `cf_site_description` text NOT NULL COMMENT '사이트 설명',
  `cf_site_keyword` text NOT NULL COMMENT '사이트 키워드 (쉼표로 구분)',
  `cf_admin` varchar(60) NOT NULL DEFAULT '' COMMENT '최고관리자 아이디',
  `cf_privacy_officer_name` varchar(100) DEFAULT NULL COMMENT '개인정보 보호 책임자 이름',
  `cf_privacy_officer_email` varchar(100) DEFAULT NULL COMMENT '개인정보 보호 책임자 이메일',
  `cf_use_shop` tinyint(1) NOT NULL DEFAULT 0 COMMENT '쇼핑몰 사용 여부',
  `cf_use_community` tinyint(1) NOT NULL DEFAULT 0 COMMENT '커뮤니티 사용 여부',
  `cf_company_name` varchar(255) DEFAULT NULL COMMENT '회사명',
  `cf_biz_reg_no` varchar(20) DEFAULT NULL COMMENT '사업자 등록 번호',
  `cf_ceo_name` varchar(100) DEFAULT NULL COMMENT '대표자 이름',
  `cf_main_phone_number` varchar(20) DEFAULT NULL COMMENT '대표 전화번호',
  `cf_fax_number` varchar(20) DEFAULT NULL COMMENT '팩스 번호',
  `cf_ecom_reg_no` varchar(50) DEFAULT NULL COMMENT '통신판매업신고번호',
  `cf_add_telecom_no` varchar(50) DEFAULT NULL COMMENT '부가통신사업자번호',
  `cf_biz_zip_code` varchar(10) DEFAULT NULL COMMENT '사업장 우편번호',
  `cf_biz_address` varchar(255) DEFAULT NULL COMMENT '사업장 주소',
  `cf_biz_address_detail` varchar(255) DEFAULT NULL COMMENT '사업장 상세 주소',
  `cf_biz_address_etc` varchar(255) DEFAULT NULL COMMENT '참고 항목',
  `cf_possible_ip` text DEFAULT NULL COMMENT '허용된 IP',
  `cf_intercept_ip` text DEFAULT NULL COMMENT '차단된 IP',
  `cf_add_script` text DEFAULT NULL COMMENT '추가 스크립트',
  `cf_add_css` text DEFAULT NULL COMMENT '추가 CSS',
  `cf_add_meta` text DEFAULT NULL COMMENT '추가 메타 데이터',
  `cf_theme` varchar(100) DEFAULT NULL COMMENT '테마',
  PRIMARY KEY (`cf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `new_content`
--
DROP TABLE IF EXISTS `new_content`;
CREATE TABLE IF NOT EXISTS `new_content` (
  `code` varchar(50) NOT NULL COMMENT '컨텐츠 코드',
  `title` varchar(255) NOT NULL COMMENT '컨텐츠 타이틀',
  `content` longtext NOT NULL COMMENT '내용',
  `seo_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO 제목',
  `hit` int(11) NOT NULL DEFAULT 0 COMMENT '조회수',
  `head_include_file` varchar(255) DEFAULT NULL COMMENT '상단 파일 경로',
  `foot_include_file` varchar(255) DEFAULT NULL COMMENT '하단 파일 경로',
  `head_image` varchar(255) DEFAULT NULL COMMENT '상단 이미지',
  `foot_image` varchar(255) DEFAULT NULL COMMENT '하단 이미지',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`code`),
  KEY `seo_title_index` (`seo_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `new_faq`
--
DROP TABLE IF EXISTS `new_faq`;
CREATE TABLE IF NOT EXISTS `new_faq` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `faq_category_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'FAQ 카테고리 ID',
  `question` text NOT NULL COMMENT '질문',
  `answer` text NOT NULL COMMENT '답변',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '활성화 여부',
  `order` int(11) NOT NULL DEFAULT 0 COMMENT '정렬 순서',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`id`),
  KEY `idx_faq_category_id` (`faq_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `new_faq_category`
--
DROP TABLE IF EXISTS `new_faq_category`;
CREATE TABLE IF NOT EXISTS `new_faq_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '제목',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '활성화 여부',
  `order` int(11) NOT NULL DEFAULT 0 COMMENT '정렬 순서',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `new_member`
--
DROP TABLE IF EXISTS `new_member`;
CREATE TABLE IF NOT EXISTS `new_member` (
  `mb_no` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mb_id` varchar(60) NOT NULL COMMENT '아이디',
  `mb_password` varchar(255) NOT NULL DEFAULT '' COMMENT '비밀번호',
  `mb_name` varchar(50) NOT NULL DEFAULT '' COMMENT '회원 실명',
  `mb_nick` varchar(100) NOT NULL COMMENT '회원 닉네임',
  `mb_nick_date` date DEFAULT NULL COMMENT '회원 닉네임 변경일',
  `mb_image` varchar(255) DEFAULT NULL COMMENT '회원 이미지',
  `mb_email` varchar(255) NOT NULL COMMENT '이메일',
  `mb_homepage` varchar(255) NOT NULL DEFAULT '' COMMENT '홈페이지',
  `mb_level` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '회원레벨',
  `mb_sex` char(1) NOT NULL DEFAULT '' COMMENT '성별',
  `mb_birth` date DEFAULT NULL COMMENT '생일',
  `mb_tel` varchar(20) NOT NULL DEFAULT '' COMMENT '전화번호',
  `mb_hp` varchar(20) NOT NULL DEFAULT '' COMMENT '휴대폰 번호',
  `mb_certify` varchar(20) NOT NULL DEFAULT '' COMMENT '본인인증 수단',
  `mb_adult` tinyint(1) NOT NULL DEFAULT 0 COMMENT '성인 인증 여부 (0: 미성년자, 1: 성인)',
  `mb_dupinfo` varchar(255) NOT NULL DEFAULT '' COMMENT '본인인증 개인식별부호값',
  `mb_zip` varchar(6) NOT NULL DEFAULT '' COMMENT '지번',
  `mb_addr1` varchar(255) NOT NULL DEFAULT '' COMMENT '기본주소',
  `mb_addr2` varchar(255) NOT NULL DEFAULT '' COMMENT '상세주소',
  `mb_addr3` varchar(255) NOT NULL DEFAULT '' COMMENT '기타주소',
  `mb_addr_jibeon` varchar(255) NOT NULL DEFAULT '' COMMENT '지번주소',
  `mb_signature` text DEFAULT NULL COMMENT '회원 서명',
  `mb_recommend` varchar(100) NOT NULL DEFAULT '' COMMENT '추천인',
  `mb_point` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '회원 포인트',
  `mb_last_login_at` datetime DEFAULT NULL COMMENT '마지막 로그인 일시',
  `mb_last_login_ip` varchar(45) NOT NULL DEFAULT '' COMMENT '마지막 로그인 IP',
  `mb_signup_ip` varchar(45) NOT NULL DEFAULT '' COMMENT '회원가입 IP',
  `mb_leave_date` datetime DEFAULT NULL COMMENT '회원 탈퇴일',
  `mb_intercept_date` datetime DEFAULT NULL COMMENT '회원 접근차단일',
  `mb_email_verified_at` datetime DEFAULT NULL COMMENT '메일인증 완료 일시',
  `mb_email_verified_code` varchar(255) NOT NULL DEFAULT '' COMMENT '메일 인증 코드(일회용)',
  `mb_memo` text DEFAULT NULL COMMENT '메모',
  `mb_memo_create_at` datetime DEFAULT NULL COMMENT '메모 등록일시',
  `mb_lost_certify` varchar(255) NOT NULL DEFAULT '' COMMENT '비밀번호 분실 시 인증 코드',
  `mb_mailling` tinyint(1) NOT NULL DEFAULT 0 COMMENT '메일 수신 여부',
  `mb_sms` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'SMS 수신 여부',
  `mb_open` tinyint(1) NOT NULL DEFAULT 0 COMMENT '회원정보 공개 여부',
  `mb_open_date` date DEFAULT NULL COMMENT '회원정보 공개 일시',
  `mb_memo_call` varchar(255) NOT NULL DEFAULT '' COMMENT '회원 쪽지 알림',
  `mb_memo_cnt` int(11) NOT NULL DEFAULT 0 COMMENT '회원 쪽지 수',
  `mb_scrap_cnt` int(11) NOT NULL DEFAULT 0 COMMENT '회원 스크랩 수',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`mb_no`),
  UNIQUE KEY `mb_id` (`mb_id`),
  KEY `mb_last_login_at` (`mb_last_login_at`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `new_member_config`
--
DROP TABLE IF EXISTS `new_member_config`;
CREATE TABLE IF NOT EXISTS `new_member_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '설정 ID',
  `use_address` tinyint(1) NOT NULL DEFAULT 0 COMMENT '주소 입력 사용여부',
  `required_address` tinyint(1) NOT NULL DEFAULT 0 COMMENT '주소 입력 필수여부',
  `use_signature` tinyint(1) NOT NULL DEFAULT 0 COMMENT '서명 입력 사용여부',
  `required_signature` tinyint(1) NOT NULL DEFAULT 0 COMMENT '서명 입력 필수여부',
  `use_telephone` tinyint(1) NOT NULL DEFAULT 0 COMMENT '전화번호 입력 사용여부',
  `required_telephone` tinyint(1) NOT NULL DEFAULT 0 COMMENT '전화번호 입력 필수여부',
  `use_phone` tinyint(1) NOT NULL DEFAULT 0 COMMENT '휴대폰 입력 사용여부',
  `required_phone` tinyint(1) NOT NULL DEFAULT 0 COMMENT '휴대폰 입력 필수여부',
  `signup_level` int(11) NOT NULL DEFAULT 0 COMMENT '회원가입 시, 회원 레벨',
  `signup_point` int(11) NOT NULL DEFAULT 0 COMMENT '회원가입 시, 지급 포인트',
  `use_member_image` tinyint(1) NOT NULL DEFAULT 0 COMMENT '회원 이미지 사용 여부',
  `upload_permission_level` int(11) NOT NULL DEFAULT 0 COMMENT '회원 이미지 업로드 제한 레벨',
  `member_image_size` int(11) NOT NULL DEFAULT 0 COMMENT '회원 이미지 제한 크기',
  `member_image_width` int(11) NOT NULL DEFAULT 0 COMMENT '회원 이미지 너비',
  `member_image_height` int(11) NOT NULL DEFAULT 0 COMMENT '회원 이미지 높이',
  `use_recommend` tinyint(1) NOT NULL DEFAULT 0 COMMENT '추천인 사용 여부',
  `recommend_point` int(11) NOT NULL DEFAULT 0 COMMENT '추천인 지급 포인트',
  `prohibit_word` text DEFAULT NULL COMMENT '금지단어 (엔터로 구분)',
  `prohibit_domain` text DEFAULT NULL COMMENT '금지 도메인 (엔터로 구분)',
  `signup_terms` text DEFAULT NULL COMMENT '가입 약관',
  `privacy_policy` text DEFAULT NULL COMMENT '개인정보 처리방침',
  `retention_period` int(11) NOT NULL DEFAULT 0 COMMENT '회원 탈퇴 후, 개인정보 보존 기간',
  `use_email_certify` tinyint(1) NOT NULL DEFAULT 0 COMMENT '이메일 인증 사용 여부',
  `use_authentication` tinyint(1) NOT NULL DEFAULT 0 COMMENT '본인인증 사용 여부',
  `is_auth_production` tinyint(1) NOT NULL DEFAULT 0 COMMENT '본인인증 실제 적용 여부',
  `authentication_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT '본인인증 필수 여부',
  `auth_service` varchar(255) DEFAULT NULL COMMENT '인증 서비스',
  `cert_service` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'KG이니시스 통합인증 암호화',
  `cert_kg_mid` varchar(255) DEFAULT NULL COMMENT 'KG이니시스 통합인증 MID',
  `cert_kg_cd` varchar(255) DEFAULT NULL COMMENT 'KG이니시스 통합인증 API KEY',
  `cert_kcp_cd` varchar(255) DEFAULT NULL COMMENT 'NHN KCP 사이트코드',
  `cert_limit` int(11) NOT NULL DEFAULT 0 COMMENT '본인인증 제한 횟수(일 단위)',
  `use_point` tinyint(1) NOT NULL DEFAULT 0 COMMENT '포인트 사용 여부',
  `point_term` int(11) NOT NULL DEFAULT 0 COMMENT '포인트 유효 기간(일 단위)',
  `login_point` int(11) NOT NULL DEFAULT 0 COMMENT '로그인 시 지급 포인트',
  `memo_send_point` int(11) NOT NULL DEFAULT 0 COMMENT '쪽지 전송에 필요한 포인트',
  `member_signup_notify` tinyint(1) NOT NULL DEFAULT 0 COMMENT '가입 시 회원에게 알림 여부',
  `member_signup_send_type` varchar(255) DEFAULT NULL COMMENT '가입 시 회원에게 발송유형',
  `member_signup_preset` text DEFAULT NULL COMMENT '가입 시 회원에게 알림 프리셋',
  `member_leave_notify` tinyint(1) NOT NULL DEFAULT 0 COMMENT '탈퇴 시 회원에게 알림 여부',
  `member_leave_send_type` varchar(255) DEFAULT NULL COMMENT '탈퇴 시 회원에게 발송유형',
  `member_leave_preset` text DEFAULT NULL COMMENT '탈퇴 시 회원에게 알림 프리셋',
  `admin_signup_notify` tinyint(1) NOT NULL DEFAULT 0 COMMENT '가입 시 운영진에게 알림 여부',
  `admin_signup_send_type` varchar(255) DEFAULT NULL COMMENT '가입 시 운영진에게 발송유형',
  `admin_signup_preset` text DEFAULT NULL COMMENT '가입 시 운영진에게 알림 프리셋',
  `admin_leave_notify` tinyint(1) NOT NULL DEFAULT 0 COMMENT '탈퇴 시 운영진에게 알림 여부',
  `admin_leave_send_type` varchar(255) DEFAULT NULL COMMENT '탈퇴 시 운영진에게 발송유형',
  `admin_leave_preset` text DEFAULT NULL COMMENT '탈퇴 시 운영진에게 알림 프리셋',
  `superadmin_signup_notify` tinyint(1) NOT NULL DEFAULT 0 COMMENT '가입 시 최고관리자에게 알림 여부',
  `superadmin_signup_send_type` varchar(255) DEFAULT NULL COMMENT '가입 시 최고관리자에게 발송유형',
  `superadmin_signup_preset` text DEFAULT NULL COMMENT '가입 시 최고관리자에게 알림 프리셋',
  `superadmin_leave_notify` tinyint(1) NOT NULL DEFAULT 0 COMMENT '탈퇴 시 최고관리자에게 알림 여부',
  `superadmin_leave_send_type` varchar(255) DEFAULT NULL COMMENT '탈퇴 시 최고관리자에게 발송유형',
  `superadmin_leave_preset` text DEFAULT NULL COMMENT '탈퇴 시 최고관리자에게 알림 프리셋',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `new_menu`
--
DROP TABLE IF EXISTS `new_menu`;
CREATE TABLE IF NOT EXISTS `new_menu` (
  `me_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '메뉴 ID',
  `me_parent_id` bigint(20) unsigned DEFAULT NULL COMMENT '부모 메뉴 ID',
  `me_name` varchar(255) NOT NULL DEFAULT '' COMMENT '메뉴 이름',
  `me_link` varchar(255) NOT NULL DEFAULT '' COMMENT '메뉴 링크',
  `me_target` varchar(50) NOT NULL DEFAULT '_self' COMMENT '링크 타겟 (_self, _blank 등)',
  `me_order` int(11) NOT NULL DEFAULT 0 COMMENT '메뉴 순서',
  `me_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '메뉴 사용 여부 (1: 사용, 0: 미사용)',
  `me_mobile_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '모바일 메뉴 사용 여부 (1: 사용, 0: 미사용)',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`me_id`),
  KEY `me_parent_id` (`me_parent_id`),
  KEY `me_order` (`me_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='사이트 메뉴 정보';


-- --------------------------------------------------------

--
-- Table structure for table `new_notification`
--
DROP TABLE IF EXISTS `new_notification`;
CREATE TABLE IF NOT EXISTS `new_notification` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '알림 ID',
  `type` varchar(100) NOT NULL COMMENT '알림 종류',
  `name` varchar(255) NOT NULL COMMENT '알림 설정 이름',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '활성화 여부',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `new_notification_setting`
--
DROP TABLE IF EXISTS `new_notification_setting`;
CREATE TABLE IF NOT EXISTS `new_notification_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '알림설정 ID',
  `notification_id` int(11) unsigned NOT NULL COMMENT '알림 ID',
  `setting_key` varchar(255) NOT NULL COMMENT '알림 설정 key',
  `setting_name` varchar(255) NOT NULL COMMENT '알림 설정 이름',
  `setting_value` varchar(255) NOT NULL COMMENT '알림 설정 값',
  `setting_description` text DEFAULT NULL COMMENT '설정값에 대한 설명',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `new_popup`
--
DROP TABLE IF EXISTS `new_popup`;
CREATE TABLE IF NOT EXISTS `new_popup` (
  `pu_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pu_division` varchar(10) NOT NULL DEFAULT 'both' COMMENT '팝업 표시 구분',
  `pu_begin_time` datetime DEFAULT NULL COMMENT '팝업 시작 시간',
  `pu_end_time` datetime DEFAULT NULL COMMENT '팝업 종료 시간',
  `pu_disable_hours` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '팝업 비활성화 시간 (시간 단위)',
  `pu_left` int(11) NOT NULL DEFAULT 0 COMMENT '팝업 위치 (left)',
  `pu_top` int(11) NOT NULL DEFAULT 0 COMMENT '팝업 위치 (top)',
  `pu_height` int(11) NOT NULL DEFAULT 0 COMMENT '팝업 높이',
  `pu_width` int(11) NOT NULL DEFAULT 0 COMMENT '팝업 너비',
  `pu_auto_height` tinyint(1) NOT NULL DEFAULT 0 COMMENT '팝업 자동 높이 여부',
  `pu_title` varchar(255) NOT NULL COMMENT '팝업 타이틀',
  `pu_content` text NOT NULL COMMENT '팝업 내용',
  `pu_mobile_content` text NOT NULL COMMENT '팝업 내용 (모바일)',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`pu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='팝업 관리';


-- --------------------------------------------------------

--
-- Table structure for table `new_qa_config`
--
DROP TABLE IF EXISTS `new_qa_config`;
CREATE TABLE IF NOT EXISTS `new_qa_config` (
  `qa_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'QA 설정 ID',
  `qa_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'QA 제목',
  `qa_category` varchar(200) NOT NULL DEFAULT '' COMMENT 'QA 카테고리 (`|`로 구분)',
  `qa_use_email` tinyint(1) NOT NULL DEFAULT 0 COMMENT '이메일 입력 사용여부',
  `qa_req_email` tinyint(1) NOT NULL DEFAULT 0 COMMENT '이메일 입력 필수여부',
  `qa_use_hp` tinyint(1) NOT NULL DEFAULT 0 COMMENT '휴대폰 번호 입력 사용여부',
  `qa_req_hp` tinyint(1) NOT NULL DEFAULT 0 COMMENT '휴대폰 번호 입력 필수여부',
  `qa_use_sms` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'SMS 수신동의 사용여부',
  `qa_send_number` varchar(20) NOT NULL DEFAULT '' COMMENT 'SMS 발신 번호',
  `qa_use_editor` tinyint(1) NOT NULL DEFAULT 0 COMMENT '에디터 사용 여부',
  `qa_image_width` int(11) NOT NULL DEFAULT 0 COMMENT '이미지 최대 너비',
  `qa_upload_size` int(11) NOT NULL DEFAULT 0 COMMENT '파일 업로드 최대 크기 (Byte)',
  `qa_insert_content` text NOT NULL DEFAULT '' COMMENT '기본 내용',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`qa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='QA 설정 관리';


-- --------------------------------------------------------

--
-- Table structure for table `new_social_provider`
--
DROP TABLE IF EXISTS `new_social_provider`;
CREATE TABLE IF NOT EXISTS `new_social_provider` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
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
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `provider_key` varchar(50) NOT NULL COMMENT '참조용 서비스 키 (예: naver, facebook)',
  `config_name` varchar(100) NOT NULL COMMENT '추가 설정 이름 (예: apikey)',
  `config_value` varchar(255) DEFAULT NULL COMMENT '추가 설정 값',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider_key` (`provider_key`,`config_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;