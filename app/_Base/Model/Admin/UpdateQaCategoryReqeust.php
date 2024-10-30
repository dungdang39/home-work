<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

class UpdateQaCategoryReqeust
{
    use SchemaHelperTrait;

    public ?int $is_enabled = 0;
    public ?string $title;
    public ?string $template;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }
}