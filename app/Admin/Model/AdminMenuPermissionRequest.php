<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;

class AdminMenuPermissionRequest
{
    use SchemaHelperTrait;

    public ?int $read = 0;
    public ?int $write = 0;
    public ?int $delete = 0;

    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);
    }
}