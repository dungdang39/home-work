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
    public ?bool $use_address = false;
    public ?bool $required_address = false;
    public ?bool $use_signature = false;
    public ?bool $required_signature = false;
    public ?bool $use_telephone = false;
    public ?bool $required_telephone = false;
    public ?bool $use_phone = false;
    public ?bool $required_phone = false;
    public ?int $signup_level = 2;
    public ?int $signup_point = 0;
    public ?bool $use_member_image = false;
    public ?int $upload_permission_level;
    public ?int $member_image_size = 0;
    public ?int $member_image_width = 0;
    public ?int $member_image_height = 0;
    public ?bool $use_recommend = false;
    public ?int $recommend_point = 0;
    public ?string $prohibit_word = '';
    public ?string $prohibit_domain = '';
    public ?string $signup_terms;
    public ?string $privacy_policy;

    // 개인정보처리
    public ?int $retention_period = 0;

    // 인증 설정
    public ?bool $use_email_certify = false;
    public ?bool $use_authentication = false;
    public ?bool $is_auth_production = false;
    public ?bool $authentication_required = false;
    public ?string $auth_service;
    public ?bool $cert_service = false;
    public ?string $cert_kg_mid;
    public ?string $cert_kg_cd;
    public ?string $cert_kcp_cd;
    public ?int $cert_limit = 0;

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