<?php

namespace App\Popup;

use Core\Database\Db;
use Exception;

class PopupService
{
    public const TABLE_NAME = 'popup';
    private string $table;

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
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
        $popup = $this->fetch($pu_id);

        if (empty($popup)) {
            throw new Exception('팝업 정보를 찾을 수 없습니다.', 404);
        }

        return $popup;
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 활성화된 팝업 가져오기
     * @param string $division  구분 (both, community, shop)
     * @param string $except_ids  제외할 팝업 ID (쉼표로 구분)
     * @return mixed
     */
    public function fetchActivePopups(string $division = '', string $except_ids = '')
    {
        $sql_where = '';
        if ($except_ids) {
            $sql_where = "AND pu_id NOT IN ({$except_ids})";
        }

        $query = "SELECT *
                    FROM {$this->table}
                    WHERE pu_division IN ('both', :pu_division) 
                        AND pu_begin_time <= NOW()
                        AND pu_end_time >= NOW()
                        {$sql_where}
                    ORDER BY pu_id DESC";

        return Db::getInstance()->run($query, ['pu_division' => $division])->fetchAll();
    }

    /**
     * 팝업 개수 조회
     * 
     * @param array $params  검색 조건
     * @return int
     */
    public function fetchPopupsCount(array $params): int
    {
        $wheres = [];
        $values = [];
        $sql_where = "";

        if (!empty($params['pu_device'])) {
            $sql_where .= 'pu_device = :pu_device';
            $values['pu_device'] = $params['pu_device'];
        }
        $sql_where = $wheres ? 'WHERE ' . implode(' AND ', $wheres) : '';

        $query = "SELECT COUNT(*)
                    FROM {$this->table}
                    {$sql_where}";

        return Db::getInstance()->run($query, $values)->fetchColumn();
    }

    /**
     * 팝업 목록 조회
     * 
     * @param array $params  검색 조건
     * @return array|false
     */
    public function fetchPopups(array $params)
    {
        $wheres = [];
        $values = [];
        $sql_where = "";
        $sql_limit = "";

        if (!empty($params['pu_device'])) {
            $sql_where .= "pu_device = :pu_device";
            $values['pu_device'] = $params['pu_device'];
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
                    ORDER BY created_at DESC
                    {$sql_limit}";

        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * 팝업 조회
     * 
     * @param int $pu_id  팝업 ID
     * @return array|false
     */
    public function fetch(int $pu_id)
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
