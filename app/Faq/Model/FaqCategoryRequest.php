<?php

namespace App\Faq\Model;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

class FaqCategoryRequest
{
    use SchemaHelperTrait;

    public ?string $subject;
    public ?int $is_enabled = 0;
    public ?int $order = 0;

    public function __construct(Request $request, Validator $validator)
    {
        $this->initializeFromRequest($request, $validator);
    }

    public function validate(): void
    {
        // 필수 값 검증
        if (empty($this->subject)) {
            throw new \InvalidArgumentException("FAQ 카테고리 제목을 입력하세요.");
        }
    }
}
