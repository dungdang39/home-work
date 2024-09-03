<?php

namespace App\Popup\Model;

use Core\Traits\SchemaHelperTrait;

class PopupCreateRequest
{
    use SchemaHelperTrait;

    public ?string $pu_division;
    public ?int $pu_disable_hours;
    public ?string $pu_begin_time;
    public ?string $pu_end_time;
    public ?int $pu_left;
    public ?int $pu_top;
    public ?int $pu_width;
    public ?int $pu_height;
    public ?string $pu_title;
    public ?string $pu_content;
    public ?string $pu_mobile_content;

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }

    public function validate(): void
    {
        // 필수 값 검증
        if (empty($this->pu_title)) {
            throw new \InvalidArgumentException("제목을 입력하세요.");
        }

        if (empty($this->pu_disable_hours) || !is_numeric($this->pu_disable_hours)) {
            throw new \InvalidArgumentException("유효한 시간 값을 입력하세요.");
        }

        if (empty($this->pu_begin_time)) {
            throw new \InvalidArgumentException("시작일시를 입력하세요.");
        }

        if (empty($this->pu_end_time)) {
            throw new \InvalidArgumentException("종료일시를 입력하세요.");
        }

        // 좌표와 크기 값 검증
        if (empty($this->pu_left) || !is_numeric($this->pu_left)) {
            throw new \InvalidArgumentException("유효한 좌측 위치 값을 입력하세요.");
        }

        if (empty($this->pu_top) || !is_numeric($this->pu_top)) {
            throw new \InvalidArgumentException("유효한 상단 위치 값을 입력하세요.");
        }

        if (empty($this->pu_width) || !is_numeric($this->pu_width)) {
            throw new \InvalidArgumentException("유효한 넓이 값을 입력하세요.");
        }

        if (empty($this->pu_height) || !is_numeric($this->pu_height)) {
            throw new \InvalidArgumentException("유효한 높이 값을 입력하세요.");
        }
    }
}
