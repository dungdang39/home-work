<?php

namespace App\Content;

use Core\Database\Db;
use Exception;

class ContentService
{
    public string $table;

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . 'content';
    }

    /**
     * 컨텐츠 목록정보 가져오기
     * @param array $params  검색 조건
     * @return array
     */
    public function getContents(array $params): array
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

    // ========================================
    // Database Queries
    // ========================================

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

        if (isset($params['offset']) && isset($params['limit'])) {
            $values["offset"] = $params['offset'];
            $values["limit"] = $params['limit'];
            $sql_limit = "LIMIT :offset, :limit";
        }

        $where = $wheres ? "WHERE " . implode(' AND ', $wheres) : "";

        $query = "SELECT * FROM {$this->table}
                    {$where}
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
