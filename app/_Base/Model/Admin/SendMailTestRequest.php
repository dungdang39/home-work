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
        $this->test_mail_addresses = array_unique($this->test_mail_addresses);
    }

    protected function validate()
    {
        $this->validateRequired($this->mail_address, '메일 발송 주소');
        $this->validateEmail($this->mail_address, '메일 발송 주소');
        $this->validateRequired($this->mail_name, '메일 발송 이름');
        $this->validateRequired($this->test_mail_addresses, '테스트 메일 주소');
        foreach ($this->test_mail_addresses as $test_mail_address) {
            $this->validateEmail($test_mail_address, '테스트 메일 주소');
        }
    }

    protected function validateRequired($value, string $label): void
    {
        if (!Validator::required($value)) {
            $this->throwMailSendException("{$label}은(는) 필수 입력 사항입니다.");
        }
    }

    protected function validateEmail(string $email, string $label): void
    {
        if (!Validator::isValidEmail($email)) {
            $this->throwMailSendException("{$label} 올바르지 않은 이메일 형식입니다. ({$email})");
        }
    }

    /**
     * 메일 발송실패 예외 발생
     * @param string $message
     * @return void
     * @throws Exception
     */
    private function throwMailSendException(string $message): void
    {
        $this->throwException("메일 발송에 실패했어요.\n(사유: {$message})");
    }
}
