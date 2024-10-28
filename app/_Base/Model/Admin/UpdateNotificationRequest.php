<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

class UpdateNotificationRequest
{
    use SchemaHelperTrait;

    public array $notifications;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    protected function validate(): void
    {
        foreach ($this->notifications as $noti => $value) {
            if ($noti === 'email' && $value['is_enabled']) {
                if (!Validator::required($value['settings']['email_address'])) {
                    $this->throwException('발송 메일 주소를 입력해주세요.');
                }
            }
            if ($noti === 'sms' && $value['is_enabled']) {
                if (!Validator::required($value['settings']['sms_type'])) {
                    $this->throwException('SMS 전송 유형을 선택해주세요.');
                }
            }
            if ($noti === 'alimtalk' && $value['is_enabled']) {
                if (!Validator::required($value['settings']['alimtalk_service'])) {
                    $this->throwException('알림톡 서비스를 선택해주세요.');
                }
            }
        }
    }
}