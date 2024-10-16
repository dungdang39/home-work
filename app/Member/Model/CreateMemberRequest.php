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
    public string $mb_id_hash = '';
    public string $mb_password;
    public int $mb_level;

    // 개인정보
    public string $mb_name;
    public string $mb_nick;
    public string $mb_email;
    public ?string $mb_nick_date = '';
    public ?string $mb_image;
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
    public ?UploadedFile $mb_image_file;

    private array $member_config;
    private array $config;

    public function __construct(
        MemberConfigService $member_config_service,
        Request $request,
    ) {
        $this->config = ConfigService::getConfig();
        $this->member_config = $member_config_service->getMemberConfig();

        $this->initializeFromRequest($request);
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
        if (
            $this->member_config['required_phone']
            && ($this->member_config['use_phone'] || $this->member_config['auth_service'])
        ) {
            $this->validateHp();
        }
        $this->validateImageFile();
    }

    protected function afterValidate()
    {
        $this->mb_id_hash = createMemberIdHash($this->mb_id);
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

        if (!Validator::required($this->mb_id)) {
            $this->throwException('아이디를 입력해주세요.');
        }
        if (!Validator::isAlnum($this->mb_id)) {
            $this->throwException('아이디는 영문, 숫자만 입력하세요.');
        }
        if (!Validator::isMinLength($this->mb_id, $min_length)) {
            $this->throwException("아이디는 최소 {$min_length}글자 이상 입력하세요.");
        }
        if (Validator::isProhibitedWord($this->mb_id, $prohibit_words)) {
            $this->throwException('이미 예약된 단어로 사용할 수 없는 아이디 입니다.');
        }
    }

    private function validatePassword()
    {
        $min_length = 4;
        $max_length = 20;

        if (!Validator::required($this->mb_password)) {
            $this->throwException('비밀번호를 입력해주세요.');
        }
        if (!Validator::isBetweenLength($this->mb_password, $min_length, $max_length)) {
            $this->throwException("비밀번호는 최소 {$min_length} 글자, 최대 {$max_length} 글자 이상 입력하세요.");
        }
    }

    private function validateName()
    {
        if (!Validator::required($this->mb_name)) {
            $this->throwException('이름을 입력해주세요.');
        }
        if (!Validator::isUtf8String($this->mb_name)) {
            $this->throwException('이름을 올바르게 입력해 주십시오.');
        }
    }

    private function validateNickName()
    {
        if (!Validator::required($this->mb_nick)) {
            $this->throwException('닉네임을 입력해주세요.');
        }
        if (!Validator::isUtf8String($this->mb_nick)) {
            $this->throwException('닉네임을 올바르게 입력해 주십시오.');
        }
        if (!Validator::isAlnumko($this->mb_nick)) {
            $this->throwException('닉네임은 한글, 영문, 숫자만 입력하세요.');
        }
        // $prohibit_words = explode("\n", $this->member_config['prohibit_word']);
        // if (Validator::isProhibitedWord($this->mb_nick, $prohibit_words)) {
        //     $this->throwException('이미 예약된 단어로 사용할 수 없는 닉네임 입니다.');
        // }
    }

    private function validateEmail()
    {
        $prohibit_domains = explode("\n", $this->member_config['prohibit_domain']);

        if (!Validator::required($this->mb_email)) {
            $this->throwException('이메일 주소를 입력해주세요.');
        }
        if (!Validator::isValidEmail($this->mb_email)) {
            $this->throwException('이메일 주소가 올바르지 않습니다.');
        }
        if (!Validator::isProhibitedDomain($this->mb_email, $prohibit_domains)) {
            $this->throwException('입력하신 도메인은 사용할 수 없습니다.');
        }
    }

    private function validateHp()
    {
        if (!Validator::isValidPhoneNumber($this->mb_hp)) {
            $this->throwException("휴대폰번호를 올바르게 입력해 주십시오.");
        }
    }

    private function validateImageFile()
    {
        if (Validator::isUploadedFile($this->mb_image_file)) {
            if (!Validator::isImage($this->mb_image_file)) {
                $this->throwException('이미지 파일만 업로드 할 수 있습니다.');
            }
        }
    }
}
