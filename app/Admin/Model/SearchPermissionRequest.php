<?php

namespace App\Admin\Model;

use Core\Model\PaginationRequest;

class SearchPermissionRequest extends PaginationRequest
{
    public ?string $search_field;

    public ?string $search_text;
}
