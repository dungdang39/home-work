<?php

namespace App\Base\Model\Admin;

use Core\Model\PaginationRequest;

class MemberSearchRequest extends PaginationRequest
{
    public ?string $status;

    public ?string $field;

    public ?string $keyword;
}
