<?php

namespace App\Base\Service;

use App\Base\Service\MemberService;
use Core\Database\Db;
use PDO;
use Slim\Http\ServerRequest as Request;
use Slim\Exception\HttpNotFoundException;

/**
 * 관리자 메뉴 권한 서비스 클래스
 */
class PermissionService
{
    public const TABLE_NAME = 'admin_menu_permission';

    public string $table;
    private AdminMenuService $admin_menu_service;
    private MemberService $member_service;
    private Request $request;

    public function __construct(
        Request $request,
        AdminMenuService $admin_menu_service,
        MemberService $member_service,
    ) {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;

        $this->request = $request;
        $this->admin_menu_service = $admin_menu_service;
        $this->member_service = $member_service;
    }

    /**
     * 관리자 메뉴 권한 목록 조회
     * @param array $params 검색 조건
     * @return array
     */
    public function getPermissions(?array $params = []): array
    {
        $permissions = $this->fetchList($params);
        if (empty($permissions)) {
            return [];
        }

        foreach ($permissions as &$permission) {
            $permission['breadcrumb'] = $this->admin_menu_service->getBreadcrumb($permission['admin_menu_id']);
        }

        return $permissions;
    }

    /**
     * 관리자 메뉴 권한 조회
     * @param string $mb_id 회원 ID
     * @param int $admin_menu_id 관리자 메뉴 ID
     * @return array
     */
    public function getPermission(string $mb_id, int $admin_menu_id): array
    {
        $permission = $this->fetch($mb_id, $admin_menu_id);
        if (empty($permission)) {
            throw new HttpNotFoundException($this->request, '관리자메뉴 권한이 존재하지 않습니다.');
        }
        return $permission;
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 총 권한 수 조회
     * @param array $params 검색 조건
     * @return int
     */
    public function fetchPermissionsTotalCount(?array $params = []): int
    {
        $wheres = [];
        $values = [];

        $this->addSearchConditions($wheres, $values, $params);

        $sql_where = Db::buildWhere($wheres);

        $query = "SELECT COUNT(*) FROM {$this->table} AS permission {$sql_where}";
        return Db::getInstance()->run($query, $values)->fetchColumn();
    }

    /**
     * 관리자 메뉴 권한을 가진 회원 목록 조회
     * @return array
     */
    public function fetchPermissionMembers(): array
    {
        $query = "SELECT DISTINCT mb_id FROM {$this->table}";
        return Db::getInstance()->run($query)->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * 관리자 메뉴 권한 존재 여부
     * @param string $mb_id 회원 ID
     * @param string $route_name 라우트
     * @param string $method HTTP 메소드
     * @return bool
     */
    public function existsAdminMenuPermission(string $mb_id, string $route_name, string $method): bool
    {
        $wheres = [];
        $values = [];

        $wheres[] = "mb_id = ?";
        $values[] = $mb_id;

        if ($method === 'GET') {
            $wheres[] = " `read` = 1";
        } elseif (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $wheres[] = " `write` = 1";
        } elseif ($method === 'DELETE') {
            $wheres[] = " `delete` = 1";
        }

        // route_name 분해하여 상위 라우트들을 생성
        $route_parts = explode('.', $route_name);
        $count = count($route_parts);
        $route_patterns = [];

        // 각 단계별로 상위 라우트를 생성하여 배열에 추가
        for ($i = 1; $i <= $count; $i++) {
            $route_patterns[] = implode('.', array_slice($route_parts, 0, $i));
        }

        // WHERE 절에 각 라우트 패턴을 조건으로 추가
        $route_placeholders = Db::makeWhereInPlaceHolder($route_patterns);

        // 모든 route_patterns를 values에 추가
        $values = array_merge($values, $route_patterns);

        $where = $wheres ? "WHERE " . implode(' AND ', $wheres) : "";

        $query = "SELECT * FROM `{$this->table}`
                    {$where}
                    AND admin_menu_id IN (
                        SELECT am_id FROM `{$this->admin_menu_service->table}`
                        WHERE am_route IN ($route_placeholders)
                    )";

        $stmt = Db::getInstance()->run($query, $values);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * 관리자 메뉴 권한 목록 정보 조회
     * @param array $params 검색 조건
     * @param array $pagination 페이징 정보
     * @return array
     */
    public function fetchList(array $params): array
    {
        $values = [];
        $wheres = [];

        $this->addSearchConditions($wheres, $values, $params);

        $values["offset"] = $params['offset'];
        $values["limit"] = $params['limit'];

        $sql_where = Db::buildWhere($wheres);
        $query = "SELECT permission.*, b.mb_nick, b.mb_name, c.am_name, c.am_parent_id
                    FROM {$this->table} AS permission
                    LEFT JOIN {$this->member_service->table} b ON permission.mb_id = b.mb_id
                    LEFT JOIN {$this->admin_menu_service->table} c ON permission.admin_menu_id = c.am_id
                    {$sql_where}
                    ORDER BY permission.mb_id ASC, c.am_id ASC
                    LIMIT :offset, :limit";

        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * 관리자 메뉴 권한 조회
     * @param string $mb_id 회원 ID
     * @param int $admin_menu_id 관리자 메뉴 ID
     * @return array|false
     */
    public function fetch(string $mb_id, int $admin_menu_id)
    {
        $query = "SELECT * FROM `{$this->table}`
                    WHERE mb_id = :mb_id
                    AND admin_menu_id = :admin_menu_id";

        $stmt = Db::getInstance()->run($query, [
            "mb_id" => $mb_id,
            "admin_menu_id" => $admin_menu_id,
        ]);

        return $stmt->fetch();
    }

    /**
     * 권한 존재 여부 확인
     * @param string $mb_id 회원 ID
     * @param int $admin_menu_id 관리자 메뉴 ID
     * @return bool
     */
    public function exists(string $mb_id, int $admin_menu_id): bool
    {
        $query = "SELECT EXISTS(
                    SELECT 1 FROM `{$this->table}`
                    WHERE mb_id = :mb_id
                    AND admin_menu_id = :admin_menu_id
                )";

        $stmt = Db::getInstance()->run($query, [
            "mb_id" => $mb_id,
            "admin_menu_id" => $admin_menu_id,
        ]);

        return (bool)$stmt->fetchColumn();
    }

    /**
     * 권한 추가
     * @param array $data 추가할 데이터
     * @return void
     */
    public function insert(array $data): void
    {
        $query = "INSERT INTO `{$this->table}`
                    SET mb_id = :mb_id,
                        admin_menu_id = :admin_menu_id,
                        `read` = :read,
                        `write` = :write,
                        `delete` = :delete";

        Db::getInstance()->run($query, $data);
    }

    /**
     * 권한 수정
     * @param string $mb_id 회원 ID
     * @param int $admin_menu_id 관리자 메뉴 ID
     * @param array $data 수정할 데이터
     * @return int
     */
    public function update(string $mb_id, int $admin_menu_id, array $data): int
    {
        $values = [
            "read" => $data['read'],
            "write" => $data['write'],
            "delete" => $data['delete'],
        ];
        $where = [
            "mb_id" => $mb_id,
            "admin_menu_id" => $admin_menu_id,
        ];

        return Db::getInstance()->update($this->table, $values, $where);
    }

    /**
     * 권한 삭제
     * @param string $mb_id 회원 ID
     * @param int $admin_menu_id 관리자 메뉴 ID
     * @return int
     */
    public function delete(string $mb_id, int $admin_menu_id): int
    {
        $where = [
            "mb_id" => $mb_id,
            "admin_menu_id" => $admin_menu_id,
        ];

        return Db::getInstance()->delete($this->table, $where);
    }

    /**
     * 검색 조건 추가
     * @param array $params 검색 조건
     * @param array $wheres WHERE 절
     * @param array $values 바인딩 값
     * @return void
     */
    private function addSearchConditions(array &$wheres, array &$values, ?array $params = [])
    {
        if (!empty($params['search_text'])) {
            $wheres[] = "permission.mb_id LIKE :search_text";
            $values["search_text"] = "%{$params['search_text']}%";
        }

        if (!empty($params['mb_id'])) {
            $wheres[] = "permission.mb_id = :mb_id";
            $values["mb_id"] = "{$params['mb_id']}";
        }
    }
}
