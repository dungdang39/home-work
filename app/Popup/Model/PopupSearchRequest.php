<?php

namespace App\Popup\Model;

use Core\Model\PaginationRequest;

class PopupSearchRequest extends PaginationRequest
{
    public ?string $pu_device;
}
