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
    public ?array $test_mail_addresses = [];

    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->initializeFromRequest($request);
    }

    protected function beforeValidate(): void
    {
        $this->test_mail_addresses = array_unique(
            array_filter($this->test_mail_addresses, function ($email) {
                return Validator::isValidEmail($email);
            })
        );
    }

    protected function validate()
    {
        $this->validateRequired('mail_address', '메일 발송 주소');
        $this->validateRequired('mail_name', '메일 발송 이름');
        $this->validateRequired('test_mail_addresses', '테스트 메일 주소');
        if (!Validator::isValidEmail($this->mail_address)) {
            $this->throwException('메일 발송 주소가 올바르지 않은 형식입니다.');
        }
    }
}
