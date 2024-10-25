<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

class CreateSocialProviderRequest
{
    use SchemaHelperTrait;

    public string $provider;
    public ?int $is_test = 0;
    public array $keys = [];

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    protected function validate()
    {
        if (!Validator::required($this->provider)) {
            $this->throwException('서비스 제공자를 입력해주세요.');
        }
    }
}