<?php

namespace App\Faq\Model;

use Core\Model\PaginationRequest;

class FaqCategorySearchRequest extends PaginationRequest
{
    public ?string $search_field;

    public ?string $search_text;
}
