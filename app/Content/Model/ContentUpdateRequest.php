<?php

namespace App\Content\Model;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;

class ContentUpdateRequest
{
    use SchemaHelperTrait;

    public ?string $title;
    public ?string $content;
    public ?string $head_include_file;
    public ?string $foot_include_file;
    public ?string $head_image;
    public ?string $foot_image;
    public ?int $head_image_del = 0;
    public ?int $foot_image_del = 0;
    public ?UploadedFile $head_image_file;
    public ?UploadedFile $foot_image_file;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    public function validate(): void
    {
        if (!Validator::required($this->title)) {
            $this->throwException("컨텐츠 타이틀을 입력하세요.");
        }
        if (!Validator::required($this->content)) {
            $this->throwException("내용을 입력하세요.");
        }
    }
}
