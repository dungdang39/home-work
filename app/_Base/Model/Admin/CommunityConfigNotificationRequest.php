<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

/**
 * 커뮤니티 > 커뮤니티 설정 > 알림/푸시 설정 업데이트 요청 객체
 */
class CommunityConfigNotificationRequest
{
    use SchemaHelperTrait;

    public ?bool $notification_post_author_on_comment = false;
    public ?string $notification_post_author_on_comment_type;
    public ?string $notification_post_author_on_comment_preset;
    public ?bool $notification_comment_author_on_reply = false;
    public ?string $notification_comment_author_on_reply_type;
    public ?string $notification_comment_author_on_reply_preset;
    public ?bool $notification_board_admin_on_post = false;
    public ?string $notification_board_admin_on_post_type;
    public ?string $notification_board_admin_on_post_preset;
    public ?bool $notification_group_admin_on_post = false;
    public ?string $notification_group_admin_on_post_type;
    public ?string $notification_group_admin_on_post_preset;
    public ?bool $notification_superadmin_on_post = false;
    public ?string $notification_superadmin_on_post_type;
    public ?string $notification_superadmin_on_post_preset;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }
}