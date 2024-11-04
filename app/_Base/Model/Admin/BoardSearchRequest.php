<?php

namespace App\Base\Model\Admin;

use Core\Model\PaginationRequest;

class BoardSearchRequest extends PaginationRequest
{
    public ?string $field;

    public ?string $keyword;
}