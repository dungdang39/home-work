<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

class MemberMemoRequest
{
    use SchemaHelperTrait;

    public ?string $memo = '';

    public function __construct(
        Request $request,
    ) {
        $this->initializeFromRequest($request);
    }
}
