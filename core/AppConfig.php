<?php

namespace Core;

class AppConfig
{
    public const APP_NAME = 'NEW-그누보드';
    public const VERSION = '1.0.0';

    public const DOMAIN = '';
    public const HTTPS_DOMAIN = '';

    public const DEBUG = false;
    public const DB_ENGINE = 'InnoDB';
    public const DB_CHARSET = 'utf8';

    public const COOKIE_DOMAIN = '';

    public const ADMIN_DIR = 'adm';
    public const BBS_DIR = 'bbs';
    public const CONTENT_DIR = 'content';
    public const CSS_DIR = 'css';
    public const DATA_DIR = 'data';
    public const EXTEND_DIR = 'extend';
    public const GROUP_DIR = 'group';
    public const IMG_DIR = 'img';
    public const JS_DIR = 'js';
    public const LIB_DIR = 'lib';
    public const PLUGIN_DIR = 'plugin';
    public const SKIN_DIR = 'skin';
    public const EDITOR_DIR = 'editor';
    public const MOBILE_DIR = 'mobile';
    public const OKNAME_DIR = 'okname';

    public const KCPCERT_DIR = 'kcpcert';
    public const INICERT_DIR = 'inicert';
    public const LGXPAY_DIR = 'lgxpay';

    public const SNS_DIR = 'sns';
    public const SYNDI_DIR = 'syndi';
    public const PHPMAILER_DIR = 'PHPMailer';
    public const SESSION_DIR = 'session';
    public const THEME_DIR = 'theme';

    public const SET_DEVICE = 'both';
    public const USE_MOBILE = true;
    public const USE_CACHE = true;

    /********************
        입력값 검사 상수
    ********************/
    public const ALPHAUPPER = 1;    // 영대문자
    public const ALPHALOWER = 2;    // 영소문자
    public const ALPHABETIC = 4;    // 영대,소문자
    public const NUMERIC = 8;       // 숫자
    public const HANGUL = 16;       // 한글
    public const SPACE = 32;        // 공백
    public const SPECIAL = 64;      // 특수문자

    /********************
        SEO TITLE 문단 길이
    ********************/
    public const SEO_TITLE_WORD_CUT = 8;  // SEO TITLE 문단 길이

    /********************
        퍼미션
    ********************/
    public const DIR_PERMISSION = 0755; // 디렉토리 생성시 퍼미션
    public const FILE_PERMISSION = 0644; // 파일 생성시 퍼미션

    /********************
        모바일 인지 결정
    ********************/
    public const MOBILE_AGENT = 'phone|samsung|lgtel|mobile|[^A]skt|nokia|blackberry|BB10|android|sony';

    /********************
        SMTP 설정
    ********************/
    public const SMTP = '127.0.0.1';
    public const SMTP_PORT = '25';

    /********************
        기타 상수
    ********************/

    // 암호화 함수 지정
    // 사이트 운영 중 설정을 변경하면 로그인이 안되는 등의 문제가 발생합니다.
    public const STRING_ENCRYPT_FUNCTION = 'create_hash';
    public const MYSQL_PASSWORD_LENGTH = 41;  // mysql password length 41, old_password 의 경우에는 16

    // SQL 에러를 표시할 것인지 지정
    public const DISPLAY_SQL_ERROR = false;

    // escape string 처리 함수 지정
    public const ESCAPE_FUNCTION = 'sql_escape_string';

    // 게시판에서 링크의 기본 개수
    public const LINK_COUNT = 2;

    // 썸네일 설정
    public const THUMB_JPG_QUALITY = 90;
    public const THUMB_PNG_COMPRESS = 5;

    // 모바일 기기에서 DHTML 에디터 사용여부
    public const IS_MOBILE_DHTML_USE = false;

    // MySQLi 사용여부
    public const MYSQLI_USE = true;

    // Browscap 사용여부
    public const BROWSCAP_USE = true;

    // 접속자 기록 때 Browscap 사용여부
    public const VISIT_BROWSCAP_USE = false;

    // IP 숨김 방법 설정
    public const IP_DISPLAY = '\\1.♡.\\3.\\4';

    // KAKAO 우편번호 서비스 CDN
    public const POSTCODE_JS = '<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js" async></script>';
}
