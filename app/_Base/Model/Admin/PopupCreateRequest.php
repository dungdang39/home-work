<?php

namespace App\Base\Model\Admin;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

class PopupCreateRequest
{
    use SchemaHelperTrait;

    public ?bool $pu_is_enabled = false;
    public ?int $pu_disable_hours;
    public ?string $pu_begin_time;
    public ?string $pu_end_time;
    public ?int $pu_left = 0;
    public ?int $pu_top = 0;
    public ?int $pu_width = 0;
    public ?int $pu_height = 0;
    public ?string $pu_title = '';
    public ?string $pu_content = '';
    public ?string $pu_mobile_content;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    public function validate(): void
    {
        $this->validateRequired('pu_title', '제목');
        $this->validateRequired('pu_content', '내용');
        if (!Validator::isNotEmptyAndNumeric($this->pu_disable_hours)) {            
            $this->throwException("유효한 시간 값을 입력하세요.");
        }
    }
}
