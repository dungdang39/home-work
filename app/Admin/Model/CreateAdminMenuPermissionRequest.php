<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;

class CreateAdminMenuPermissionRequest
{
    use SchemaHelperTrait;

    public string $mb_id;
    public int $admin_menu_id;
    public ?int $read = 0;
    public ?int $write = 0;
    public ?int $delete = 0;

    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);
        $this->validate();
    }

    private function validate()
    {
        if (empty($this->mb_id)) {
            $this->throwException('아이디를 입력해주세요.');
        }
        if (empty($this->admin_menu_id)) {
            $this->throwException('관리자 메뉴를 선택해주세요.');
        }
    }
}