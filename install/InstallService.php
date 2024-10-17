<?php

namespace Install;

use Bootstrap\EnvLoader;
use Core\AppConfig;
use Core\Database\Db;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * 설치 서비스 클래스
 */
class InstallService
{
    public const TEMPLATE_DIR = '/install/template';

    /**
     * 템플릿 로드
     * @return Environment
     */
    public function loadTemplate(): Environment
    {
        $app_config = AppConfig::getInstance();

        $loader = new FilesystemLoader($app_config->get('BASE_PATH') . self::TEMPLATE_DIR . '/');
        $twig = new Environment($loader);
        $twig->addGlobal('app_name', $app_config->get('APP_NAME'));
        $twig->addGlobal('base_url', $app_config->get('BASE_URL'));
        $twig->addGlobal('version', AppConfig::VERSION);

        return $twig;
    }

    /**
     * 라이센스 파일 내용 반환
     * @param string|null $filename 파일명
     * @return string 라이센스 파일 내용
     */
    public function getLicense(string $filename = null): string
    {
        $app_config = AppConfig::getInstance();
        $filename = $filename ?? $app_config->get('LICENSE_FILE');

        return file_get_contents($app_config->get('BASE_PATH') . '/' . $filename);
    }

    /**
     * 테이블 생성
     * @param Db $db 데이터베이스 객체
     * @param string $prefix 테이블 접두사
     * @param string $file_path 파일 경로
     * @return void
     */
    public function createTable(Db $db, string $prefix, string $file_path): void
    {
        $file = implode('', file($file_path));
        eval("\$file = \"$file\";");

        $file = preg_replace('/^--.*$/m', '', $file);
        $file = preg_replace('/`new_([^`]+`)/', '`' . $prefix . '$1', $file);
        $f = explode(';', $file);
        for ($i = 0; $i < count($f); $i++) {
            if (trim($f[$i]) == '') {
                continue;
            }

            $sql = $this->get_db_create_replace($f[$i]);
            $db->run($sql);
        }
    }

    /**
     * .env 파일 생성
     * @param array $form 폼 데이터
     * @return void
     */
    public function createEnvFile(array $form): void
    {
        $app_config = AppConfig::getInstance();
        $key = getRandomTokenString(16);
        $url = $app_config->get('BASE_URL');

        $env_file = fopen($app_config->get('BASE_PATH') . '/' . EnvLoader::ENV_FILE, 'w');
        $env_content = <<<EOD
        APP_ENV=production
        APP_DEBUG=false
        APP_KEY=base64:{$key}
        APP_URL={$url}

        DB_CONNECTION=mysql
        DB_HOST={$form['mysql_host']}
        DB_PORT=3306
        DB_DATABASE={$form['mysql_db']}
        DB_USERNAME={$form['mysql_user']}
        DB_PASSWORD={$form['mysql_pass']}
        DB_PREFIX={$form['table_prefix']}
        EOD;
        fwrite($env_file, $env_content);
        fclose($env_file);
    }

    /**
     * 데이터 디렉토리에 .htaccess 파일 생성
     * 
     * data 디렉토리 및 하위 디렉토리에서는 아래 파일을 실행할수 없게함.
     * - .htaccess .htpasswd .php .phtml .html .htm .inc .cgi .pl .phar
     * 
     * @return void
     */
    public function createHtaccessToDataDirectory(string $data_path): void
    {
        $f = fopen($data_path . '/.htaccess', 'w');
        $str = <<<EOD
        <FilesMatch "\.(htaccess|htpasswd|[Pp][Hh][Pp]|[Pp][Hh][Tt]|[Pp]?[Hh][Tt][Mm][Ll]?|[Ii][Nn][Cc]|[Cc][Gg][Ii]|[Pp][Ll]|[Pp][Hh][Aa][Rr])">
        Order allow,deny
        Deny from all
        </FilesMatch>
        RedirectMatch 403 /session/.*
        EOD;
        fwrite($f, $str);
        fclose($f);
    }

    /**
     * .htaccess 파일 생성
     * @return void
     */
    public function createHtaccess(): void
    {
        $app_config = AppConfig::getInstance();
        $base_url = $app_config->get('BASE_URL');
        $rewrite_base = '/';
        $parse_url = parse_url($base_url, 5);
        if (strpos($_SERVER['REQUEST_URI'], $parse_url) === 0) {
            $rewrite_base = $parse_url . '/';
        }
        
        $htaccess_file = fopen($app_config->get('BASE_PATH') . '/.htaccess', 'w');
        $htaccess_content = <<<EOD
        RewriteEngine On

        # 설치 위치에 따라 RewriteBase 조정 (루트 설치시 '/' 사용)
        RewriteBase {$rewrite_base}

        # 모든 요청을 index.php로 전달
        RewriteRule ^index\.php$ - [L]
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . {$rewrite_base}index.php [L]
        EOD;
        fwrite($htaccess_file, $htaccess_content);
        fclose($htaccess_file);
    }

    /**
     * 테이블 생성 DDL에서 DB 엔진 및 문자셋 변경
     * @param string $sql_str SQL문
     * @return string 변경된 SQL문
     */
    private function get_db_create_replace(string $sql_str): string
    {
        $app_config = AppConfig::getInstance();
        $db_engine = $app_config->get('DB_ENGINE');
        $db_charset = $app_config->get('DB_CHARSET');

        if (in_array(strtolower($db_engine), array('innodb', 'myisam'))) {
            $sql_str = preg_replace('/ENGINE=InnoDB/', 'ENGINE=' . $db_engine, $sql_str);
        } else {
            $sql_str = preg_replace('/ENGINE=InnoDB/', '', $sql_str);
        }

        if ($db_charset !== 'utf8') {
            $sql_str = preg_replace('/CHARSET=utf8/', 'CHARACTER SET ' . get_db_charset($db_charset), $sql_str);
        }

        return $sql_str;
    }
}
