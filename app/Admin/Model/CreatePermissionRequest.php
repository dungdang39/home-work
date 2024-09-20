<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

class CreatePermissionRequest
{
    use SchemaHelperTrait;

    public string $mb_id;
    public int $admin_menu_id;
    public ?int $read = 0;
    public ?int $write = 0;
    public ?int $delete = 0;

    private Validator $validator;

    public function __construct(Request $request, Validator $validator)
    {
        $this->validator = $validator;
        $this->initializeFromRequest($request, $this->validator);
    }

    protected function validate()
    {
        $this->mb_id = '';
        $this->validateMemberId();
        $this->validateAdminMenuId();
    }

    private function validateMemberId()
    {
        $this->validator->addRule(
            'mb_id',
            [
                ['required' => ['message' => '아이디를 입력해주세요.']],
            ]
        );
    }

    private function validateAdminMenuId()
    {
        $this->validator->addRule(
            'admin_menu_id',
            [
                ['required' => ['message' => '관리자 메뉴를 선택해주세요.']],
            ]
        );
    }
}
