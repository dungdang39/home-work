<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

class DeletePermissionListRequest
{
    use SchemaHelperTrait;

    public ?array $chk = [];
    public array $admin_menu_id = [];
    public array $mb_id = [];

    public function __construct(
        Request $request
    ) {
        $this->initializeFromRequest($request);
    }
}