<?php

namespace App\Member\Model;

use App\Config\ConfigService;
use App\Member\MemberConfigService;
use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;

class CreateMemberRequest
{
    use SchemaHelperTrait;

    // 기본 정보
    public string $mb_id;
    public string $mb_password;
    public int $mb_level;

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
    public ?int $mb_mailling = 0;
    public ?int $mb_sms = 0;
    public ?int $mb_open = 0;
    public ?string $mb_leave_date = null;
    public ?string $mb_intercept_date = null;

    // 기타 정보
    public ?string $mb_signup_ip = '';
    public ?string $mb_email_verified_at = null;

    // 회원 이미지 파일
    public ?UploadedFile $mb_img = null;

    private array $member_config;
    private array $config;
    private Validator $validator;

    public function __construct(
        ConfigService $config_service,
        MemberConfigService $member_config_service,
        Request $request,
        Validator $validator
    ) {
        $this->config = $config_service->getConfig();
        $this->member_config = $member_config_service->getMemberConfig();
        $this->validator = $validator;

        $this->initializeFromRequest($request, $validator);
    }

    protected function beforeValidate()
    {
        $this->mb_id = strtolower(trim($this->mb_id));
    }

    protected function validate()
    {
        $this->validateMemberId();
        $this->validatePassword();
        $this->validateName();
        $this->validateNickName();
        $this->validateEmail();
    }

    protected function afterValidate()
    {
        $this->mb_password = password_hash($this->mb_password, PASSWORD_DEFAULT);
        $this->mb_nick_date = date('Y-m-d');
        $this->mb_hp = hyphen_hp_number($this->mb_hp);
        $this->mb_signup_ip = $_SERVER['REMOTE_ADDR'];
        if (!$this->member_config['use_email_certify']) {
            $this->mb_email_verified_at = date('Y-m-d H:i:s');
        }
    }

    private function validateMemberId()
    {
        $min_length = 4;
        $prohibit_words = explode("\n", $this->member_config['prohibit_word']);

        $this->validator->addRule(
            'mb_id',
            [
                ['required' => ['message' => '아이디를 입력해주세요.']],
                ['alnum' => ['message' => '아이디는 영문, 숫자만 입력하세요.']],
                ['min_length' => ['message' => "아이디는 최소 {$min_length}글자 이상 입력하세요.", 'args' => [$min_length]]],
                ['prohibit_word' => ['message' => '이미 예약된 단어로 사용할 수 없는 아이디 입니다.', 'args' => [$prohibit_words]]]
            ]
        );
    }

    private function validatePassword()
    {
        $min_length = 4;
        $max_length = 20;

        $this->validator->addRule(
            'mb_password',
            [
                ['required' => ['message' => '비밀번호를 입력해주세요.']],
                ['between_length' => [
                    'message' => "비밀번호는 최소 {$min_length} 글자, 최대 {$max_length} 글자 이상 입력하세요.",
                    'args' => [$min_length, $max_length]
                ]],
            ]
        );
    }

    private function validateName()
    {
        $this->validator->addRule(
            'mb_name',
            [
                ['required' => ['message' => '이름을 입력해주세요.']],
                ['utf8_string' => ['message' => '이름을 올바르게 입력해 주십시오.']]
            ]
        );
    }

    private function validateNickName()
    {
        $prohibit_words = explode("\n", $this->member_config['prohibit_word']);

        $this->validator->addRule(
            'mb_nick',
            [
                ['required' => ['message' => '닉네임을 입력해주세요.']],
                ['utf8_string' => ['message' => '닉네임을 올바르게 입력해 주십시오.']],
                ['nickname' => ['message' => '닉네임은 한글, 영문, 숫자만 입력하세요.']],
                ['prohibit_word' => ['message' => '이미 예약된 단어로 사용할 수 없는 닉네임 입니다.', 'args' => [$prohibit_words]]]
            ]
        );
    }

    protected function validateEmail()
    {
        $prohibit_domains = explode("\n", $this->member_config['prohibit_domain']);

        $this->validator->addRule(
            'mb_email',
            [
                ['required' => ['message' => '이메일 주소를 입력해주세요.']],
                ['email' => ['message' => '이메일 주소가 올바르지 않습니다.']],
                ['prohibit_domain' => ['message' => '입력하신 도메인은 사용할 수 없습니다.', 'args' => [$prohibit_domains]]]
            ]
        );
    }
}
