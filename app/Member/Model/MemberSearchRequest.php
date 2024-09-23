<?php

namespace App\Member\Model;

use Core\Model\PaginationRequest;

class MemberSearchRequest extends PaginationRequest
{
    public ?string $status;

    public ?string $field;

    public ?string $keyword;
}
