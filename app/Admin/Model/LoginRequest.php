<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;


class LoginRequest
{
    use SchemaHelperTrait;

    public string $mb_id;
    public string $mb_password;

    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);
        $this->validate();
    }

    private function validate()
    {
        if (empty($this->mb_id) || empty($this->mb_password)) {
            $this->throwException('아이디 또는 비밀번호를 입력해주세요.');
        }
    }
}