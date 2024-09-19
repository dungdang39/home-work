<?php

namespace App\Content\Model;

use Core\Traits\SchemaHelperTrait;
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
    public ?UploadedFile $head_image_file = null;
    public ?UploadedFile $foot_image_file = null;

    public function __construct(Request $request)
    {
        $body = $request->getParsedBody() ?? [];
        $files = $request->getUploadedFiles() ?? [];

        $this->mapDataToProperties($this, array_merge($body, $files));
    }

    public function validate(): void
    {
        // 필수 값 검증
        if (empty($this->title)) {
            throw new \InvalidArgumentException("컨텐츠 타이틀을 입력하세요.");
        }

        if (empty($this->content)) {
            throw new \InvalidArgumentException("내용을 입력하세요.");
        }
    }
}
