<?php

namespace App\Base\Service;

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
        if (empty($popups)) {
            return [];
        }

        foreach ($popups as &$popup) {
            $popup['is_within_date'] = $this->isWithinDate($popup['pu_begin_time'], $popup['pu_end_time']);
        }

        return $popups;
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

    /**
     * 팝업이 현재 날짜에 포함되는지 확인
     * @todo 동일한 코드가 배너에도 있으므로 개선이 필요함.
     * @param string $start_date  시작일
     * @param string $end_date  종료일
     * @return bool
     */
    public function isWithinDate(?string $start_date, ?string $end_date): bool
    {
        if (empty($start_date) && empty($end_date)) {
            return true;
        }
        $now = date('Y-m-d H:i:s');
        if (empty($start_date)) {
            return $now <= $end_date;
        }
        if (empty($end_date)) {
            return $start_date <= $now;
        }

        return $start_date <= $now && $now <= $end_date;
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
        $this->addSearchConditions($wheres, $values, $params);
        $sql_where = Db::buildWhere($wheres);

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
        $this->addSearchConditions($wheres, $values, $params);
        $sql_where = Db::buildWhere($wheres);
        
        $sql_limit = "";
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

    /**
     * 목록 검색 조건 추가
     * @param array $params 검색 조건
     * @param array $wheres WHERE 절
     * @param array $values 바인딩 값
     * @return void
     */
    private function addSearchConditions(?array &$wheres = [], ?array &$values = [], ?array $params = [])
    {
        if (isset($params['keyword'])) {
            $wheres[] = "(pu_title LIKE :keyword1 OR pu_content LIKE :keyword2)";
            $values["keyword1"] = "%{$params['keyword']}%";
            $values["keyword2"] = "%{$params['keyword']}%";
        }
        if (isset($params['position'])) {
            // $wheres[] = "position = :position";
            // $values["position"] = $params['position'];
        }
        if (isset($params['pu_is_enabled'])) {
            $wheres[] = " pu_is_enabled = :pu_is_enabled";
            $values["pu_is_enabled"] = $params['pu_is_enabled'];
        }
    }

    // ========================================
    // Getters and Setters
    // ========================================
}
