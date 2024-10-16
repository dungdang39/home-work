<?php

namespace App\Base\Model\Admin;

use Core\Model\PaginationRequest;

class ContentSearchRequest extends PaginationRequest
{
    public ?string $search_field;

    public ?string $search_text;
}
