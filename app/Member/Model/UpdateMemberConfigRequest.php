<?php

namespace App\Member\Model;

use Core\Traits\SchemaHelperTrait;

/**
 * 회원 > 기본환경설정 업데이트 요청 객체
 */
class UpdateMemberConfigRequest
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
    public ?string $prohibit_word;
    public ?string $prohibit_domain;
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

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);

        $this->filterProperties();

        $this->prohibit_word = $this->removeDuplicateLines($this->prohibit_word);
        $this->prohibit_domain = $this->removeDuplicateLines($this->prohibit_domain);
    }

    /**
     * 태그를 허용하는 속성을 제외한 모든 속성 필터링
     * @todo trait로 분리 예정 (config에도 동일코드 존재)
     * @return void
     */
    private function filterProperties(): void
    {
        $allow_tags = ['signup_terms', 'privacy_policy'];

        foreach ($this as $key => $value) {
            if (is_string($value) && !in_array($key, $allow_tags)) {
                $this->$key = strip_tags(clean_xss_attributes($value));
            }
        }
    }
    
    /**
     * 문자열을 "\n"으로 나누고, 중복을 제거한 후 다시 합쳐서 반환하는 함수
     * @param string $input_string
     * @return string
     */
    private function removeDuplicateLines(string $input_string) {
        $lines = explode("\n", $input_string);
        $trimmed_lines = array_map('trim', $lines); // 각 줄에서 공백 제거
        $unique_lines = array_unique(array_filter($trimmed_lines)); // 중복 제거 및 빈 줄 필터링

        // 오름차순 정렬
        sort($unique_lines);
        
        return implode("\n", $unique_lines);
    }
}