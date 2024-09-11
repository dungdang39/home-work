<?php

namespace App\Member\Model;

use Core\Traits\SchemaHelperTrait;

class MemberSearchRequest
{
    use SchemaHelperTrait;

    public ?string $status;

    public ?string $field;

    public ?string $keyword;

    public function __construct(array $query_params = [])
    {
        $this->mapDataToProperties($this, $query_params);
    }
}
