<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Sanitizer;
use Slim\Http\ServerRequest as Request;

/**
 * 회원 > 기본환경설정 업데이트 요청 객체
 */
class MemberConfigRequest
{
    use SchemaHelperTrait;

    // 회원가입 입력 설정
    public ?bool $address_input_enabled = false;
    public ?bool $address_input_required = false;
    public ?bool $signature_input_enabled = false;
    public ?bool $signature_input_required = false;
    public ?bool $telephone_input_enabled = false;
    public ?bool $telephone_input_required = false;
    public ?bool $phone_input_enabled = false;
    public ?bool $phone_input_required = false;
    public ?int $signup_level = 2;
    public ?int $signup_point = 0;
    public ?bool $member_image_enabled = false;
    public ?int $member_image_upload_level;
    public ?int $member_image_max_size = 0;
    public ?int $member_image_width = 0;
    public ?int $member_image_height = 0;
    public ?bool $recommend_enabled = false;
    public ?int $recommend_point = 0;
    public ?string $prohibit_word = '';
    public ?string $prohibit_domain = '';
    public ?string $signup_terms;
    public ?string $privacy_policy;

    // 개인정보처리
    public ?int $retention_period = 0;

    // 인증 설정
    public ?bool $email_verification_enabled = false;
    public ?bool $identity_verification_enabled = false;
    public ?bool $identity_verification_product = false;
    public ?bool $identity_verification_required = false;
    public ?string $identity_verification_service;
    public ?bool $kg_sso_encryption_enabled = false;
    public ?string $kg_mid;
    public ?string $kg_api_key;
    public ?string $kcp_site_code;
    public ?int $identity_verification_limit = 0;

    // 포인트 설정
    public ?bool $use_point = false;
    public ?int $point_term = 0;
    public ?int $login_point = 0;
    public ?int $memo_send_point = 0;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);

        Sanitizer::cleanXssAll($this, ['signup_terms', 'privacy_policy']);
        $this->prohibit_word = Sanitizer::removeDuplicateLines($this->prohibit_word);
        $this->prohibit_domain = Sanitizer::removeDuplicateLines($this->prohibit_domain);
    }
}