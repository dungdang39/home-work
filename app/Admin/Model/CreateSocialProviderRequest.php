<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

class CreateSocialProviderRequest
{
    use SchemaHelperTrait;

    public string $provider_name;
    public string $provider_key;
    public ?string $client_id = '';
    public ?string $client_secret = '';

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    protected function validate()
    {
        if (!Validator::required($this->provider_name)) {
            $this->throwException('제공자명을 입력해주세요.');
        }
        if (!Validator::required($this->provider_key)) {
            $this->throwException('제공자키를 입력해주세요.');
        }
    }
}