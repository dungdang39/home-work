<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;

class UpdateConfigRequest
{
    use SchemaHelperTrait;

    

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }
}