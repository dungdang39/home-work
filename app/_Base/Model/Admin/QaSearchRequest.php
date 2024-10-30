<?php

namespace App\Base\Model\Admin;

use Core\Model\PaginationRequest;

class QaSearchRequest extends PaginationRequest
{
    public ?int $category_id;

    public ?string $status;

    public ?string $keyword;
}
