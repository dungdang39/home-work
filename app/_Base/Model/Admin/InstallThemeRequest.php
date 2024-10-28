<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;

class InstallThemeRequest
{
    use SchemaHelperTrait;

    public ?UploadedFile $theme_file = null;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    protected function validate()
    {
        if (!Validator::required($this->theme_file)) {
            $this->throwException('테마 압축파일을 선택해주세요.');
        }
        if (!Validator::isArchive($this->theme_file)) {
            $this->throwException('압축파일만 업로드 가능합니다.');
        }
    }
}
