<?php

namespace App\Admin;

use Core\Database\Db;

/**
 * 관리자 메뉴 서비스 클래스
 * @todo 캐시처리 적용
 * @todo 테이블 이름 변경
 */
class AdminMenuService
{
    public string $table;

    public function __construct()
    {
        $this->table = 'g5_admin_menu';
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 메뉴 조회
     * @return array|false
     */
    public function fetchAll()
    {
        $query = "SELECT * FROM `{$this->table}` order by `am_parent_id`, `am_order`";

        $stmt = Db::getInstance()->run($query);

        return $stmt->fetchAll();
    }

    /**
     * 부모 메뉴 조회
     * @return array|false
     */
    public function fetchParents()
    {
        $query = "SELECT * FROM `{$this->table}` WHERE am_parent_id is null order by `am_order`";

        $stmt = Db::getInstance()->run($query);

        return $stmt->fetchAll();
    }

    /**
     * 자식 메뉴 조회
     * @param int $parent_id  부모 메뉴 ID
     * @return array|false
     */
    public function fetchChildByParentId(int $parent_id)
    {
        $query = "SELECT * FROM `{$this->table}` WHERE am_parent_id = :parent_id";
        $stmt = Db::getInstance()->run($query, ["parent_id" => $parent_id]);

        return $stmt->fetchAll();
    }
}
