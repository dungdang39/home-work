<?php

namespace App\Member\Model;

use Core\Traits\SchemaHelperTrait;

class MemberSearchRequest
{
    use SchemaHelperTrait;

    public ?string $mb_id;

    public function __construct(array $query_params = [])
    {
        $this->mapDataToProperties($this, $query_params);
    }
}
