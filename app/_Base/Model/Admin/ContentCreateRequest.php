<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;

class ContentCreateRequest
{
    use SchemaHelperTrait;

    public ?string $code;
    public ?string $title;
    public ?string $content;
    public ?string $head_include_file;
    public ?string $foot_include_file;
    public ?string $head_image;
    public ?string $foot_image;
    public ?UploadedFile $head_image_file = null;
    public ?UploadedFile $foot_image_file = null;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    public function validate(): void
    {
        if (!Validator::required($this->code)) {
            $this->throwException("컨텐츠 코드를 입력하세요.");
        }
        if (!Validator::required($this->title)) {
            $this->throwException("컨텐츠 타이틀을 입력하세요.");
        }
        if (!Validator::required($this->content)) {
            $this->throwException("내용을 입력하세요.");
        }
    }
}
