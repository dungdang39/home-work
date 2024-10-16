<?php

namespace App\Base\Model\Admin;

use Core\Model\PaginationRequest;

class FaqCategorySearchRequest extends PaginationRequest
{
    public ?string $field;

    public ?string $keyword;
}
