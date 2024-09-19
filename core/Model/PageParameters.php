<?php

namespace Core\Model;

use Core\Traits\SchemaHelperTrait;
use Slim\Http\ServerRequest as Request;

class PageParameters
{
    use SchemaHelperTrait;

    public const DEFAULT_LIMIT = 15;

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
     * 시작 위치
     */
    public int $offset = 0;

    /**
     * 총 페이지 수
     */
    public int $total_page = 0;

    /**
     * 총 데이터 수
     */
    public int $total_count = 0;

    /**
     * @param array $data 요청 데이터
     * @return void
     */
    public function __construct(Request $request) 
    {
        $this->mapDataToProperties($this, $request->getQueryParams());

        // limit 및 offset 설정
        $this->setLimit(self::DEFAULT_LIMIT);
    }

    /**
     * 페이지 정보 반환
     * @return array
     */
    public function getPaginationInfo(): array
    {
        return [
            "page" => $this->page,
            "limit" => $this->limit,
            "offset" => $this->offset,
            "total_page" => $this->total_page,
            "total_count" => $this->total_count,
        ];
    }

    /**
     * 총 데이터 수 설정
     * @param int $total_count 총 데이터 수
     * @return void
     */
    public function setTotalCount(int $total_count): void
    {
        $this->total_count = $total_count;
        $this->setTotalPage();
    }

    /**
     * limit 값 설정
     * @param int $limit 기본 페이지당 결과 수
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;

        // limit 값이 없을 경우 기본값 설정
        if ($this->limit <= 0) {
            $this->limit = self::DEFAULT_LIMIT;
        }

        // limit 값이 100을 초과하지 않도록 제한
        if ($this->limit > 100) {
            $this->limit = 100;
        }

        $this->setOffset();
    }

    /**
     * 총 페이지 수 설정
     * @return void
     */
    private function setTotalPage(): void
    {
        $this->total_page = ceil($this->total_count / $this->limit);
    }

    /**
     * offset 값 설정
     * - 검색 시작 위치 설정
     */
    private function setOffset(): void
    {
        if ($this->page <= 0) {
            $this->page = 1;
        }
        $this->offset = ($this->page - 1) * $this->limit;
    }
}
