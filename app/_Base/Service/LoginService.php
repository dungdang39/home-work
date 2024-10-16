<?php

namespace App\Base\Service;

use Core\AppConfig;
use Core\Database\Db;
use Exception;

class LoginService
{
    public function __construct() {
    }

    public function login(string $mb_id): void
    {
        $_SESSION['ss_mb_id'] = $mb_id;
        // $this->service->set_member_key_session($member);
    }

    /**
     * FLASH XSS 공격에 대응하기 위하여 회원의 고유키 생성
     */
    public function set_member_key_session(array $member): void
    {
        $_SESSION['ss_mb_key'] = md5($member['created_at'] . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * 패스워드 체크
     * - 그누보드5의 암호화 방식을 사용하는 경우 이전 암호화 방식으로 체크
     * 
     * @param string $password
     * @param string $hashed_password
     * @return bool
     */
    public function checkPassword(string $password, string $hashed_password): bool
    {
        if ($this->isOldPassword($hashed_password)) {
            return $this->checkPasswordWithOldHashing($password, $hashed_password);
        }

        return password_verify($password, $hashed_password);
    }

    /**
     * 이전 암호화 방식인지 체크
     * 
     * @param string $hashed_password
     * @return bool
     */
    public function isOldPassword(string $hashed_password): bool
    {
        return password_needs_rehash($hashed_password, PASSWORD_DEFAULT);
    }

    /**
     * 이전 암호화 방식으로 패스워드 체크
     * @param string $password
     * @param string $hashed_password
     * @return bool
     * @throws Exception
     */
    private function checkPasswordWithOldHashing(string $password, string $hashed_password): bool
    {
        $app_config = AppConfig::getInstance();
        if ($app_config->get('STRING_ENCRYPT_FUNCTION') === 'create_hash') {
            return validate_password($password, $hashed_password);
        }

        try {
            $encrypt_password = $this->mysqlPassword($password);

            return ($encrypt_password === $hashed_password);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Mysql PASSWORD 함수 사용
     * @deprecated  MySQL 8.0.11 버전 이상에서는 PASSWORD 함수가 제거됨으로 사용할 수 없음.
     * @param string $password 비밀번호
     * @return string
     */
    private function mysqlPassword(string $password): string
    {
        $row = Db::getInstance()->run("SELECT PASSWORD('{$password}') as pw")->fetch();
        return $row['pw'];
    }
}
