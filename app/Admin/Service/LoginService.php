<?php

namespace App\Admin\Service;

use App\Member\MemberService;
use Core\AppConfig;
use Core\Database\Db;
use Exception;

class LoginService
{
    private MemberService $member_service;

    public function __construct(
        MemberService $member_service
    ) {
        $this->member_service = $member_service;
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
            return $this->checkOldPassword($password, $hashed_password);
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
    private function checkOldPassword(string $password, string $hashed_password): bool
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
