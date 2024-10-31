<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

class UpdateQaReqeust
{
    use SchemaHelperTrait;

    public ?string $status = '';
    public ?string $content = '';

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    protected function validate(): void
    {
        if (!Validator::required($this->status)) {
            $this->throwException('Q&A 상태를 선택해주세요.');
        }
    }
}