<?php

namespace Core\Model;

use Core\Traits\SchemaHelperTrait;

class PageParameters
{
    use SchemaHelperTrait;

    /**
     * 페이지 번호
     * @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", minimum=1, default=1))
     */
    public int $page = 1;

    /**
     * 한 페이지에 표시할 데이터 수 (0이면 설정된 기본값을 사용)
     * @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer", minimum=0, maximum=100, default=0))
     */
    public int $limit = 0;

    /**
     * 모바일 여부
     * @OA\Parameter(name="is_mobile", in="query", @OA\Schema(type="boolean", default=false))
     */
    public bool $is_mobile = false;

    /**
     * 시작 위치
     */
    public int $offset = 0;

    /**
     * @param array $data 요청 데이터
     * @param int $limit 기본 페이지당 결과 수
     * @param int $mobile_limit 모바일에서 기본 페이지당 결과 수
     */
    public function __construct(array $data, int $limit = 15, int $mobile_limit = 10)
    {
        $this->mapDataToProperties($this, $data);

        // limit과 offset 설정
        $this->setLimit($limit, $mobile_limit);
        $this->setOffset();
    }

    /**
     * limit 값 설정
     * @param int $limit 기본 페이지당 결과 수
     * @param int $mobile_limit 모바일에서 기본 페이지당 결과 수
     */
    private function setLimit(int $limit, int $mobile_limit): void
    {
        // limit값이 없을 경우 기본값 설정
        $this->limit = ($this->limit <= 0)
            ? ($this->is_mobile ? $mobile_limit : $limit)
            : $this->limit;

        // limit 값이 100을 초과하지 않도록 제한
        if ($this->limit > 100) {
            $this->limit = 100;
        }
    }

    /**
     * offset 값 설정
     */
    private function setOffset(): void
    {
        // 시작 위치 초기화
        $this->offset = ($this->page - 1) * $this->limit;
    }
}
