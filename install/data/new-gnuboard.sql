-- --------------------------------------------------------

--
-- Table structure for table `new_admin_menu`
--
DROP TABLE IF EXISTS `new_admin_menu`;
CREATE TABLE IF NOT EXISTS `new_admin_menu` (
  `am_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `am_parent_id` int(11) unsigned DEFAULT NULL COMMENT '부모 ID',
  `am_name` varchar(255) NOT NULL COMMENT '관리자 메뉴 이름',
  `am_route` varchar(255) DEFAULT NULL COMMENT '라우터 이름',
  `am_order` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '정렬순서',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`am_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='관리자 메뉴';


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='관리자 메뉴 권한';


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='배너 정보';


-- --------------------------------------------------------

--
-- Table structure for table `new_config`
--
DROP TABLE IF EXISTS `new_config`;
CREATE TABLE IF NOT EXISTS `new_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `scope` varchar(255) NOT NULL DEFAULT 'other' COMMENT '환경설정 포함 범위',
  `name` varchar(255) NOT NULL COMMENT '환경설정 이름',
  `value` text NOT NULL DEFAULT '' COMMENT '환경설정 값',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`scope`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='사이트 환경설정';


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='컨텐츠 정보';


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='FAQ 정보';


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='FAQ 카테고리';


-- --------------------------------------------------------

--
-- Table structure for table `new_mainpage`
--
DROP TABLE IF EXISTS `new_mainpage`;
CREATE TABLE IF NOT EXISTS `new_mainpage` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `section` varchar(255) NOT NULL DEFAULT '' COMMENT '섹션 타입',
  `section_title` varchar(255) NOT NULL DEFAULT '' COMMENT '섹션 타이틀',
  `skin` varchar(100) DEFAULT NULL COMMENT '섹션별 스킨',
  `display_count` int(11) NOT NULL DEFAULT 0 COMMENT '표시할 항목 수(컬럼 수)',
  `is_display_title` tinyint(1) NOT NULL DEFAULT 0 COMMENT '타이틀 표시 여부',
  `is_swipe` tinyint(1) NOT NULL DEFAULT 0 COMMENT '배너 자동 넘김(배너 전용)',
  `max_item` int(11) NOT NULL DEFAULT 0 COMMENT '최대 상품 수 (쇼핑카테고리, 기획전 전용)',
  `row_item` int(11) NOT NULL DEFAULT 0 COMMENT '한줄 최소 상품 수 (쇼핑카테고리, 기획전 전용)',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '사용 여부(기획전 전용)',
  `display_boards` varchar(255) DEFAULT NULL COMMENT '출력할 게시판(커뮤니티 최신글 전용)',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='메인페이지 설정';


-- --------------------------------------------------------

--
-- Table structure for table `new_member`
--
DROP TABLE IF EXISTS `new_member`;
CREATE TABLE IF NOT EXISTS `new_member` (
  `mb_no` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mb_id` varchar(60) NOT NULL COMMENT '아이디',
  `mb_id_hash` varchar(64) NOT NULL DEFAULT '' COMMENT '아이디 해시 (sha256)',
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
  `mb_memo_created_at` datetime DEFAULT NULL COMMENT '메모 등록일시',
  `mb_lost_certify` varchar(255) NOT NULL DEFAULT '' COMMENT '비밀번호 분실 시 인증 코드',
  `mb_is_marketing_receive` tinyint(1) NOT NULL DEFAULT 0 COMMENT '광고성정보 수신동의',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='회원 정보';


-- gnuboard7.new_member_social_profiles definition

CREATE TABLE `new_member_social_profiles` (
  `mp_no` int(11) NOT NULL AUTO_INCREMENT,
  `mb_id` varchar(255) NOT NULL DEFAULT '' COMMENT '회원 아이디',
  `provider` varchar(50) NOT NULL DEFAULT '' COMMENT '소셜 제공자',
  `object_sha` varchar(45) NOT NULL DEFAULT '' COMMENT '소셜 객체 식별자',
  `identifier` varchar(255) NOT NULL DEFAULT '' COMMENT '소셜 식별자',
  `profileurl` varchar(255) NOT NULL DEFAULT '' COMMENT '프로필 URL',
  `photourl` varchar(255) NOT NULL DEFAULT '' COMMENT '프로필 이미지 URL',
  `displayname` varchar(150) NOT NULL DEFAULT '' COMMENT '표시 이름',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '설명',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '등록일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  UNIQUE KEY `mp_no` (`mp_no`),
  KEY `mb_id` (`mb_id`),
  KEY `provider` (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='회원 소셜 프로필 정보';


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='메뉴 정보';


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='알림 설정';


-- --------------------------------------------------------

--
-- Table structure for table `new_notification_setting`
--
DROP TABLE IF EXISTS `new_notification_setting`;
CREATE TABLE IF NOT EXISTS `new_notification_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '알림설정 ID',
  `notification_id` int(11) unsigned NOT NULL COMMENT '알림 ID',
  `setting_key` varchar(255) NOT NULL COMMENT '설정 key',
  `setting_name` varchar(255) NOT NULL COMMENT '설정 이름',
  `setting_value` varchar(255) NOT NULL COMMENT '설정 값',
  `setting_options` text DEFAULT NULL COMMENT '설정 값 옵션',
  `setting_description` text DEFAULT NULL COMMENT '설정 값에 대한 설명',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='알림 설정에 대한 값';


-- --------------------------------------------------------

--
-- Table structure for table `new_point`
--

DROP TABLE IF EXISTS `new_point`;
CREATE TABLE IF NOT EXISTS `new_point` (
  `po_id` int(11) NOT NULL AUTO_INCREMENT,
  `mb_id` varchar(60) NOT NULL DEFAULT '' COMMENT '회원 아이디',
  `po_content` varchar(255) NOT NULL DEFAULT '' COMMENT '포인트 내용',
  `po_point` int(11) NOT NULL DEFAULT 0 COMMENT '포인트',
  `po_use_point` int(11) NOT NULL DEFAULT 0 COMMENT '사용된 포인트',
  `po_expired` tinyint(1) NOT NULL DEFAULT 0 COMMENT '포인트 만료 여부',
  `po_expire_date` date DEFAULT NULL COMMENT '포인트 만료날짜',
  `po_mb_point` int(11) NOT NULL DEFAULT 0 COMMENT '회원의 포인트 합계',
  `po_rel_table` varchar(20) NOT NULL DEFAULT '' COMMENT '연관 table',
  `po_rel_id` varchar(60) NOT NULL DEFAULT '' COMMENT '연관 id',
  `po_rel_action` varchar(100) NOT NULL DEFAULT '' COMMENT '연관 action',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
  PRIMARY KEY (`po_id`),
  KEY `index1` (`mb_id`,`po_rel_table`,`po_rel_id`,`po_rel_action`),
  KEY `index2` (`po_expire_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='포인트 정보';


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='팝업 정보';


-- --------------------------------------------------------

--
-- Table structure for table `new_question_category`
--
DROP TABLE IF EXISTS `new_question_category`;
CREATE TABLE IF NOT EXISTS `new_question_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'QA 카테고리 ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '카테고리 타이틀',
  `template` text DEFAULT '' COMMENT '서식',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '카테고리 활성화 여부',
  `display_order`int(11) NOT NULL DEFAULT 0 COMMENT '정렬순서',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Q&A 카테고리 관리';


-- --------------------------------------------------------

--
-- Table structure for table `new_question`
--

DROP TABLE IF EXISTS `new_question`;
CREATE TABLE IF NOT EXISTS `new_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL COMMENT '카테고리 ID',
  `mb_id` varchar(60) DEFAULT NULL COMMENT '회원 아이디',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '작성자 이름',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT '작성자 이메일',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '1:1문의 상태(pending, in_progress, completed)',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '질문 제목',
  `content` text NOT NULL COMMENT '질문 내용',
  `ip` varchar(45) NOT NULL DEFAULT '' COMMENT '질문 등록 시 IP',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Q&A 질문 관리';


-- --------------------------------------------------------

--
-- Table structure for table `new_question_answer`
--

DROP TABLE IF EXISTS `new_question_answer`;
CREATE TABLE IF NOT EXISTS `new_question_answer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL COMMENT '질문 ID',
  `admin_id` varchar(60) DEFAULT NULL COMMENT '답변 작성자 회원 아이디',
  `content` text NOT NULL COMMENT '답변 내용',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Q&A 답변 관리';


-- --------------------------------------------------------

--
-- Table structure for table `new_social_provider`
--
DROP TABLE IF EXISTS `new_social_provider`;
CREATE TABLE IF NOT EXISTS `new_social_provider` (
  `provider` varchar(50) NOT NULL COMMENT '서비스 공급자 (예: naver, facebook)',
  `provider_name` varchar(100) NOT NULL COMMENT '서비스 이름 (예: 네이버, 페이스북)',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '소셜 로그인 활성화 여부',
  `is_test` tinyint(1) NOT NULL DEFAULT 0 COMMENT '테스트/연동 중 여부',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='소셜 로그인';

-- --------------------------------------------------------

--
-- Table structure for table `new_social_provider_key`
--
DROP TABLE IF EXISTS `new_social_provider_key`;
CREATE TABLE IF NOT EXISTS `new_social_provider_key` (
  `provider` varchar(50) NOT NULL COMMENT '서비스 공급자 (예: naver, facebook)',
  `name` varchar(100) NOT NULL COMMENT '키 이름 (예: client_id)',
  `value` varchar(255) DEFAULT NULL COMMENT '키 값',
  `created_at` datetime DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일',
  PRIMARY KEY (`provider`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='소셜 로그인 별 키 정보';