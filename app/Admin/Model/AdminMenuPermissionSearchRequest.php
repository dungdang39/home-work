<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;

class AdminMenuPermissionSearchRequest
{
    use SchemaHelperTrait;

    public ?string $search_text = null;

    public function __construct(array $query_params = [])
    {
        $this->mapDataToProperties($this, $query_params);
    }

    public static function load(array $query_params): self
    {
        return new self($query_params);
    }
}
