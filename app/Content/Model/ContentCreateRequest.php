<?php

namespace App\Content\Model;

use Core\Traits\SchemaHelperTrait;

class ContentCreateRequest
{
    use SchemaHelperTrait;

    public ?string $code;
    public ?string $title;
    public ?string $content;
    public ?string $mobile_content;
    public ?string $head_include_file;
    public ?string $foot_include_file;
    public ?string $head_image;
    public ?string $foot_image;

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }

    public function validate(): void
    {
        // 필수 값 검증
        if (empty($this->code)) {
            throw new \InvalidArgumentException("컨텐츠 코드를 입력하세요.");
        }

        if (empty($this->title)) {
            throw new \InvalidArgumentException("컨텐츠 타이틀을 입력하세요.");
        }

        if (empty($this->content)) {
            throw new \InvalidArgumentException("내용을 입력하세요.");
        }
    }
}
