<?php

namespace Install;

use League\Plates\Engine;

/**
 * 설치전 검사 서비스 클래스
 */
class InstallValidateService
{
    public const ENV_FILE = '.env';
    public const LICENSE_FILE = 'LICENSE.txt';
    public const TEMPLATE_DIR = './template';

    private $templates;
    private string $data_dir;
    private string $data_path;
    private string $version;

    public function __construct(Engine $templates)
    {
        $this->templates = $templates;
        $this->data_dir = G5_DATA_DIR;
        $this->data_path = G5_DATA_PATH;
        $this->version = G5_VERSION;
    }

    /**
     * 설치 가능 여부 체크
     * @return string Error Type(파일 이름)
     */
    public function validateInstall(): string
    {
        $error_data = [
            "version" => $this->version,
            "env_file" => "/" . self::ENV_FILE,
            "data_dir" => $this->data_dir,
        ];
        if ($this->isInstalled()) {
            return $this->templates->render("error/installed", $error_data);
        }
        if (!$this->isDataDirExists()) {
            return $this->templates->render("error/data_directory", $error_data);
        }
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $sapi_type = php_sapi_name();
            if (substr($sapi_type, 0, 3) == 'cgi') {
                if (!$this->isDataDirWritableFromCgi()) {
                    return $this->templates->render("error/permission_705", $error_data);
                }
            } else {
                if (!$this->isDataDirWritable()) {
                    return $this->templates->render("error/permission_707", $error_data);
                }
            }
        }

        return "";
    }

    /**
     * 이미 그누보드가 설치됬는지 체크
     * - .env 파일 존재여부 확인
     * @return bool
     */
    public function isInstalled(): bool
    {
        return file_exists(G5_PATH . '/' . self::ENV_FILE);
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
     * @param string $agree 동의 여부
     * @return bool
     */
    public function checkLicenseAgree(?string $agree = ""): bool
    {
        return (!isset($agree) || $agree != '동의함');
    }

    /**
     * AJAX 확인용 토큰 생성
     */
    public function makeAjaxToken(): string
    {
        $tmp_str = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
        return md5($tmp_str . $_SERVER['REMOTE_ADDR'] . dirname(dirname(__FILE__) . '/'));
    }
}