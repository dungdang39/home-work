<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;

class BannerUpdateRequest
{
    use SchemaHelperTrait;

    public ?string $bn_title;
    public ?string $bn_alt;
    public ?string $bn_url;
    public ?string $bn_position;
    public ?string $bn_target;
    public ?string $bn_start_datetime;
    public ?string $bn_end_datetime;
    public ?int $bn_order;
    public ?int $bn_is_enabled;
    public ?int $bn_image_del = 0;
    public ?int $bn_mobile_image_del = 0;
    public ?UploadedFile $bn_image_file;
    public ?UploadedFile $bn_mobile_image_file;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);

        // 이미지 유효성 검사
        $this->validateImage();
        $this->validateMobileImage();
    }

    /**
     * 이미지 파일 유효성 검사
     * 
     * @return void
     */
    private function validateImage(): void
    {
        if (Validator::isUploadedFile($this->bn_image_file)) {
            if (!Validator::isImage($this->bn_image_file)) {
                $this->throwException('이미지 파일만 업로드 할 수 있습니다.');
            }
        }
    }

    /**
     * 모바일 이미지 파일 유효성 검사
     * 
     * @return void
     */
    private function validateMobileImage(): void
    {
        if (Validator::isUploadedFile($this->bn_mobile_image_file)) {
            if (!Validator::isImage($this->bn_mobile_image_file)) {
                $this->throwException('이미지 파일만 업로드 할 수 있습니다.');
            }
        }
    }
}
