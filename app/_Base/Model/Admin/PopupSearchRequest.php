<?php

namespace App\Base\Model\Admin;

use Core\Model\PaginationRequest;

class PopupSearchRequest extends PaginationRequest
{
    public ?string $keyword;

    public ?bool $pu_is_enabled;

    public ?string $position;
}
