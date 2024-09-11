<?php

namespace App\Member\Model;

use App\Config\ConfigService;
use App\Member\MemberConfigService;

class CreateMemberRequest extends MemberRequest
{
    // 기본 정보
    public string $mb_id;

    private array $member_config;
    private array $config;

    public function __construct(
        MemberConfigService $member_config_service,
        ConfigService $config_service
    ) {
        $this->member_config = $member_config_service->getMemberConfig();
        $this->config = $config_service->getConfig();
    }
        /*
        $mb_id          = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
        $mb_password    = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';
        $mb_certify_case = isset($_POST['mb_certify_case']) ? preg_replace('/[^0-9a-z_]/i', '', $_POST['mb_certify_case']) : '';
        $mb_certify     = isset($_POST['mb_certify']) ? preg_replace('/[^0-9a-z_]/i', '', $_POST['mb_certify']) : '';
        $mb_zip         = isset($_POST['mb_zip']) ? preg_replace('/[^0-9a-z_]/i', '', $_POST['mb_zip']) : '';

        // 휴대폰번호 체크
        $mb_hp = hyphen_hp_number($_POST['mb_hp']);
        if ($mb_hp) {
            $result = exist_mb_hp($mb_hp, $mb_id);
            if ($result) {
                alert($result);
            }
        }

        // 인증정보처리
        if ($mb_certify_case && $mb_certify) {
            $mb_certify = isset($_POST['mb_certify_case']) ? preg_replace('/[^0-9a-z_]/i', '', (string)$_POST['mb_certify_case']) : '';
            $mb_adult = isset($_POST['mb_adult']) ? preg_replace('/[^0-9a-z_]/i', '', (string)$_POST['mb_adult']) : '';
        } else {
            $mb_certify = '';
            $mb_adult = 0;
        }

        $mb_zip1 = substr($mb_zip, 0, 3);
        $mb_zip2 = substr($mb_zip, 3);

        $mb_email = isset($_POST['mb_email']) ? get_email_address(trim($_POST['mb_email'])) : '';
        $mb_nick = isset($_POST['mb_nick']) ? trim(strip_tags($_POST['mb_nick'])) : '';

        if ($msg = valid_mb_nick($mb_nick)) {
            alert($msg, "", true, true);
        }

        $mb_memo = isset($_POST['mb_memo']) ? $_POST['mb_memo'] : '';


        if ($mb_password) {
            $sql_password = " , mb_password = '" . get_encrypt_string($mb_password) . "' ";
        } else {
            $sql_password = "";
        }

        if (isset($passive_certify) && $passive_certify) {
            $sql_certify = " , mb_email_certify = '" . G5_TIME_YMDHIS . "' ";
        } else {
            $sql_certify = "";
        }

        // 이미지 검사
        */

    public function load(array $data, array $member = []): CreateMemberRequest
    {
        $this->mapDataToProperties($this, $data);

        $this->validateId();
        $this->validatePassword();
        $this->validateName();
        $this->validateNickName();
        $this->validateEmail();
        if (
            $this->member_config['required_phone']
            && ($this->member_config['use_phone'] || $this->member_config['auth_service'])
        ) {
            $this->validateHp();
        }

        $this->mb_id = strtolower($this->mb_id);
        $this->processPassword();
        $this->mb_email = get_email_address($this->mb_email);
        $this->mb_nick_date = date('Y-m-d');
        $this->mb_hp = hyphen_hp_number($this->mb_hp);
        $this->mb_signup_ip = $_SERVER['REMOTE_ADDR'];
        $this->mb_level = $this->config['cf_register_level'] ?? 1;
        if (!$this->member_config['use_email_certify']) {
            $this->mb_email_verified_at = date('Y-m-d H:i:s');
        }

        return $this;
    }

    protected function validateId()
    {
        if (empty(trim($this->mb_id))) {
            $this->throwException("아이디를 입력해주세요.");
        }
        if (!is_valid_mb_id($this->mb_id)) {
            $this->throwException("회원아이디는 영문자, 숫자, _ 만 입력하세요.");
        }
        $min_length = 3;
        if (!has_min_length($this->mb_id, $min_length)) {
            $this->throwException("회원아이디는 최소 {$min_length}글자 이상 입력하세요.");
        }
        // if (is_prohibited_word($this->mb_id, $this->member_config)) {
        //     $this->throwException("이미 예약된 단어로 사용할 수 없는 아이디 입니다.");
        // }
    }

    protected function validatePassword()
    {
        if (empty(trim($this->mb_password))) {
            $this->throwException("비밀번호를 입력해주세요.");
        }
    }

    protected function processPassword()
    {
        $this->mb_password = password_hash($this->mb_password, PASSWORD_DEFAULT);
    }
}
