<?php

namespace App\Popup\Model;

use Core\Traits\SchemaHelperTrait;

class PopupSearchRequest
{
    use SchemaHelperTrait;

    public ?string $pu_device;

    public function __construct(array $query_params = [])
    {
        $this->mapDataToProperties($this, $query_params);
    }
}
