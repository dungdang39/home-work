<?php

namespace App\Banner\Model;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;

class BannerUpdateRequest
{
    use SchemaHelperTrait;

    public ?string $bn_title;
    public ?string $bn_alt;
    public ?string $bn_image;
    public ?string $bn_mobile_image;
    public ?string $bn_url;
    public ?string $bn_position;
    public ?string $bn_target;
    public ?string $bn_start_datetime;
    public ?string $bn_end_datetime;
    public ?int $bn_order;
    public ?int $bn_is_enabled;

    public ?UploadedFile $image_file;
    public ?UploadedFile $mobile_image_file;

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
        $image = $this->image_file;
        if (!$this->isValidUploadFile($image)) {
            return;
        }
        $this->validExtension($image);
        $this->validImageFromUri($image);
    }

    /**
     * 모바일 이미지 파일 유효성 검사
     * 
     * @return void
     */
    private function validateMobileImage(): void
    {
        $image = $this->mobile_image_file;
        if (!$this->isValidUploadFile($image)) {
            return;
        }
        $this->validExtension($image);
        $this->validImageFromUri($image);
    }

    /**
     * 파일이 전달 되었는지 확인
     * 
     * @param UploadedFile|null $image
     * @return bool
     */
    private function isValidUploadFile(?UploadedFile $image): bool
    {
        if ($image === null || $image->getError() !== UPLOAD_ERR_OK) {
            return false;
        }
        return true;
    }

    /**
     * 파일 확장자 유효성 검사
     * 
     * @param UploadedFile $image
     * @return void
     * @throws \Exception  이미지 확장자가 아닌 경우 예외 발생
     */
    private function validExtension(UploadedFile $image): void
    {
        $filename = $image->getClientFilename();
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($extension, ['gif', 'jpg', 'jpeg', 'bmp', 'png'])) {
            $this->throwException("이미지 파일만 업로드 할 수 있습니다.");
        }
    }

    /**
     * 메타데이터를 이용하여 이미지 파일 유효성 검사
     * 
     * @param UploadedFile $image
     * @return void
     * @throws \Exception  이미지 파일이 아닌 경우 예외 발생
     */
    private function validImageFromUri(UploadedFile $image): void
    {
        $tmp_file_path = $image->getStream()->getMetadata('uri');
        $image_info = @getimagesize($tmp_file_path);
        if (
            $image_info === false
            || !in_array(
                $image_info[2],
                [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_BMP, IMAGETYPE_PNG],
                true
            )
        ) {
            $this->throwException("유효한 이미지 파일만 업로드 할 수 있습니다.");
        }
    }
}
