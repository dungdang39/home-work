<?php

namespace App\Qa\Model;

use Core\Traits\SchemaHelperTrait;
use Psr\Http\Message\ServerRequestInterface;

/**
 * 1:1문의 설정 요청
 */
class QaConfigRequest
{
    use SchemaHelperTrait;

    public string $qa_title;
    public string $qa_category = '';
    public int $qa_use_email = 0;
    public int $qa_req_email = 0;
    public int $qa_use_hp = 0;
    public int $qa_req_hp = 0;
    public int $qa_use_sms = 0;
    public string $qa_send_number;
    public int $qa_use_editor = 0;
    public int $qa_image_width = 0;
    public int $qa_upload_size = 0;
    public ?string $qa_insert_content = '';

    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);

        $this->validate();
    }

    /**
     * 유효성 검사
     * @throws Exception 유효성 검사 실패 시 예외 발생
     */
    private function validate(): void
    {
        if (empty($this->qa_title)) {
            $this->throwException('타이틀을 입력해주세요.');
        }
        if (empty($this->qa_category)) {
            $this->throwException('카테고리를 입력해주세요.');
        }
    }
}