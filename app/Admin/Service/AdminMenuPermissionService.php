<?php

namespace App\Admin\Service;

use App\Member\MemberService;
use Core\Database\Db;
use Exception;

/**
 * 관리자 메뉴 권한 서비스 클래스
 */
class AdminMenuPermissionService
{
    public string $table;
    private AdminMenuService $admin_menu_service;
    private MemberService $member_service;

    public function __construct(
        AdminMenuService $admin_menu_service,
        MemberService $member_service
    ) {
        $this->table = $_ENV['DB_PREFIX'] . 'admin_menu_permission';
        $this->admin_menu_service = $admin_menu_service;
        $this->member_service = $member_service;
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
            throw new Exception('권한이 존재하지 않습니다.');
        }
        return $permission;
    }

    /**
     * 관리자 메뉴 권한 목록 조회
     * @param array $params 검색 조건
     * @return array
     */
    public function getPermissions(array $params): array
    {
        $permissions = $this->fetchList($params);
        if (empty($permissions)) {
            return [];
        }
        return $permissions;
    }
    

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 관리자 메뉴 권한 존재 여부
     * @param string $route 라우트
     * @param string $mb_id 회원 ID
     * @param string $method HTTP 메소드
     * @return bool
     */
    public function existsAdminMenuPermission(string $mb_id, string $route_name, string $method): bool
    {
        $values = [];
        $values["mb_id"] = $mb_id;
        $values["route"] = $route_name;

        $where_route = "";
        $route = explode('.', $route_name);
        if (count($route) === 3) {
            $values['parent_route'] = $route[0] . '.' . $route[1];
            $where_route = "am_route = :parent_route OR am_route = :route";
        } else {
            $where_route = "am_route = :route";
        }

        $query = "SELECT * FROM `{$this->table}`
                    WHERE mb_id = :mb_id
                    AND admin_menu_id in (
                        SELECT am_id FROM `{$this->admin_menu_service->table}`
                        WHERE {$where_route}
                    )";

        if ($method === 'GET') {
            $query .= " AND `read` = 1";
        } elseif (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $query = " AND `write` = 1";
        } elseif ($method === 'DELETE') {
            $query = " AND `delete` = 1";
        }

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
        $where = "";

        if (!empty($params['search_text'])) {
            $where .= "a.mb_id LIKE :search_text";
            $values["search_text"] = "%{$params['search_text']}%";
        }

        $values["offset"] = $params['offset'];
        $values["limit"] = $params['limit'];

        $where = $where ? "WHERE {$where}" : "";
        $query = "SELECT a.*, b.mb_nick, b.mb_name, c.am_name, c.am_parent_id
                    FROM {$this->table} a
                    LEFT JOIN {$this->member_service->table} b on a.mb_id = b.mb_id
                    LEFT JOIN {$this->admin_menu_service->table} c on a.admin_menu_id = c.am_id
                    {$where}
                    ORDER BY a.mb_id ASC
                    LIMIT :offset, :limit";

        $stmt = Db::getInstance()->run($query, $values);

        return $stmt->fetchAll();
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

    public function delete(string $mb_id, int $admin_menu_id): int
    {
        $where = [
            "mb_id" => $mb_id,
            "admin_menu_id" => $admin_menu_id,
        ];

        return Db::getInstance()->delete($this->table, $where);
    }
}
