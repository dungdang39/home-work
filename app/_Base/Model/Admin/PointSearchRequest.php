<?php

namespace App\Base\Model\Admin;

use Core\Model\PaginationRequest;

class PointSearchRequest extends PaginationRequest
{
    public ?string $field;

    public ?string $keyword;
}
