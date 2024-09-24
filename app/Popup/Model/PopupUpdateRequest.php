<?php

namespace App\Popup\Model;

use Core\Traits\SchemaHelperTrait;
use Core\Validator\Validator;
use Slim\Http\ServerRequest as Request;

class PopupUpdateRequest
{
    use SchemaHelperTrait;

    public ?string $pu_division;
    public ?int $pu_disable_hours;
    public ?string $pu_begin_time;
    public ?string $pu_end_time;
    public ?int $pu_left = 0;
    public ?int $pu_top = 0;
    public ?int $pu_width = 0;
    public ?int $pu_height = 0;
    public ?string $pu_title;
    public ?string $pu_content;
    public ?string $pu_mobile_content;

    public function __construct(Request $request)
    {
        $this->initializeFromRequest($request);
    }

    public function validate(): void
    {
        // 필수 값 검증
        if (!Validator::required($this->pu_title)) {
            $this->throwException("제목을 입력하세요.");
        }
        if (!Validator::isNotEmptyAndNumeric($this->pu_disable_hours)) {            
            $this->throwException("유효한 시간 값을 입력하세요.");
        }
        if (!Validator::required($this->pu_begin_time)) {
            $this->throwException("시작일시를 입력하세요.");
        }
        if (!Validator::required($this->pu_end_time)) {
            $this->throwException("종료일시를 입력하세요.");
        }
        if (!is_numeric($this->pu_left)) {
            $this->throwException("유효한 좌측 위치 값을 입력하세요.");
        }
        if (!is_numeric($this->pu_top)) {
            $this->throwException("유효한 상단 위치 값을 입력하세요.");
        }
        if (!Validator::isNotEmptyAndNumeric($this->pu_width)) {
            $this->throwException("유효한 넓이 값을 입력하세요.");
        }
        if (!Validator::isNotEmptyAndNumeric($this->pu_height)) {
            $this->throwException("유효한 높이 값을 입력하세요.");
        }
    }
}
