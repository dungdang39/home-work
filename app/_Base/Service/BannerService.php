<?php

namespace App\Base\Service;

use App\Base\Model\Admin\Banner;
use Core\Database\Db;
use Core\FileService;
use DI\Container;
use Exception;
use Slim\Http\ServerRequest as Request;

class BannerService
{
    public const TABLE_NAME = 'banner';
    public const DIRECTORY = 'banner';
    public const MAX_WIDTH = 750;

    private string $table;
    private Container $container;
    private FileService $file_service;

    public function __construct(
        Container $container,
        FileService $file_service
    ) {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
        $this->container = $container;
        $this->file_service = $file_service;
    }

    /**
     * 배너 목록정보 가져오기
     */
    public function getBanners(array $params): array
    {
        $banners = $this->fetchBanners($params);
        if (empty($banners)) {
            return [];
        }
        foreach ($banners as &$banner) {
            $banner['is_within_date'] = $this->isWithinDate($banner['bn_start_datetime'], $banner['bn_end_datetime']);
        }

        return $banners;
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

    /**
     * 배너가 현재 날짜에 포함되는지 확인
     * @param string $start_date  시작일
     * @param string $end_date  종료일
     * @return bool
     */
    public function isWithinDate(?string $start_date, ?string $end_date): bool
    {
        if (empty($start_date) && empty($end_date)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        return $start_date <= $now || $now <= $end_date;
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 포지션별 배너 개수 조회
     * @param int $bn_position  배너 포지션
     * @return int
     */
    public function fetchBannersCountByPosition(int $bn_position): int
    {
        $query = "SELECT COUNT(*) FROM {$this->table} WHERE bn_position = :bn_position";
        return Db::getInstance()->run($query, ["bn_position" => $bn_position])->fetchColumn();
    }

    /**
     * 배너 목록 조회
     * 
     * @param array $search_params  검색 조건
     * @return array|false
     */
    public function fetchBanners(array $params)
    {
        $wheres = [];
        $values = [];

        $mainpage_table = $this->container->get(MainPageService::class)->table;

        if (!empty($params['keyword'])) {
            $wheres[] = " bn_title LIKE :keyword";
            $values['keyword'] = "%{$params['keyword']}%";
        }
        if (isset($params['bn_is_enabled'])) {
            $wheres[] = " bn_is_enabled = :bn_is_enabled";
            $values["bn_is_enabled"] = $params['bn_is_enabled'];
        }
        if (!empty($params['bn_position'])) {
            $wheres[] = " bn_position = :bn_position";
            $values["bn_position"] = $params['bn_position'];
        }
        $sql_where = Db::buildWhere($wheres);

        $sql_limit = '';
        if (isset($params['offset']) && isset($params['limit'])) {
            $values["offset"] = $params['offset'];
            $values["limit"] = $params['limit'];
            $sql_limit = "LIMIT :offset, :limit";
        }

        $query = "SELECT banner.*, main.id as main_id, main.section_title
                    FROM {$this->table} banner
                    LEFT JOIN {$mainpage_table} main ON main.id = banner.bn_position
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
