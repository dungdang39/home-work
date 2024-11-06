<?php

namespace Install;

use Bootstrap\EnvLoader;
use Core\AppConfig;
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
     * 설치 가능 여부 체크
     * @return void
     * @param Environment $templates
     */
    public function validateInstall(Environment $templates): void
    {
        $error_data = [
            "env_file" => "/" . EnvLoader::ENV_FILE,
            "data_dir" => $this->data_dir,
        ];
        if ($this->isInstalled()) {
            echo $templates->render("error/installed.html", $error_data);
            exit;
        }
        if (!$this->isDataDirExists()) {
            echo $templates->render("error/data_directory.html", $error_data);
            exit;
        }
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
     * 이미 그누보드가 설치됬는지 체크
     * - .env 파일 존재여부 확인
     * @return bool
     */
    public function isInstalled(): bool
    {
        return file_exists($this->base_path . "/" . EnvLoader::ENV_FILE);
    }

    /**
     * data 디렉토리 존재 여부 체크
     * @return bool
     */
    public function isDataDirExists(): bool
    {
        return is_dir($this->data_path);
    }

    /**
     * data 디렉토리에 파일 생성 가능 여부 체크
     * @return bool
     */
    public function isDataDirWritable(): bool
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
    public function isDataDirWritableFromCgi(): bool
    {
        if (!is_readable($this->data_path) || !is_executable($this->data_path)) {
            return false;
        }
        return true;
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
     * 라이센스 동의 체크
     * @param string|null $agree 동의 여부
     * @return bool
     */
    public function checkLicenseAgree(?string $agree = ''): bool
    {
        return $agree === '동의함';
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
    function validateInstallInput(?string $str = null)
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

    public function jsonResponse(string $message, string $type = 'error'): string
    {
        return json_encode(['type' => $type, 'message' => $message]);
    }
}
