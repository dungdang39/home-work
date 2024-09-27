<?php

namespace App\Banner\Model;

use Core\Model\Model;
use Core\Traits\SchemaHelperTrait;

/**
 * @property int $bn_id
 * @property string $bn_title
 * @property string $bn_image
 * @property string $bn_mobile_image
 * @property string $bn_alt
 * @property string $bn_url
 * @property string $bn_position
 * @property string $bn_target
 * @property string $bn_start_datetime
 * @property string $bn_end_datetime
 * @property int bn_order
 * @property int bn_is_enabled
 * @property int bn_hit
 * @property string created_at
 * @property string updated_at
 */
class Banner extends Model
{
    use SchemaHelperTrait;

    protected string $table = 'banner';
}
