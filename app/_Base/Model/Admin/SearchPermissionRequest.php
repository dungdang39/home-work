<?php

namespace App\Base\Model\Admin;

use Core\Model\PaginationRequest;

class SearchPermissionRequest extends PaginationRequest
{
    public ?string $mb_id;

    public ?string $search_field;

    public ?string $search_text;
}
