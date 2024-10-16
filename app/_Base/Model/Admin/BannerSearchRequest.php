<?php

namespace App\Base\Model\Admin;

use Core\Model\PaginationRequest;

class BannerSearchRequest extends PaginationRequest
{
    public ?string $bn_position;
}