<?php

namespace App\Qa\Model;

use Core\Traits\SchemaHelperTrait;

/**
 * 1:1문의 설정 요청
 */
class QaConfigRequest
{
    use SchemaHelperTrait;

    public string $qa_title;
    public string $qa_category = '';
    public bool $qa_use_email = false;
    public bool $qa_req_email = false;
    public bool $qa_use_hp = false;
    public bool $qa_req_hp = false;
    public int $qa_use_sms = 0;
    public string $qa_send_number;
    public int $qa_use_editor = 0;
    public int $qa_image_width;
    public int $qa_upload_size;
    public ?string $qa_insert_content = '';

    public function __construct()
    {
    }

    public function load(array $data): self
    {
        $this->mapDataToProperties($this, $data);

        return $this;
    }

    public function validate(): bool
    {
        // 여기에 유효성 검사를 추가할 수 있습니다.
        // 예: $this->qa_title 이 비어 있으면 false 반환
        if (empty($this->qa_title) || empty($this->qa_category)) {
            return false;
        }

        // 기타 유효성 검사 로직 추가
        return true;
    }
}