<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Sanitizer;
use Slim\Http\ServerRequest as Request;

/**
 *  커뮤니티 > 커뮤니티 설정 > 기본환경설정 업데이트 요청 객체
 */
class BoardConfigRequest
{
    use SchemaHelperTrait;

    // 커뮤니티 설정
    public ?int $display_name_limit = 0;
    public ?int $nickname_edit_limit = 0;
    public ?int $info_edit_limit = 0;
    public ?int $recent_post_lines = 0;
    public ?int $recent_post_delete = 0;
    public ?int $page_rows = 0;
    public ?int $mobile_page_rows = 0;
    public ?int $pages_display_count = 0;
    public ?int $mobile_pages_display_count = 0;
    public ?string $basic_editor = '';
    public ?string $basic_captcha = '';
    public ?string $audio_captcha_choice = '';
    public ?string $recaptcha_site_key = '';
    public ?string $recaptcha_secret_key = '';
    public ?int $write_interval = 0;
    public ?string $link_target = '';
    public ?int $read_point = 0;
    public ?int $write_point = 0;
    public ?int $comment_point = 0;
    public ?int $download_point = 0;
    public ?int $search_unit = 0;
    public ?string $image_upload_extensions = '';
    public ?string $video_upload_extensions = '';
    public ?string $word_filtering = '';

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);

        Sanitizer::cleanXssAll($this);
    }
}