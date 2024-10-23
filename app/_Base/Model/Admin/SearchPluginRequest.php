<?php

namespace App\Base\Model\Admin;

use Core\Model\PaginationRequest;

class SearchPluginRequest extends PaginationRequest
{
    public ?string $status;

    public ?string $search_text;
}
