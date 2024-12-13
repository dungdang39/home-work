<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;

/**
 * 로고 업데이트 요청
 */
class UpdateLogoRequest
{
    use SchemaHelperTrait;

    public ?string $logo_header;
    public ?string $logo_footer;

    public ?UploadedFile $logo_header_file;
    public ?UploadedFile $logo_footer_file;

    public ?int $logo_header_del = 0;
    public ?int $logo_footer_del = 0;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    protected function validate()
    {
        $this->validateLogoHeader();
        $this->validateLogoFooter();
    }

    private function validateLogoHeader(): void
    {
        if (Validator::isUploadedFile($this->logo_header_file)) {
            if (!Validator::isImage($this->logo_header_file)) {
                $this->throwException('이미지 파일만 업로드 할 수 있습니다.');
            }
        }
    }

    private function validateLogoFooter(): void
    {
        if (Validator::isUploadedFile($this->logo_footer_file)) {
            if (!Validator::isImage($this->logo_footer_file)) {
                $this->throwException('이미지 파일만 업로드 할 수 있습니다.');
            }
        }
    }
}
