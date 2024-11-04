<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

class UpdateBoardRequest
{
    use SchemaHelperTrait;

    public ?int $board_group_id = null;
    public ?string $title = '';
    public ?string $mobile_title = '';
    public ?string $admin_id = '';
    public ?int $list_level = 1;
    public ?int $read_level = 1;
    public ?int $write_level = 1;
    public ?int $comment_level = 1;
    public ?int $reply_level = 1;
    public ?int $upload_level = 1;
    public ?int $download_level = 1;
    public ?int $link_level = 1;
    public ?int $html_level = 1;
    public ?int $comments_limit_for_edit = 0;
    public ?int $comments_limit_for_delete = 0;
    public ?bool $enable_sideview = false;
    public ?int $enable_secret_post = 0;
    public ?bool $enable_dhtml_editor = false;
    public ?string $selected_editor = '';
    public ?bool $enable_rss = false;
    public ?bool $show_ip = false;
    public ?bool $enable_like = false;
    public ?bool $enable_dislike = false;
    public ?bool $use_real_name = false;
    public ?bool $show_signature = false;
    public ?bool $show_content_in_list = false;
    public ?bool $show_files_in_list = false;
    public ?bool $show_full_list = false;
    public ?bool $enable_email_notification = false;
    public ?string $authentication_type = '';
    public ?int $max_file_upload_count = 0;
    public ?int $max_file_upload_size = 0;
    public ?bool $enable_file_description = false;
    public ?int $min_contents_count_limit = 0;
    public ?int $max_contents_count_limit = 0;
    public ?int $min_comment_count_limit = 0;
    public ?int $max_comment_count_limit = 0;
    public ?bool $enable_sns = false;
    public ?bool $enable_search = false;
    public ?int $display_order = 0;
    public ?bool $enable_captcha = false;
    public ?string $skin = '';
    public ?string $header_file_path = '';
    public ?string $footer_file_path = '';
    public ?string $header_content = '';
    public ?string $footer_content = '';
    public ?string $default_template = '';
    public ?int $subject_length_limit = 0;
    public ?int $items_per_page = 0;
    public ?int $board_width = 0;
    public ?int $image_width = 0;
    public ?int $new_icon_time = 0;
    public ?int $popular_icon_time = 0;
    public ?int $reply_sort = 0;
    public ?string $list_sort_field = '';

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    protected function validate(): void
    {
        $this->validateRequired('title', '게시판 타이틀');
    }

    /**
     * 필수 입력 사항 검사
     */
    private function validateRequired(string $field, string $label): void
    {
        if (!Validator::required($this->$field)) {
            $this->throwException("{$label}은(는) 필수 입력 사항입니다.");
        }
    }
}
