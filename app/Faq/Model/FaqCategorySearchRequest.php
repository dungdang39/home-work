<?php

namespace App\Faq\Model;

use Core\Traits\SchemaHelperTrait;

class FaqCategorySearchRequest
{
    use SchemaHelperTrait;

    public ?string $search_field;

    public ?string $search_text;

    public function __construct(array $query_params = [])
    {
        $this->mapDataToProperties($this, $query_params);
    }
}
