<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

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

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    /**
     * 유효성 검사
     * @throws Exception 유효성 검사 실패 시 예외 발생
     */
    private function validate(): void
    {
        if (!Validator::required($this->qa_title)) {
            $this->throwException('타이틀을 입력해주세요.');
        }
        if (!Validator::required($this->qa_category)) {
            $this->throwException('카테고리를 입력해주세요.');
        }
    }
}