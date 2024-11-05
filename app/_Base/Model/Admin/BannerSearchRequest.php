<?php

namespace App\Base\Model\Admin;

use Core\Model\PaginationRequest;

class BannerSearchRequest extends PaginationRequest
{
    public ?string $keyword;

    public ?int $bn_position;

    public ?bool $bn_is_enabled;
}