<?php

namespace App\Base\Model\Admin;

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