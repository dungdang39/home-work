<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

/**
 * 1:1문의 설정 요청
 */
class QaConfigRequest
{
    use SchemaHelperTrait;

    public ?bool $email_input_enabled = false;
    public ?bool $email_input_required = false;
    public ?bool $hp_input_enabled = false;
    public ?bool $hp_input_required = false;
    public ?bool $sms_notification = false;
    public ?bool $is_use_editor = false;
    public ?bool $is_use_upload = false;
    public ?int $qa_upload_size = 0;
    public ?string $notification_member = 'all';
    public ?string $notification_member_type = 'all';
    public ?string $notification_admin = 'all';
    public ?string $notification_admin_type = 'all';

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }
}