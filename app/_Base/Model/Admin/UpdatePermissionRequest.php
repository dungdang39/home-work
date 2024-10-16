<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

class UpdatePermissionRequest
{
    use SchemaHelperTrait;

    public ?int $read = 0;
    public ?int $write = 0;
    public ?int $delete = 0;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }
}