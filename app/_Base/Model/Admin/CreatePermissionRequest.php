<?php

namespace App\Base\Model\Admin;

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

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    protected function validate()
    {
        $this->validateMemberId();
        $this->validateAdminMenuId();
    }

    private function validateMemberId()
    {
        if (!Validator::required($this->mb_id)) {
            $this->throwException('아이디를 입력해주세요.');
        }
    }

    private function validateAdminMenuId()
    {
        if (!Validator::required($this->admin_menu_id)) {
            $this->throwException('관리자 메뉴를 입력해주세요.');
        }
    }
}
