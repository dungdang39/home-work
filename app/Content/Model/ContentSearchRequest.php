<?php

namespace App\Content\Model;

use Core\Traits\SchemaHelperTrait;

class ContentSearchRequest
{
    use SchemaHelperTrait;

    public ?string $search_field;

    public ?string $search_text;

    public function __construct(array $query_params = [])
    {
        $this->mapDataToProperties($this, $query_params);
    }
}
