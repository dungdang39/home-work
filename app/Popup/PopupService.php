<?php

namespace App\Popup;

use Core\Database\Db;

class PopupService
{
    private string $table;

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . 'popup';
    }

    /**
     * 팝업 목록정보 가져오기
     * @param array $params  검색 조건
     * @return array
     */
    public function getPopups(array $params): array
    {
        $popups = $this->fetchPopups($params);

        return $popups ?: [];
    }

    /**
     * 팝업 정보 가져오기
     */
    public function getPopup(int $pu_id): array
    {
        $popup = $this->fetchPopup($pu_id);

        if (empty($popup)) {
            throw new \Exception('팝업 정보를 찾을 수 없습니다.');
        }

        return $popup;
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 팝업 목록 조회
     * 
     * @param array $params  검색 조건
     * @return array|false
     */
    public function fetchPopups(array $params)
    {
        $sql_where = "1";
        $values = [
            "offset" => $params['offset'],
            "limit" => $params['limit'],
        ];

        if (!empty($params['pu_device'])) {
            $sql_where .= " AND pu_device = :pu_device";
            $values['pu_device'] = $params['pu_device'];
        }

        $query = "SELECT *
                    FROM {$this->table}
                    WHERE {$sql_where}
                    ORDER BY pu_created_at DESC
                    LIMIT :offset, :limit";

        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * 팝업 조회
     * 
     * @param int $pu_id  팝업 ID
     * @return array|false
     */
    public function fetchPopup(int $pu_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE pu_id = :pu_id";
        return Db::getInstance()->run($query, ["pu_id" => $pu_id])->fetch();
    }

    /**
     * 팝업 추가
     * 
     * @param array $data  추가할 데이터
     * @return int  추가된 행의 ID
     */
    public function insert(array $data): int
    {
        return Db::getInstance()->insert($this->table, $data);
    }

    /**
     * 팝업 수정
     * 
     * @param int $pu_id  팝업 ID
     * @param array $data  수정할 데이터
     * @return int  수정된 행 수
     */
    public function update(int $pu_id, array $data): int
    {
        return Db::getInstance()->update($this->table, $data, ["pu_id" => $pu_id]);
    }

    /**
     * 팝업 삭제
     * 
     * @param int $pu_id  팝업 ID
     * @return int  삭제된 행 수
     */
    public function delete(int $pu_id): int
    {
        return Db::getInstance()->delete($this->table, ["pu_id" => $pu_id]);
    }

    // ========================================
    // Getters and Setters
    // ========================================
}