<?php

namespace App\Faq\Model;

use Core\Model\PaginationRequest;

class FaqCategorySearchRequest extends PaginationRequest
{
    public ?string $field;

    public ?string $keyword;
}
