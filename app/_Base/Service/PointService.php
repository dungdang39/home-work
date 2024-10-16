<?php

namespace App\Base\Service;

use Core\Database\Db;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\ServerRequest as Request;

class PointService
{
    public const TABLE_NAME = 'point';
    public string $table;

    private bool $use_point = true;
    private int $point_term = 0;
    private MemberService $member_service;
    private Request $request;

    public function __construct(
        MemberService $member_service,
        MemberConfigService $member_config_service,
        Request $request
    ) {
        $member_config = $member_config_service->getMemberConfig();
        $this->use_point = (bool)$member_config['use_point'];
        $this->point_term = (int)$member_config['point_term'];

        $this->member_service = $member_service;
        $this->request = $request;

        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
    }

    // ========================================
    // Public Methods
    // ========================================

    /**
     * 포인트 내역 조회
     * @param int $po_id 포인트 아이디
     * @return array
     * @throws HttpNotFoundException
     */
    public function getPoint(int $po_id): array
    {
        $point = $this->fetch($po_id);

        if (!$point) {
            throw new HttpNotFoundException($this->request, '포인트 내역이 존재하지 않습니다.');
        }

        return $point;
    }

    /**
     * 포인트 목록정보 조회
     * @param array $params 조회조건
     * @return array
     */
    public function getPoints(array $params): array
    {
        $points = $this->fetchPoints($params);

        if (!$points) {
            return [];
        }

        return $points;
    }

    /**
     * 포인트 합계를 지급/차감 별로 계산
     * @param array $points
     * @return array
     */
    public function calculate_sum(array $points): array
    {
        $sum = ['negative' => 0, 'positive' => 0];

        foreach ($points as $point) {
            if ($point['po_point'] < 0) {
                $sum['negative'] += $point['po_point'];
            } else {
                $sum['positive'] += $point['po_point'];
            }
        }

        return $sum;
    }

    /**
     * 포인트 지급
     * - common.lib.php > insert_point() 함수 참고
     * @param string $mb_id 회원 아이디
     * @param int $point 지급할 포인트
     * @param string $content 내용
     * @param string $rel_table 관련 테이블
     * @param string $rel_id 관련 아이디
     * @param string $rel_action 관련 액션
     * @param int $expire 만료일
     * @return bool
     */
    public function addPoint(
        string $mb_id,
        int $point,
        string $content = '',
        string $rel_table = '',
        string $rel_id = '',
        string $rel_action = '',
        int $expire = 0
    ): bool {
        if (
            !$this->use_point
            || $point === 0
            || $mb_id === ''
        ) {
            return false;
        }
        if (!$mb_id) {
            return false;
        }
        $member = $this->member_service->fetchMemberById($mb_id);
        if (!$member) {
            return false;
        }
        if ($rel_table || $rel_id || $rel_action) {
            $exists_relation = $this->fetchPointByRelation($mb_id, $rel_table, $rel_id, $rel_action);
            if ($exists_relation) {
                return false;
            }
        }

        $po_expire_date = '9999-12-31';
        if ($this->point_term > 0) {
            if ($expire > 0) {
                $po_expire_date = date('Y-m-d', strtotime('+' . ($expire - 1) . ' days', time())); // G5_SERVER_TIME
            } else {
                $po_expire_date = date('Y-m-d', strtotime('+' . ($this->point_term - 1) . ' days', time())); // G5_SERVER_TIME
            }
        }

        $po_expired = 0;
        if ($point < 0) {
            $po_expired = 1;
            $po_expire_date = date('Y-m-d'); // G5_TIME_YMD
        }

        $mb_point = $this->calculateMemberPointSum($mb_id);
        $po_mb_point = $mb_point + $point;

        $data = [
            'mb_id' => $mb_id,
            'po_content' => addslashes($content),
            'po_point' => $point,
            'po_mb_point' => $po_mb_point,
            'po_expired' => $po_expired,
            'po_expire_date' => $po_expire_date,
            'po_rel_table' => $rel_table,
            'po_rel_id' => $rel_id,
            'po_rel_action' => $rel_action
        ];
        $this->insert($data);

        if ($point < 0) {
            $this->calculateUsePoint($mb_id, $point);
        }

        $this->member_service->update($mb_id, ['mb_point' => $po_mb_point]);

        return true;
    }

    /**
     * 회원의 전체 포인트 계산
     * - common.lib.php > get_point_sum() 함수 참고
     * @param string $mb_id 회원 아이디
     * @return int 포인트 합계
     */
    public function calculateMemberPointSum(string $mb_id)
    {
        if ($this->point_term > 0) {
            // 소멸포인트가 있으면 내역 추가
            $expire_point = $this->fetchExpirePointSum($mb_id);
            if ($expire_point > 0) {
                $mb = $this->member_service->fetchMemberById($mb_id);
                $point = $expire_point * (-1);
                $data = [
                    'mb_id' => $mb_id,
                    'po_content' => '포인트 소멸',
                    'po_point' => $point,
                    'po_mb_point' => $mb['mb_point'] + $point,
                    'po_expired' => 1,
                    'po_expire_date' => date('Y-m-d'),
                    'po_rel_table' => '@expire',
                    'po_rel_id' => $mb_id,
                    'po_rel_action' => 'expire' . '-' . uniqid()
                ];
                $this->insert($data);

                // 240726 개선
                // - $expire_point가 양수이고 $point는 * -1 이므로
                // - insert_use_point는 무조건 실행됨
                $this->calculateUsePoint($mb_id, $point);
            }

            $this->updateExpiredPoints($mb_id);
        }

        // 포인트 합계
        return $this->fetchMemberPointSum($mb_id);
    }

    /**
     * 아직 사용하지 않은 포인트 내역에 사용할 포인트를 기록
     * - common.lib.php > insert_use_point() 함수 참고
     * @param string $mb_id 회원 아이디
     * @param int $point 사용할 포인트
     * @param string $po_id 사용포인트에서 제외할 포인트 아이디
     * @return void
     */
    public function calculateUsePoint(string $mb_id, int $point, string $po_id = '')
    {
        $use_point = abs($point);
        $points = $this->fetchUnusedPoints($mb_id, $po_id);
        foreach ($points as $row) {
            $total_point = (int)$row['po_point'];
            $used_point = (int)$row['po_use_point'];

            $available_point = $total_point - $used_point;
            if ($available_point > $use_point) {
                $this->update($row['po_id'], [
                    'po_use_point' => $used_point + $use_point
                ]);
                break;
            } else {
                $this->update($row['po_id'], [
                    'po_use_point' => $total_point,
                    'po_expired' => 100
                ]);

                $use_point -= $available_point;
            }
        }
    }

    /**
     * 포인트 1건 만료 처리
     * @param array $point 포인트 정보
     * @return bool
     */
    public function expirePoint(array $point): bool
    {
        // 1. 남은 포인트 조회
        $unused_point = (int)$point['po_point'] - (int)$point['po_use_point'];

        // 2. 남은 포인트 만큼 차감
        $this->addPoint(
            $point['mb_id'],
            $unused_point * -1,
            '포인트 만료',
            '@expire',
            $point['mb_id'],
            'expire' . '-' . uniqid(),
            0
        );

        // 3. 포인트 내역에 만료 처리
        $this->update($point['po_id'], ['po_expired' => 1]);

        // 4. 포인트 합계 업데이트
        $sum_point = $this->fetchMemberPointSum($point['mb_id']);
        $this->member_service->update($point['mb_id'], ['mb_point' => $sum_point]);

        return true;
    }

    /**
     * 포인트 삭제 처리
     * @param array $point 포인트 정보
     * @return bool
     */
    public function removePoint(array $point): bool
    {
        // 만료&사용 포인트 처리
        if ($point['po_point'] < 0) {
            if ($point['po_rel_table'] == '@expire') {
                $this->removeExpirePoint($point['mb_id'], (int)$point['po_point']);
            } else {
                $this->removeUsePoint($point['mb_id'], (int)$point['po_point']);
            }
        } elseif ($point['po_use_point'] > 0) {
            $this->calculateUsePoint($point['mb_id'], (int)$point['po_use_point'], $point['po_id']);
        }

        $result = $this->delete($point['po_id']);

        // 포인트 내역에 회원포인트 갱신
        $this->updateHistoryForMember($point);

        // 회원의 포인트 내역의 합계를 구하고 업데이트
        $sum_point = $this->calculateMemberPointSum($point['mb_id']);
        $this->member_service->update($point['mb_id'], ['mb_point' => $sum_point]);

        return $result;
    }

    /**
     * 연관 데이터로 포인트 삭제 처리
     * - common.lib.php > delete_point() 함수 참고
     * @param string $mb_id 회원 아이디
     * @param string $rel_table 관련 테이블
     * @param string $rel_id 관련 아이디
     * @param string $rel_action 관련 액션
     * @return bool
     */
    public function removePointByRelation(string $mb_id, string $rel_table, string $rel_id, string $rel_action): bool
    {
        if (!($rel_table || $rel_id || $rel_action)) {
            return false;
        }

        // 포인트 내역이 없을 경우
        $row = $this->fetchPointByRelation($mb_id, $rel_table, $rel_id, $rel_action);
        if (!(isset($row['po_id']) && $row['po_id'])) {
            return true;
        }

        // 사용 포인트 처리
        if (isset($row['po_point']) && (int)$row['po_point'] < 0) {
            $po_point = abs($row['po_point']);
            $this->removeUsePoint($mb_id, $po_point);
        } elseif (isset($row['po_use_point']) && $row['po_use_point'] > 0) {
            $this->calculateUsePoint($mb_id, $row['po_use_point'], $row['po_id']);
        }

        $result = $this->deleteByRelation($mb_id, $rel_table, $rel_id, $rel_action);

        // 포인트 내역에 회원포인트 갱신
        $this->updateHistoryForMember($row);

        // 회원의 포인트 내역의 합계를 구하고 업데이트
        $sum_point = $this->calculateMemberPointSum($mb_id);
        $this->member_service->update($mb_id, ['mb_point' => $sum_point]);

        return $result;
    }

    /**
     * 사용포인트 삭제
     * - common.lib.php > delete_use_point() 함수 참고
     * @param string $mb_id 회원 아이디
     * @param int $point 삭제할 포인트
     * @return void
     */
    public function removeUsePoint(string $mb_id, int $point)
    {
        $delete_point = abs($point);
        $points = $this->fetchNonExpiredUsedPoints($mb_id);
        foreach ($points as $row) {
            $used_point = $row['po_use_point'];
            $po_expired = $row['po_expired'];
            $po_expire_date = $row['po_expire_date'];

            if (
                $po_expired == 100
                && ($po_expire_date == '9999-12-31' || $po_expire_date >= date('Y-m-d')) // G5_TIME_YMD
            ) {
                $po_expired = 0;
            }

            if ($used_point > $delete_point) {
                $this->update($row['po_id'], [
                    'po_use_point' => 'po_use_point - ' . $delete_point,
                    'po_expired' => $po_expired
                ]);
                break;
            } else {
                $this->update($row['po_id'], [
                    'po_use_point' => '0',
                    'po_expired' => $po_expired
                ]);

                $delete_point -= $used_point;
            }
        }
    }

    /**
     * 만료된 포인트 삭제
     * - common.lib.php > delete_expire_point() 함수 참고
     * @param string $mb_id 회원 아이디
     * @param int $point 삭제할 포인트
     * @return void
     */
    public function removeExpirePoint(string $mb_id, int $point)
    {
        $delete_point = abs($point);
        $points = $this->fetchExpiredUsedPoints($mb_id);

        foreach ($points as $row) {
            $used_point = $row['po_use_point'];
            $po_expired = '0';
            $po_expire_date = '9999-12-31';

            if ($this->point_term > 0) {
                $po_expire_date = date('Y-m-d', strtotime('+' . ($this->point_term - 1) . ' days', time())); // G5_SERVER_TIME
            }

            if ($used_point > $delete_point) {
                $this->update($row['po_id'], [
                    'po_use_point' => 'po_use_point - ' . $delete_point,
                    'po_expired' => $po_expired,
                    'po_expire_date' => $po_expire_date
                ]);
                break;
            } else {
                $this->update($row['po_id'], [
                    'po_use_point' => '0',
                    'po_expired' => $po_expired,
                    'po_expire_date' => $po_expire_date
                ]);

                $delete_point -= $used_point;
            }
        }
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 포인트 내역 조회
     * @param int $po_id 포인트 아이디
     * @return array|false
     */
    public function fetch(int $po_id)
    {
        $query = "SELECT *
                    FROM {$this->table}
                    WHERE po_id = :po_id";

        return Db::getInstance()->run($query, ['po_id' => $po_id])->fetch();
    }

    /**
     * 포인트 총합 조회
     * @param array $params 조회조건
     * @return int
     */
    public function fetchTotalPoint(array $params = []): int
    {
        $wheres = [];
        $values = [];

        if (isset($params['field']) && isset($params['keyword'])) {
            $wheres[] = "{$params['field']} LIKE :keyword";
            $values["keyword"] = "%{$params['keyword']}%";
        }

        $sql_where = $wheres ? 'WHERE ' . implode(' AND ', $wheres) : '';

        $query = "SELECT SUM(po_point) 
                    FROM {$this->table}
                    {$sql_where}";

        return Db::getInstance()->run($query, $values)->fetchColumn() ?: 0;
    }

    /**
     * 포인트 목록 전체 카운트 조회
     * @param array $params 조회조건
     * @return int
     */
    public function fetchPointsCount(array $params = []): int
    {
        $wheres = [];
        $values = [];

        if (isset($params['field']) && isset($params['keyword'])) {
            $wheres[] = "{$params['field']} LIKE :keyword";
            $values["keyword"] = "%{$params['keyword']}%";
        }

        $sql_where = $wheres ? 'WHERE ' . implode(' AND ', $wheres) : '';

        $query = "SELECT COUNT(*)
                    FROM {$this->table}
                    {$sql_where}";

        return Db::getInstance()->run($query, $values)->fetchColumn() ?: 0;
    }

    /**
     * 포인트 목록 조회
     * @param array $params 조회조건
     * @return array|false
     */
    public function fetchPoints(array $params): array
    {
        $wheres = [];
        $values = [];

        if (isset($params['field']) && isset($params['keyword'])) {
            $wheres[] = "point.{$params['field']} LIKE :keyword";
            $values["keyword"] = "%{$params['keyword']}%";
        }

        if (isset($params['offset']) && isset($params['limit'])) {
            $values["offset"] = $params['offset'];
            $values["limit"] = $params['limit'];
            $sql_limit = "LIMIT :offset, :limit";
        }

        $sql_where = $wheres ? 'WHERE ' . implode(' AND ', $wheres) : '';

        $query = "SELECT point.*, member.mb_name, member.mb_nick, member.mb_image
                    FROM {$this->table} as point
                    LEFT JOIN {$this->member_service->table} as member ON member.mb_id = point.mb_id
                    {$sql_where}
                    ORDER BY created_at DESC, po_id DESC
                    {$sql_limit}";

        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * 관련 정보에 해당하는 포인트 내역 조회
     * @param string $mb_id 회원 아이디
     * @param string $rel_table 관련 테이블
     * @param string $rel_id 관련 아이디
     * @param string $rel_action 관련 액션
     * @return array|false
     */
    public function fetchPointByRelation(string $mb_id, string $rel_table, string $rel_id, string $rel_action)
    {
        $value = [
            'mb_id' => $mb_id,
            'po_rel_table' => $rel_table,
            'po_rel_id' => $rel_id,
            'po_rel_action' => $rel_action
        ];

        $query = "SELECT *
                    FROM {$this->table}
                    WHERE mb_id = :mb_id
                    AND po_rel_table = :po_rel_table
                    AND po_rel_id = :po_rel_id
                    AND po_rel_action = :po_rel_action";

        return Db::getInstance()->run($query, $value)->fetch();
    }

    /**
     * 회원의 포인트 합계 조회
     * @param string $mb_id 회원 아이디
     * @return int 포인트 합계
     */
    protected function fetchMemberPointSum(string $mb_id): int
    {
        $query = "SELECT SUM(po_point) as sum_point
                    FROM {$this->table}
                    WHERE mb_id = :mb_id";

        $row = Db::getInstance()->run($query, ['mb_id' => $mb_id])->fetch();
        return $row['sum_point'] ?? 0;
    }

    /**
     * 만료일이 지난 포인트 조회
     * - common.lib.php > get_expire_point() 함수 참고
     * @param string $mb_id 회원 아이디
     * @return int 만료된 포인트 합계
     */
    protected function fetchExpirePointSum(string $mb_id): int
    {
        if ($this->point_term === 0) {
            return 0;
        }

        $value = [
            'mb_id' => $mb_id,
            'po_expire_date' => date('Y-m-d')
        ];

        $query = "SELECT SUM(po_point - po_use_point) as sum_point
                    FROM {$this->table}
                    WHERE mb_id = :mb_id
                    AND po_expired = '0'
                    AND po_expire_date <> '9999-12-31'
                    AND po_expire_date < :po_expire_date";

        $row = Db::getInstance()->run($query, $value)->fetch();
        return $row['sum_point'] ?? 0;
    }

    /**
     * 사용하지 않은 포인트 내역 조회
     * @param string $mb_id 회원 아이디
     * @param string $po_id 제외할 포인트 아이디
     * @return array
     */
    protected function fetchUnusedPoints(string $mb_id, string $po_id = ''): array
    {
        if ($this->point_term) {
            $order_by = 'ORDER BY po_expire_date ASC, po_id ASC';
        } else {
            $order_by = 'ORDER BY po_id ASC';
        }

        $query = "SELECT po_id, po_point, po_use_point
                    FROM {$this->table}
                    WHERE mb_id = :mb_id
                    AND po_id <> :po_id
                    AND po_expired = '0'
                    AND po_point > po_use_point
                    {$order_by}";

        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id,
            'po_id' => $po_id
        ]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * 만료일이 지나지 않은 사용한 포인트 조회
     * @param string $mb_id 회원 아이디
     * @return array
     */
    protected function fetchNonExpiredUsedPoints(string $mb_id): array
    {
        if ($this->point_term) {
            $order_by = 'ORDER BY po_expire_date ASC, po_id ASC';
        } else {
            $order_by = 'ORDER BY po_id ASC';
        }

        $query = "SELECT po_id, po_point, po_use_point, po_expire_date
                    FROM {$this->table}
                    WHERE mb_id = :mb_id
                    AND po_expired <> '1'
                    AND po_use_point > 0
                    {$order_by}";

        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id,
        ]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * 만료되고 사용한 포인트 조회
     * @param string $mb_id 회원 아이디
     * @return array
     */
    protected function fetchExpiredUsedPoints(string $mb_id): array
    {
        $query = "SELECT po_id, po_point, po_use_point, po_expire_date
                    FROM {$this->table}
                    WHERE mb_id = :mb_id
                    AND po_expired = '1'
                    AND po_point >= 0
                    AND po_use_point > 0
                    ORDER BY po_expire_date DESC, po_id DESC";

        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id,
        ]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * 포인트 내역 추가
     * @param array $data 추가할 데이터
     * @return int 추가된 포인트 po_id
     */
    protected function insert(array $data): int
    {
        $insert_id = Db::getInstance()->insert($this->table, $data);

        return (int)$insert_id;
    }

    /**
     * 포인트 내역 수정
     * @param int $po_id 포인트 아이디
     * @param array $data 수정할 데이터
     * @return int 수정된 행 갯수
     */
    public function update(int $po_id, array $data): int
    {
        $update_count = Db::getInstance()->update($this->table, $data, ["po_id" => $po_id]);

        return $update_count;
    }

    /**
     * 유효기간이 지난 포인트 만료처리
     * @param string $mb_id 회원 아이디
     * @return int 만료된 포인트 개수
     */
    protected function updateExpiredPoints(string $mb_id): int
    {
        $value = [
            'mb_id' => $mb_id,
            'po_expire_date' => date('Y-m-d')
        ];

        $query = "UPDATE {$this->table}
                    SET po_expired = '1'
                    WHERE mb_id = :mb_id
                    AND po_expired <> '1'
                    AND po_expire_date <> '9999-12-31'
                    AND po_expire_date < :po_expire_date";

        return Db::getInstance()->run($query, $value)->rowCount();
    }

    /**
     * 포인트 목록에 회원 포인트 갱신
     * 
     * @param array $row 포인트 내역
     */
    public function updateHistoryForMember(array $row): void
    {
        if (isset($row['po_point'])) {
            $query = "UPDATE {$this->table}
                    SET po_mb_point = po_mb_point - :po_point
                    WHERE mb_id = :mb_id
                    AND po_id > :po_id";

            Db::getInstance()->run($query, [
                ':po_point' => $row['po_point'],
                ':mb_id' => $row['mb_id'],
                ':po_id' => $row['po_id'],
            ]);
        }
    }

    /**
     * 포인트 내역 삭제
     * @param string $po_id 포인트 아이디
     * @return bool
     */
    public function delete(int $po_id): bool
    {
        $row_count = Db::getInstance()->delete($this->table, ['po_id' => $po_id]);

        return $row_count > 0;
    }

    /**
     * 포인트 내역 삭제
     * @param string $mb_id 회원 아이디
     * @param string $rel_table 관련 테이블
     * @param string $rel_id 관련 아이디
     * @param string $rel_action 관련 액션
     * @return bool
     */
    protected function deleteByRelation(string $mb_id, string $rel_table, string $rel_id, string $rel_action): bool
    {
        $row_count = Db::getInstance()->delete($this->table, [
            'mb_id' => $mb_id,
            'po_rel_table' => $rel_table,
            'po_rel_id' => $rel_id,
            'po_rel_action' => $rel_action
        ]);

        return $row_count > 0;
    }
}
