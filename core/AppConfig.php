<?php

namespace Core;

class AppConfig
{
    private static ?AppConfig $instance = null;
    public const VERSION = '1.0.0';

    private array $config = [
        'APP_NAME' => 'NEW-그누보드',
        'LICENSE_FILE' => 'LICENSE.txt',

        'DOMAIN' => '',
        'HTTPS_DOMAIN' => '',
        'BASE_URL' => '',
        'BASE_PATH' => '',

        'APP_DEBUG' => false,
        'DB_ENGINE' => 'InnoDB',
        'DB_CHARSET' => 'utf8',

        'COOKIE_DOMAIN' => '',

        'ADMIN_DIR' => 'adm',
        'BBS_DIR' => 'bbs',
        'CONTENT_DIR' => 'content',
        'CSS_DIR' => 'css',
        'DATA_DIR' => 'data',
        'EXTEND_DIR' => 'extend',
        'GROUP_DIR' => 'group',
        'IMG_DIR' => 'img',
        'JS_DIR' => 'js',
        'LIB_DIR' => 'lib',
        'PLUGIN_DIR' => 'plugin',
        'SKIN_DIR' => 'skin',
        'EDITOR_DIR' => 'editor',
        'MOBILE_DIR' => 'mobile',
        'OKNAME_DIR' => 'okname',

        'KCPCERT_DIR' => 'kcpcert',
        'INICERT_DIR' => 'inicert',
        'LGXPAY_DIR' => 'lgxpay',

        'SNS_DIR' => 'sns',
        'SYNDI_DIR' => 'syndi',
        'PHPMAILER_DIR' => 'PHPMailer',
        'SESSION_DIR' => 'session',
        'THEME_DIR' => 'theme',

        'SET_DEVICE' => 'both',
        'USE_MOBILE' => true,
        'USE_CACHE' => true,

        // 입력값 검사 상수
        'ALPHAUPPER' => 1,    // 영대문자
        'ALPHALOWER' => 2,    // 영소문자
        'ALPHABETIC' => 4,    // 영대,소문자
        'NUMERIC' => 8,       // 숫자
        'HANGUL' => 16,       // 한글
        'SPACE' => 32,        // 공백
        'SPECIAL' => 64,      // 특수문자

        // SEO TITLE 문단 길이
        'SEO_TITLE_WORD_CUT' => 8,  // SEO TITLE 문단 길이

        // 퍼미션
        'DIR_PERMISSION' => 0755, // 디렉토리 생성시 퍼미션
        'FILE_PERMISSION' => 0644, // 파일 생성시 퍼미션

        // 모바일 인지 결정
        'MOBILE_AGENT' => 'phone|samsung|lgtel|mobile|[^A]skt|nokia|blackberry|BB10|android|sony',

        // SMTP 설정
        'SMTP' => '127.0.0.1',
        'SMTP_PORT' => '25',

        // 암호화 함수 지정
        'STRING_ENCRYPT_FUNCTION' => 'create_hash',
        'MYSQL_PASSWORD_LENGTH' => 41,  // mysql password length 41, old_password 의 경우에는 16

        // SQL 에러를 표시할 것인지 지정
        'DISPLAY_SQL_ERROR' => false,

        // escape string 처리 함수 지정
        'ESCAPE_FUNCTION' => 'sql_escape_string',

        // 게시판에서 링크의 기본 개수
        'LINK_COUNT' => 2,

        // 썸네일 설정
        'THUMB_JPG_QUALITY' => 90,
        'THUMB_PNG_COMPRESS' => 5,

        // 모바일 기기에서 DHTML 에디터 사용여부
        'IS_MOBILE_DHTML_USE' => false,

        // MySQLi 사용여부
        'MYSQLI_USE' => true,

        // Browscap 사용여부
        'BROWSCAP_USE' => true,

        // 접속자 기록 때 Browscap 사용여부
        'VISIT_BROWSCAP_USE' => false,

        // IP 숨김 방법 설정
        'IP_DISPLAY' => '\\1.♡.\\3.\\4',

        // KAKAO 우편번호 서비스 CDN
        'POSTCODE_JS' => '<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js" async></script>',
    ];

    private function __construct(array $customConfig = [])
    {
        $this->config['BASE_PATH'] = dirname(__DIR__);
        $this->config['BASE_URL'] = $_ENV['APP_URL'] ?? $this->base_url();
        $this->loadFromEnv();

        $this->config = array_merge($this->config, $customConfig);
    }

    public static function getInstance(array $customConfig = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self($customConfig);
        }
        return self::$instance;
    }

    public function get(string $key)
    {
        return $this->config[$key] ?? null;
    }

    public function set(string $key, $value): void
    {
        $this->config[$key] = $value;
    }

    public function loadFromEnv(): void
    {
        foreach ($_ENV as $key => $value) {
            if (array_key_exists($key, $this->config)) {
                if ($value === 'false' or $value === 'true') {
                    $this->config[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                } else {
                    $this->config[$key] = $value;
                }
            }
        }
    }

    /**
    * 기본 URL 반환 함수
    * @return string
    */
    private function base_url()
    {
        $chroot = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], __DIR__));
        //root 경로 슬래시 변경
        $path = str_replace('\\', '/', $chroot . dirname(__DIR__));

        // 윈도우 , 리눅스 경로 호환 슬래시로 변경 , // -> / 로 변경
        $server_script_name = preg_replace('/\/+/', '/', str_replace('\\', '/', $_SERVER['SCRIPT_NAME']));
        $server_script_filename = preg_replace('/\/+/', '/', str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']));

        // ~ 제거 - 리눅스에서 유저에 ~가 들어가는 경우
        $tilde_remove = preg_replace('/^\/~[^\/]+(.*)$/', '$1', $server_script_name);
        $document_root = str_replace($tilde_remove, '', $server_script_filename);
        $pattern = '/.*?' . preg_quote($document_root, '/') . '/i';
        $url_root = preg_replace($pattern, '', $path);
    
        $http = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    
        //host 경로 정규화
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
        if (isset($_SERVER['HTTP_HOST']) && strpos($host, ':') !== false) {
            $host = preg_replace('/:[0-9]+$/', '', $host);
        }
        $host = preg_replace('/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/', '', $host);
    
        // 웹서버의 사용자 경로
        $user = str_replace(preg_replace($pattern, '', $server_script_filename), '', $server_script_name);
    
        $server_port = $_SERVER['SERVER_PORT'];
        $port = ($server_port == 80 || $server_port == 443) ? '' : ':' . $server_port;
    
        return "{$http}{$host}{$port}{$user}{$url_root}";
    }
}
