<?php

namespace App\Member\Model;

use App\Config\ConfigService;
use App\Member\MemberConfigService;
use Core\Traits\SchemaHelperTrait;
use Slim\Psr7\UploadedFile;

class MemberRequest
{
    use SchemaHelperTrait;

    // 기본 정보
    public string $mb_password;
    public int $mb_level;
    // public ?string $mb_img;  // 이미지 파일 경로

    // 개인정보
    public string $mb_name;
    public string $mb_nick;
    public string $mb_email;
    public ?string $mb_nick_date = '';
    public ?string $mb_homepage = '';
    public ?string $mb_hp = '';
    public ?string $mb_tel = '';
    public ?string $mb_zip = '';
    public ?string $mb_addr1 = '';
    public ?string $mb_addr2 = '';
    public ?string $mb_addr3 = '';
    public ?string $mb_signature = '';
    public ?string $mb_certify = '';
    
    // 관리 정보
    public ?string $mb_memo = '';
    public bool $mb_mailling = false;
    public bool $mb_sms = false;
    public bool $mb_open = false;
    public ?string $mb_leave_date = null;
    public ?string $mb_intercept_date = null;
    public ?string $mb_email_verified_at = null;
    public ?string $mb_signup_ip = '';

    // 회원 이미지 파일
    private ?UploadedFile $image_file;

    private array $member_config;
    private array $config;

    public function __construct(
        MemberConfigService $member_config_service,
        ConfigService $config_service
    ) {
        $this->member_config = $member_config_service->getMemberConfig();
        $this->config = $config_service->getConfig();
    }

    public function load(array $data, array $member): MemberRequest
    {
        $this->mapDataToProperties($this, $data);

        if ($member['mb_nick'] !== $this->mb_nick) {
            $this->validateNickName();
        }
        if ($member['mb_email'] !== $this->mb_email) {
            $this->validateEmail();
            $this->mb_email = get_email_address($this->mb_email);
        }

        if (
            $this->member_config['required_phone']
            && ($this->member_config['use_phone'] || $this->member_config['auth_service'])
        ) {
            $this->validateHp();
        }

        $this->processPassword();
        $this->mb_hp = hyphen_hp_number($this->mb_hp);

        // unset($this->member_config);
        // unset($this->config);

        return $this;
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
        // if (!is_valid_mb_nick($this->mb_nick)) {
        //     $this->throwException("닉네임은 공백없이 한글, 영문, 숫자만 입력 가능합니다.");
        // }
        // if (is_prohibited_word($this->mb_nick, $this->member_config)) {
        //     $this->throwException("이미 예약된 단어로 사용할 수 없는 닉네임 입니다.");
        // }
    }

    protected function validateEmail()
    {
        if (empty(trim($this->mb_email))) {
            $this->throwException("이메일 주소를 입력해주세요.");
        }
        if (!filter_var($this->mb_email, FILTER_VALIDATE_EMAIL)){
            $this->throwException("이메일 주소가 올바르지 않습니다.");
        }
        // if (is_prohibited_email_domain($this->mb_email, $this->member_config)) {
        //     $this->throwException("{$this->mb_email} 메일은 사용할 수 없습니다.");
        // }
    }

    protected function validateHp()
    {
        if (!is_valid_hp($this->mb_hp)) {
            $this->throwException("휴대폰번호를 올바르게 입력해 주십시오.");
        }
    }

    protected function processPassword()
    {
        if (isset($this->mb_password)) {
            $this->mb_password = password_hash($this->mb_password, PASSWORD_DEFAULT);
        }
    }
}
