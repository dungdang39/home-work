<?php

namespace App\Admin\Model;

use Core\Traits\SchemaHelperTrait;


class NotificationRequest
{
    use SchemaHelperTrait;

    public function __construct(array $data)
    {
        $this->mapDataToProperties($this, $data);
        $this->validate();
    }

    private function validate()
    {
    }
}