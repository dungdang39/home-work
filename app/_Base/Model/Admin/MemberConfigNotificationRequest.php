<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

/**
 * 회원 > 기본환경설정 업데이트 요청 객체
 */
class MemberConfigNotificationRequest
{
    use SchemaHelperTrait;

    public ?bool $notification_member_on_signup = false;
    public ?string $notification_member_on_signup_type = '';
    public ?string $notification_member_on_signup_preset = '';
    public ?bool $notification_member_on_leave= false;
    public ?string $notification_member_on_leave_type = '';
    public ?string $notification_member_on_leave_preset = '';
    public ?bool $notification_admin_on_signup= false;
    public ?string $notification_admin_on_signup_type = '';
    public ?string $notification_admin_on_signup_preset = '';
    public ?bool $notification_admin_on_leave= false;
    public ?string $notification_admin_on_leave_type = '';
    public ?string $notification_admin_on_leave_preset = '';
    public ?bool $notification_superadmin_on_signup= false;
    public ?string $notification_superadmin_on_signup_type = '';
    public ?string $notification_superadmin_on_signup_preset = '';
    public ?bool $notification_superadmin_on_leave= false;
    public ?string $notification_superadmin_on_leave_type = '';
    public ?string $notification_superadmin_on_leave_preset = '';

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }
}