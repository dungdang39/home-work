<?php

namespace App\Base\Service;

use App\Base\Model\Admin\Banner;
use Core\Database\Db;
use Core\FileService;
use Exception;
use Slim\Http\ServerRequest as Request;

class BannerService
{
    public const DIRECTORY = 'banner';
    public const MAX_WIDTH = 750;

    private string $table;
    private FileService $file_service;

    public function __construct(
        FileService $file_service,
    ) {
        $this->table = $_ENV['DB_PREFIX'] . 'banner';
        $this->file_service = $file_service;
    }

    /**
     * 배너 목록정보 가져오기
     */
    public function getGroupedBanners(array $params): array
    {
        $banners = $this->fetchBanners($params);

        // bn_position으로 그룹핑
        $grouped_banners = [];
        foreach ($banners as $banner) {
            $grouped_banners[$banner['bn_position']][] = $banner;
        }

        return $grouped_banners;
    }

    /**
     * 배너 정보 가져오기
     */
    public function getBanner(int $bn_id): Banner
    {
        $banner = $this->fetch($bn_id);

        if (empty($banner)) {
            throw new Exception('배너 정보를 찾을 수 없습니다.', 404);
        }

        return Banner::create($banner);
    }

    /**
     * 배너 삭제
     * @param Request $request
     * @param Banner $banner
     * @return void
     */
    public function deleteBanner(Request $request, Banner $banner): void
    {
        $this->file_service->deleteByDb($request, $banner->bn_image);
        $this->file_service->deleteByDb($request, $banner->bn_mobile_image);

        $this->delete($banner->bn_id);
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 배너 목록 조회
     * 
     * @param array $search_params  검색 조건
     * @param array $page_params  페이지 정보
     * @return array|false
     */
    public function fetchBanners(array $params) // , array $page_params
    {
        $wheres = [];
        $values = [];
        $sql_where = "";
        $sql_limit = "";

        if (!empty($params['bn_position'])) {
            $wheres[] = " bn_position = :bn_position";
            $values["bn_position"] = $params['bn_position'];
        }
        $sql_where = $wheres ? 'WHERE ' . implode(' AND ', $wheres) : '';

        if (isset($params['offset']) && isset($params['limit'])) {
            $values["offset"] = $params['offset'];
            $values["limit"] = $params['limit'];
            $sql_limit = "LIMIT :offset, :limit";
        }

        $query = "SELECT *
                    FROM {$this->table}
                    {$sql_where}
                    ORDER BY bn_order, created_at DESC
                    {$sql_limit}";

        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * 배너 조회
     * 
     * @param int $bn_id  배너 ID
     * @return array|false
     */
    public function fetch(int $bn_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE bn_id = :bn_id";
        return Db::getInstance()->run($query, ["bn_id" => $bn_id])->fetch();
    }

    /**
     * 배너 추가
     * 
     * @param array $data  추가할 데이터
     * @return int  추가된 행의 ID
     */
    public function insert(array $data): int
    {
        return Db::getInstance()->insert($this->table, $data);
    }

    /**
     * 배너 수정
     * 
     * @param int $bn_id  배너 ID
     * @param array $data  수정할 데이터
     * @return int  수정된 행 수
     */
    public function update(int $bn_id, array $data): int
    {
        return Db::getInstance()->update($this->table, $data, ["bn_id" => $bn_id]);
    }

    /**
     * 배너 삭제
     * 
     * @param int $bn_id  배너 ID
     * @return int  삭제된 행 수
     */
    public function delete(int $bn_id): int
    {
        return Db::getInstance()->delete($this->table, ["bn_id" => $bn_id]);
    }

    // ========================================
    // Getters and Setters
    // ========================================
}
