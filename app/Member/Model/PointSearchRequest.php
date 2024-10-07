<?php

namespace App\Member\Model;

use Core\Model\PaginationRequest;

class PointSearchRequest extends PaginationRequest
{
    public ?string $field;

    public ?string $keyword;
}
