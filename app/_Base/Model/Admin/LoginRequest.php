<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

class LoginRequest
{
    use SchemaHelperTrait;

    public string $mb_id;
    public string $mb_password;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    protected function validate()
    {
        if (!Validator::required($this->mb_id) || !Validator::required($this->mb_password)) {
            $this->throwException('아이디 또는 비밀번호를 입력해주세요.');
        }
    }
}