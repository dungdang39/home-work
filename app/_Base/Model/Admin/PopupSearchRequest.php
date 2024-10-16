<?php

namespace App\Base\Model\Admin;

use Core\Model\PaginationRequest;

class PopupSearchRequest extends PaginationRequest
{
    public ?string $pu_device;
}
