<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;
use Slim\Psr7\UploadedFile;

/**
 * 테마 설정 업데이트 요청
 */
class UpdateThemeConfigRequest
{
    use SchemaHelperTrait;

    public ?string $logo_header = '';
    public ?string $logo_footer = '';

    public ?string $layout_community = '';
    public ?string $layout_member = '';
    public ?string $layout_shop = '';
    public ?string $layout_content = '';

    public UploadedFile $logo_header_file;
    public UploadedFile $logo_footer_file;

    public function __construct(array $data = [], array $files = [])
    {
        $this->mapDataToProperties($this, $data);

        // 파일 처리
        $this->logo_header_file = $files['logo_header_file'] ?? null;
        $this->logo_footer_file = $files['logo_footer_file'] ?? null;

        $this->validateImageFile($this->logo_header_file);
        $this->validateImageFile($this->logo_footer_file);
    }

    /**
     * 모바일 이미지 파일 유효성 검사
     * 
     * @return void
     */
    private function validateImageFile(?UploadedFile $file): void
    {
        if (!$this->isValidUploadFile($file)) {
            return;
        }
        $this->validExtension($file);
        $this->validImageFromUri($file);
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