<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

class NotificationRequest
{
    use SchemaHelperTrait;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    protected function validate()
    {
    }
}