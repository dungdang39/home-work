<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

/**
 * 회원 > 회원설정 > 알림/푸시 요청
 */
class MemberConfigNotificationRequest
{
    use SchemaHelperTrait;

    public ?string $notification_member_on_signup_type = '';
    public ?string $notification_member_on_signup_preset = '';
    public ?string $notification_member_on_leave_type = '';
    public ?string $notification_member_on_leave_preset = '';
    public ?string $notification_admin_on_signup_type = '';
    public ?string $notification_admin_on_signup_preset = '';
    public ?string $notification_admin_on_leave_type = '';
    public ?string $notification_admin_on_leave_preset = '';
    public ?string $notification_superadmin_on_signup_type = '';
    public ?string $notification_superadmin_on_signup_preset = '';
    public ?string $notification_superadmin_on_leave_type = '';
    public ?string $notification_superadmin_on_leave_preset = '';

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    protected function validate(): void
    {
        if (Validator::required($this->notification_member_on_signup_type)) {
            $this->validateRequired('notification_member_on_signup_preset', '가입 시 회원에게 알림 프리셋');
        }
        if (Validator::required($this->notification_member_on_leave_type)) {
            $this->validateRequired('notification_member_on_leave_preset', '탈퇴 시 회원에게 알림 프리셋');
        }
        if (Validator::required($this->notification_admin_on_signup_type)) {
            $this->validateRequired('notification_admin_on_signup_preset', '가입 시 운영진에게 알림 프리셋');
        }
        if (Validator::required($this->notification_admin_on_leave_type)) {
            $this->validateRequired('notification_admin_on_leave_preset', '탈퇴 시 운영진에게 알림 프리셋');
        }
        if (Validator::required($this->notification_superadmin_on_signup_type)) {
            $this->validateRequired('notification_superadmin_on_signup_preset', '가입 시 최고관리자에게 알림 프리셋');
        }
        if (Validator::required($this->notification_superadmin_on_leave_type)) {
            $this->validateRequired('notification_superadmin_on_leave_preset', '탈퇴 시 최고관리자에게 알림 프리셋');
        }
    }
}