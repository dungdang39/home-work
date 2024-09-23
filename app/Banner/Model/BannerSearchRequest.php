<?php

namespace App\Banner\Model;

use Core\Model\PaginationRequest;

class BannerSearchRequest extends PaginationRequest
{
    public ?string $bn_position;
}