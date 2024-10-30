<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

class MemberNotificationRequest
{
    use SchemaHelperTrait;

    public ?string $notification_message = '';

    public function __construct(
        Request $request
    ) {
        $this->initializeFromRequest($request);
    }

    protected function validate(): void
    {
        if (!Validator::required($this->notification_message)) {
            $this->throwException('알림 메시지는 필수입니다.');
        }
    }
}
