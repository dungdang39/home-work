<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Sanitizer;
use Slim\Http\ServerRequest as Request;

/**
 * 회원 > 기본환경설정 업데이트 요청 객체
 */
class MemberConfigNotificationRequest
{
    use SchemaHelperTrait;

    public ?int $member_signup_notify = 0;
    public ?string $member_signup_send_type = '';
    public ?string $member_signup_preset = '';
    public ?int $member_leave_notify = 0;
    public ?string $member_leave_send_type = '';
    public ?string $member_leave_preset = '';
    public ?int $admin_signup_notify = 0;
    public ?string $admin_signup_send_type = '';
    public ?string $admin_signup_preset = '';
    public ?int $admin_leave_notify = 0;
    public ?string $admin_leave_send_type = '';
    public ?string $admin_leave_preset = '';
    public ?int $superadmin_signup_notify = 0;
    public ?string $superadmin_signup_send_type = '';
    public ?string $superadmin_signup_preset = '';
    public ?int $superadmin_leave_notify = 0;
    public ?string $superadmin_leave_send_type = '';
    public ?string $superadmin_leave_preset = '';

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }
}