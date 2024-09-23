<?php

namespace App\Faq\Model;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

class FaqRequest
{
    use SchemaHelperTrait;

    public ?int $order;
    public ?string $question;
    public ?string $answer;
    public ?int $is_enabled = 0;

    public function __construct(Request $request, Validator $validator)
    {
        $this->initializeFromRequest($request, $validator);
    }

    public function validate(): void
    {
        // 필수 값 검증
        if (empty($this->question)) {
            throw new \InvalidArgumentException("질문을 입력하세요.");
        }

        if (empty($this->answer)) {
            throw new \InvalidArgumentException("답변을 입력하세요.");
        }
    }
}
