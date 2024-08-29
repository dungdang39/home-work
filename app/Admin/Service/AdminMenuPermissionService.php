<?php

namespace App\Admin\Service;

use Core\Database\Db;

/**
 * 관리자 메뉴 권한 서비스 클래스
 */
class AdminMenuPermissionService
{
    private string $table;
    private AdminMenuService $admin_menu_service;

    public function __construct(AdminMenuService $admin_menu_service)
    {
        $this->table = $_ENV['DB_PREFIX'] . 'admin_menu_permission';
        $this->admin_menu_service = $admin_menu_service;
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
    public function existsAdminMenuPermission(string $mb_id, string $route, string $method): bool
    {
        $query = "SELECT * FROM `{$this->table}`
                    WHERE mb_id = :mb_id
                    AND admin_menu_id = (
                        SELECT am_id FROM `{$this->admin_menu_service->table}`
                        WHERE am_route = :am_route
                        AND am_parent_id IS NOT NULL
                    )";

        if ($method === 'GET') {
            $query .= " AND `read` = 1";
        } elseif (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $query = " AND `write` = 1";
        } elseif ($method === 'DELETE') {
            $query = " AND `delete` = 1";
        }

        $stmt = Db::getInstance()->run($query, [
            "mb_id" => $mb_id,
            "am_route" => $route,
        ]);

        return $stmt->fetchColumn() > 0;
    }
}
