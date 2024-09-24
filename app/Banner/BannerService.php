<?php

namespace App\Banner;

use Core\Database\Db;
use Core\FileService;
use Core\Lib\UriHelper;
use Exception;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\ImageManagerStatic as Image;
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
    public function getBanner(int $bn_id): array
    {
        $banner = $this->fetchBanner($bn_id);

        if (empty($banner)) {
            throw new Exception('배너 정보를 찾을 수 없습니다.', 404);
        }

        return $banner;
    }

    /**
     * 이미지 너비 가져오기
     * @todo 이미지 처리하는 클래스로 이동
     * @param Request $request
     * @param string|null $path
     * @return int
     */
    public function getImageWidth(Request $request, ?string $path = null): int
    {
        if (empty($path)) {
            return 0;
        }

        $base_path = UriHelper::getBasePath($request);
        try {
            return Image::make($base_path . $path)->width();
        } catch (NotReadableException $e) {
            return 0;
        }
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
        $directory = $this->getUploadDir($request);

        $data->bn_image = $this->file_service->uploadFile($directory, $data->image_file);
        $data->bn_mobile_image = $this->file_service->uploadFile($directory, $data->mobile_image_file);
        
        $data->bn_image = $this->getRelativeFilePath($data->bn_image);
        $data->bn_mobile_image = $this->getRelativeFilePath($data->bn_mobile_image);

        // 파일 필드 제거
        unset($data->image_file);
        unset($data->mobile_image_file);
    }

    /**
     * 배너 업로드 디렉토리 가져오기
     */
    public function getUploadDir(Request $request): string
    {
        return FileService::getUploadDir($request) . '/' . self::DIRECTORY;
    }


    /**
     * 배너 업로드 상대 경로 가져오기
     */
    public function getRelativeFilePath(?string $filename = null): string
    {
        if (empty($filename)) {
            return '';
        }

        return implode('/', [FileService::getRelativePath(), self::DIRECTORY, $filename]);
    }

    /**
     * 배너 이미지 삭제
     * @param Request $request
     * @param array $banner
     * @return void
     */
    public function deleteImage(Request $request, string $path): void
    {
        $this->file_service->deleteFile(UriHelper::getBasePath($request) . $path);
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
        $sql_where = $wheres ? "WHERE " . implode(' AND ', $wheres) : "";

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
