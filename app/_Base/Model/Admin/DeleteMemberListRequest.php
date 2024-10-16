<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

class DeleteMemberListRequest
{
    use SchemaHelperTrait;

    public ?array $chk = [];
    public array $members = [];

    public function __construct(
        Request $request
    ) {
        $this->initializeFromRequest($request);

        $this->members = $this->chk;
    }
}
