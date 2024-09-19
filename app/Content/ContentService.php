<?php

namespace App\Content;

use Core\Database\Db;
use Core\Lib\UriHelper;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;

class ContentService
{
    public const DIRECTORY = 'content';
    public const PERMISSION = 0755;

    private string $table;
    private UriHelper $uri_helper;

    public function __construct(UriHelper $uri_helper)
    {
        $this->table = $_ENV['DB_PREFIX'] . 'content';
        $this->uri_helper = $uri_helper;
    }

    /**
     * 컨텐츠 목록정보 가져오기
     * @param array|null $params  검색 조건
     * @return array
     */
    public function getContents(array $params = []): array
    {
        $contents = $this->fetchContents($params);

        return $contents ?: [];
    }

    /**
     * 컨텐츠 정보 가져오기
     */
    public function getContent(string $code): array
    {
        $content = $this->fetchContent($code);

        if (empty($content)) {
            throw new Exception('컨텐츠 정보를 찾을 수 없습니다.', 404);
        }

        return $content;
    }

    /**
     * 컨텐츠 이미지 경로 가져오기
     * 
     * @param Request $request
     * @return string
     */
    public function getContentPath(Request $request): string
    {
        $base_path = $this->uri_helper->getBasePath($request);
        // @TODO: data 경로도 상수로 빼야함.
        return $base_path . "/data/" . self::DIRECTORY;
    }

    /**
     * 컨텐츠 디렉토리 생성
     * 
     * @param Request $request
     * @return void
     */
    public function makeContentDir(Request $request): void
    {
        $content_path = $this->getContentPath($request);
        if (file_exists($content_path)) {
            return;
        }

        @mkdir($content_path, self::PERMISSION);
        @chmod($content_path, self::PERMISSION);
    }

    /**
     * 컨텐츠 이미지 업로드
     * 
     * @param Request $request  요청 객체
     * @param object $data  컨텐츠 데이터
     * @return void
     */
    public function uploadImage(Request $request, object &$data)
    {
        $content_path = $this->getContentPath($request);
        if ($data->head_image_file->getSize() > 0) {
            $data->head_image = moveUploadedFile($content_path, $data->head_image_file);
        }
        if ($data->foot_image_file->getSize() > 0) {
            $data->foot_image = moveUploadedFile($content_path, $data->foot_image_file);
        }
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 컨텐츠 총 개수 조회
     * @param array $params  검색 조건
     * @return int
     */
    public function fetchContentsTotalCount(array $params = []): int
    {
        $wheres = [];
        $values = [];
        $sql_where = $wheres ? "WHERE " . implode(' AND ', $wheres) : "";

        $query = "SELECT COUNT(*) FROM {$this->table} {$sql_where}";
        return Db::getInstance()->run($query, $values)->fetchColumn();
    }

    /**
     * 컨텐츠 목록 조회
     * 
     * @param array $params  검색 조건
     * @return array|false
     */
    public function fetchContents(array $params = [])
    {
        $wheres = [];
        $values = [];
        $sql_where = $wheres ? "WHERE " . implode(' AND ', $wheres) : "";

        $sql_limit = "";
        if (isset($params['offset']) && isset($params['limit'])) {
            $values["offset"] = $params['offset'];
            $values["limit"] = $params['limit'];
            $sql_limit = "LIMIT :offset, :limit";
        }

        $query = "SELECT * FROM {$this->table}
                    {$sql_where}
                    ORDER BY created_at DESC
                    {$sql_limit}";

        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * 컨텐츠 조회
     * 
     * @param string $code  컨텐츠 코드
     * @return array|false
     */
    public function fetchContent(string $code)
    {
        $query = "SELECT * FROM {$this->table} WHERE code = :code";
        return Db::getInstance()->run($query, ["code" => $code])->fetch();
    }

    /**
     * 컨텐츠 추가
     * 
     * @param array $data  추가할 데이터
     * @return int  추가된 행의 ID
     */
    public function insert(array $data): int
    {
        return Db::getInstance()->insert($this->table, $data);
    }

    /**
     * 컨텐츠 수정
     * 
     * @param string $code  컨텐츠 코드
     * @param array $data  수정할 데이터
     * @return int  수정된 행 수
     */
    public function update(string $code, array $data): int
    {
        return Db::getInstance()->update($this->table, $data, ["code" => $code]);
    }

    /**
     * 컨텐츠 삭제
     * 
     * @param string $code  컨텐츠 코드
     * @return int  삭제된 행 수
     */
    public function delete(string $code): int
    {
        return Db::getInstance()->delete($this->table, ["code" => $code]);
    }

    // ========================================
    // Getters and Setters
    // ========================================
}
