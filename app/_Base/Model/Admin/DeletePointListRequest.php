<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

class DeletePointListRequest
{
    use SchemaHelperTrait;

    public ?array $chk = [];
    public array $po_ids = [];

    public function __construct(
        Request $request
    ) {
        $this->initializeFromRequest($request);

        $this->po_ids = $this->chk;
    }
}
