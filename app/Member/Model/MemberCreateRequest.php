<?php

namespace App\Member\Model;

use App\Config\ConfigService;
use App\Member\MemberConfigService;
use Core\Traits\SchemaHelperTrait;
use Slim\Psr7\UploadedFile;

class MemberCreateRequest
{
    use SchemaHelperTrait;

    // 기본 정보
    public string $mb_id;
    public string $mb_password;
    public int $mb_level;
    public ?string $mb_img;  // 이미지 파일 경로

    // 개인정보
    public string $mb_name;
    public string $mb_nick;
    public string $mb_email;
    public ?string $mb_nick_date;
    public ?string $mb_homepage;
    public ?string $mb_hp = '';
    public ?string $mb_tel;
    public ?string $mb_zip;
    public ?string $mb_addr1;
    public ?string $mb_addr2;
    public ?string $mb_addr3;
    public ?string $mb_signature;
    public ?string $mb_certify;
    
    // 관리 정보
    public ?string $mb_memo;
    public bool $mb_mailling;
    public bool $mb_sms;
    public bool $mb_open;
    public ?string $mb_leave_date;
    public ?string $mb_intercept_date;
    public string $mb_email_verified_at;
    public string $mb_signup_ip;

    // 여분 필드
    public ?string $mb_1;
    public ?string $mb_2;
    public ?string $mb_3;
    public ?string $mb_4;
    public ?string $mb_5;
    public ?string $mb_6;
    public ?string $mb_7;
    public ?string $mb_8;
    public ?string $mb_9;
    public ?string $mb_10;

    // 회원 이미지 파일
    public ?UploadedFile $image_file;

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

    public function load(array $data): MemberCreateRequest
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

        unset($this->config);
        unset($this->member_config);

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
        if (is_prohibited_word($this->mb_id, $this->member_config)) {
            $this->throwException("이미 예약된 단어로 사용할 수 없는 아이디 입니다.");
        }
    }

    protected function validatePassword()
    {
        if (empty(trim($this->mb_password))) {
            $this->throwException("비밀번호를 입력해주세요.");
        }
    }

    protected function validateName()
    {
        if (empty(trim($this->mb_name))) {
            $this->throwException("이름을 입력해주세요.");
        }
        if (!is_valid_utf8_string($this->mb_name)) {
            $this->throwException("이름을 올바르게 입력해 주십시오.");
        }
    }

    protected function validateNickName()
    {
        if (empty(trim($this->mb_nick))) {
            $this->throwException("닉네임을 입력해주세요.");
        }
        if (!is_valid_utf8_string($this->mb_nick)) {
            $this->throwException("닉네임을 올바르게 입력해 주십시오.");
        }
        if (!is_valid_mb_nick($this->mb_nick)) {
            $this->throwException("닉네임은 공백없이 한글, 영문, 숫자만 입력 가능합니다.");
        }
        if (is_prohibited_word($this->mb_nick, $this->member_config)) {
            $this->throwException("이미 예약된 단어로 사용할 수 없는 닉네임 입니다.");
        }
    }

    protected function validateEmail()
    {
        if (empty(trim($this->mb_email))) {
            $this->throwException("이메일 주소를 입력해주세요.");
        }
        if (!is_valid_email($this->mb_email)) {
            $this->throwException("잘못된 형식의 이메일 주소입니다.");
        }
        if (is_prohibited_email_domain($this->mb_email, $this->member_config)) {
            $this->throwException("{$this->mb_email} 메일은 사용할 수 없습니다.");
        }
    }

    protected function validateHp()
    {
        if (!is_valid_hp($this->mb_hp)) {
            $this->throwException("휴대폰번호를 올바르게 입력해 주십시오.");
        }
    }

    protected function processPassword()
    {
        $this->mb_password = get_encrypt_string($this->mb_password);
    }
}
