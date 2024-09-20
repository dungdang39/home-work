<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

class UpdatePermissionRequest
{
    use SchemaHelperTrait;

    public ?int $read = 0;
    public ?int $write = 0;
    public ?int $delete = 0;

    public function __construct(Request $request, Validator $validator)
    {
        $this->initializeFromRequest($request, $validator);
    }
}