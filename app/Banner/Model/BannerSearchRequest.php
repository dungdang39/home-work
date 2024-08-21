<?php

namespace App\Banner\Model;

use Core\Traits\SchemaHelperTrait;

class BannerSearchRequest
{
    use SchemaHelperTrait;

    public ?string $bn_position;

    public function __construct(array $query_params = [])
    {
        $this->mapDataToProperties($this, $query_params);
    }

    /**
     * 객체를 배열로 변환
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
