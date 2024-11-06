<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

/**
 * 메일 테스트 요청 객체
 */
class SendMailTestRequest
{
    use SchemaHelperTrait;

    public ?string $mail_address = '';
    public ?string $mail_name = '';

    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->initializeFromRequest($request);
    }

    protected function validate()
    {
        $this->validateRequired('mail_address', '메일 발송 주소');
        $this->validateRequired('mail_name', '메일 발송 이름');
        if (!Validator::isValidEmail($this->mail_address)) {
            $this->throwException('메일 발송 주소가 올바르지 않은 형식입니다.');
        }
    }
}