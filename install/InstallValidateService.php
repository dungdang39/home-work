<?php

namespace Install;

use App\Base\Service\ConfigService;
use Bootstrap\EnvLoader;
use Core\AppConfig;
use Core\Database\Db;
use Core\Database\PDO\Exception\DbConnectException;
use Twig\Environment;

/**
 * 설치전 검사 서비스 클래스
 */
class InstallValidateService
{
    private string $base_path;
    private string $data_dir;
    private string $data_path;

    public function __construct()
    {
        $this->base_path = AppConfig::getInstance()->get('BASE_PATH');
        $this->data_dir = AppConfig::getInstance()->get('DATA_DIR');
        $this->data_path = $this->base_path . "/" . $this->data_dir;
    }

    /**
     * 설치 여부 체크
     * @return void
     */
    public static function checkInstall()
    {
        $base_url = AppConfig::getInstance()->get('BASE_URL');
        if (!self::isExistsEnv()) {
            header('Location: ' . $base_url . '/install');
            exit;
        }
        try {
        if (!self::isExistsConfigTable()) {
            header('Location: ' . $base_url . '/install/step2_form.php');
            exit;
        }
        } catch (DbConnectException $e) {
            echo self::renderDbConnectError();
            exit;
        }
    }

    /**
     * 설치 Step1 단계에서 설치 여부 체크
     * @return void
     * @param Environment $templates
     */
    public function checkInstallByStep1(Environment $templates): void
    {
        $error_data = [
            "env_file" => '/' . EnvLoader::ENV_FILE,
            "data_dir" => $this->data_dir,
        ];
        // 이미 설치된 경우
        if ($this->isExistsEnv()) {
            try {
                if ($this->isExistsConfigTable()) {
                    echo $templates->render("error/installed.html", $error_data);
                    exit;
                } else {
                    header('Location: ' . AppConfig::getInstance()->get('BASE_URL') . '/install/step2_form.php');
                    exit;
                }
            } catch (DbConnectException $e) {
                echo self::renderDbConnectError($e->getMessage());
                exit;
            }
        }
        // .env.example 파일이 존재하지 않을 경우
        if (!file_exists($this->base_path . '/' . InstallService::ENV_EXAMPLE_FILE)) {
            echo $templates->render("error/env_example.html", $error_data);
            exit;
        }
        // 기본 디렉토리에 쓰기 권한이 없을 경우
        if (!is_writable($this->base_path)) {
            echo $templates->render("error/root_directory.html", $error_data);
            exit;
        }
        $this->checkCommonInstallConditions($templates, $error_data);
    }

    /**
     * 설치 Step2 단계에서 설치 여부 체크
     * @return void
     * @param Environment $templates
     */
    public function checkInstallByStep2(Environment $templates): void
    {
        // 이미 설치된 경우
        if (!$this->isExistsEnv()) {
            header('Location: ' . AppConfig::getInstance()->get('BASE_URL'));
            exit;
        }

        $error_data = [
            "env_file" => '/' . EnvLoader::ENV_FILE,
            "data_dir" => $this->data_dir,
        ];
        try {
            if ($this->isExistsConfigTable()) {
                echo $templates->render("error/installed.html", $error_data);
                exit;
            }
        } catch (DbConnectException $e) {
            echo self::renderDbConnectError($e->getMessage());
            exit;
        }
        $this->checkCommonInstallConditions($templates, $error_data);
    }

    /**
     * .env 파일 존재 여부 확인
     * @return bool
     */
    public static function isExistsEnv()
    {
        $env = EnvLoader::ENV_FILE;
        $env_path = str_replace('\\', '/', dirname(__DIR__)) . '/' . $env;

        return file_exists($env_path);
    }

    /**
     * 설정 테이블 존재 여부 확인
     * @return bool
     */
    public static function isExistsConfigTable()
    {
        // 환경설정 로드
        EnvLoader::load();

        $table_name = $_ENV['DB_PREFIX'] . ConfigService::TABLE_NAME;
        return Db::getInstance()->isTableExists($table_name);
    }

    /**
     * GD 라이브러리 존재 여부 체크
     * @return bool
     */
    public function isGdLibraryExists(): bool
    {
        return extension_loaded('gd') && function_exists('gd_info');
    }

    /**
     * AJAX 확인용 토큰 생성
     */
    public function createAjaxToken(): string
    {
        $tmp_str = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
        return md5($tmp_str . $_SERVER['REMOTE_ADDR'] . dirname(dirname(__FILE__) . '/'));
    }

    /**
     * 입력 문자열에 안전하지 않은 문자가 포함되어 있는지 확인
     * @param string|array $str 검사할 문자열 또는 배열
     * @param bool $is_json JSON 형식으로 메시지를 반환할지 여부
     * @return string|array
     */
    public function validateInstallInput(?string $str = null)
    {
        if (!isset($str)) {
            return '';
        }

        $unsafe_patterns = [
            '#\);(passthru|eval|pcntl_exec|exec|system|popen|fopen|fsockopen|file|file_get_contents|readfile|unlink|include|include_once|require|require_once)\s?#i',
            '#\$_(get|post|request)\s?\[.*?\]\s?\)#i'
        ];

        foreach ($unsafe_patterns as $pattern) {
            if (preg_match($pattern, $str)) {
                die($this->jsonResponse('입력한 값에 안전하지 않은 문자가 포함되어 있습니다. 설치를 중단합니다.'));
            }
        }

        // 안전한 경우, stripslashes를 다차원 배열에도 적용
        return array_map_deep('stripslashes', $str);
    }

    /**
     * JSON 형식으로 메시지 반환
     * @param string $message 메시지
     * @param string $type 메시지 타입
     * @return string
     */
    public function jsonResponse(string $message, string $type = 'error'): string
    {
        return json_encode(['type' => $type, 'message' => $message]);
    }

    /**
     * 공통 설치 조건 체크
     * @param Environment $templates
     * @param array $error_data
     * @return void
     */
    private function checkCommonInstallConditions(Environment $templates, array $error_data): void
    {
        // data 디렉토리가 존재하지 않을 경우
        if (!is_dir($this->data_path)) {
            echo $templates->render("error/data_directory.html", $error_data);
            exit;
        }
        // data 디렉토리에 쓰기 권한이 없을 경우
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $sapi_type = php_sapi_name();
            if (substr($sapi_type, 0, 3) == 'cgi') {
                if (!$this->isDataDirWritableFromCgi()) {
                    echo $templates->render("error/permission_705.html", $error_data);
                    exit;
                }
            } else {
                if (!$this->isDataDirWritable()) {
                    echo $templates->render("error/permission_707.html", $error_data);
                    exit;
                }
            }
        }
    }

    /**
     * data 디렉토리에 파일 생성 가능 여부 체크
     * @return bool
     */
    private function isDataDirWritable(): bool
    {
        if (
            !is_readable($this->data_path)
            || !is_writeable($this->data_path)
            || !is_executable($this->data_path)
        ) {
            return false;
        }
        return true;
    }

    /**
     * data 디렉토리에 파일 생성 가능 여부 체크 (CGI)
     * @return bool
     */
    private function isDataDirWritableFromCgi(): bool
    {
        if (
            !is_readable($this->data_path)
            || !is_executable($this->data_path)
        ) {
            return false;
        }
        return true;
    }

    /**
     * 데이터베이스 연결 오류 페이지 출력
     * @param string|null $message 오류 메시지
     * @return string
     */
    private static function renderDbConnectError(?string $message = ''): string
    {
        $install_service = new InstallService();
        $template = $install_service->loadTemplate();
        return $template->render('error/db_connect.html', ['message' => $message]);
    }
}
