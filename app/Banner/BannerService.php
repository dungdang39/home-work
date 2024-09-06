<?php

namespace App\Banner;

use Core\Database\Db;
use Core\Lib\UriHelper;
use Psr\Http\Message\ServerRequestInterface as Request;

class BannerService
{
    public const DIRECTORY = 'banner';
    public const PERMISSION = 0755;

    private string $table;
    private UriHelper $uri_helper;

    public function __construct(UriHelper $uri_helper)
    {
        $this->table = $_ENV['DB_PREFIX'] . 'banner';
        $this->uri_helper = $uri_helper;
    }

    /**
     * 배너 목록정보 가져오기
     */
    public function getGroupedBanners(array $params): array
    {
        $banners = $this->fetchBanners($params); // , $page_params

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
    public function getBanner(int $bn_id): array
    {
        $banner = $this->fetchBanner($bn_id);

        if (empty($banner)) {
            throw new \Exception('배너 정보를 찾을 수 없습니다.');
        }

        return $banner;
    }

    /**
     * 배너 디렉토리 생성
     * 
     * @param Request $request
     * @return void
     */
    public function makeBannerDir(Request $request): void
    {
        $banner_path = $this->getBannerPath($request);
        if (file_exists($banner_path)) {
            return;
        }

        @mkdir($banner_path, self::PERMISSION);
        @chmod($banner_path, self::PERMISSION);
    }

    /**
     * 배너 이미지 업로드
     * 
     * @param Request $request  요청 객체
     * @param object $data  배너 데이터
     * @return void
     */
    public function uploadImage(Request $request, object &$data)
    {
        $banner_path = $this->getBannerPath($request);

        if ($data->image_file->getSize() > 0) {
            $data->bn_image = moveUploadedFile($banner_path, $data->image_file);
        }
        if ($data->mobile_image_file->getSize() > 0) {
            $data->bn_mobile_image = moveUploadedFile($banner_path, $data->mobile_image_file);
        }

        // 파일 필드 제거
        unset($data->image_file);
        unset($data->mobile_image_file);
    }

    /**
     * 배너 이미지 경로 가져오기
     * 
     * @param Request $request
     * @return string
     */
    public function getBannerPath(Request $request): string
    {
        $base_path = $this->uri_helper->getBasePath($request);
        // @TODO: data 경로도 상수로 빼야함.
        return $base_path . "/data/" . self::DIRECTORY;
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
        $sql_where = "1";
        if (!empty($params['bn_position'])) {
            $sql_where .= " AND bn_position = :bn_position";
        }

        $query = "SELECT * FROM {$this->table} WHERE {$sql_where} ORDER BY bn_order, created_at DESC";

        // $params = array_merge($params, $page_params);

        return Db::getInstance()->run($query, $params)->fetchAll();
    }

    /**
     * 배너 조회
     * 
     * @param int $bn_id  배너 ID
     * @return array|false
     */
    public function fetchBanner(int $bn_id)
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
