<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;

class InstallPluginRequest
{
    use SchemaHelperTrait;

    public ?UploadedFile $plugin_file = null;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    protected function validate()
    {
        if (!Validator::required($this->plugin_file)) {
            $this->throwException('플러그인 압축파일을 선택해주세요.');
        }
        if (!Validator::isArchive($this->plugin_file)) {
            $this->throwException('압축파일만 업로드 가능합니다.');
        }
    }
}