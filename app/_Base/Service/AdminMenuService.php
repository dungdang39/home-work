<?php

namespace App\Base\Service;

use Core\Database\Db;

/**
 * 관리자 메뉴 서비스 클래스
 */
class AdminMenuService
{
    public const TABLE_NAME = 'admin_menu';
    public string $table;

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
    }

    public function getBreadcrumb(int $admin_menu_id): string
    {
        $breadcrumb = [];
        $menu = $this->fetchById($admin_menu_id);
        if ($menu) {
            $breadcrumb[] = $menu['am_name'];
            if ($menu['am_parent_id'] > 0) {
                $parent_menu = $this->fetchById($menu['am_parent_id']);
                // 재귀적으로 부모 메뉴를 불러옴
                $breadcrumb = array_merge(explode(' > ', $this->getBreadcrumb($parent_menu['am_id'])), $breadcrumb);
            }
        }
    
        // 배열을 " > "로 구분된 문자열로 변환
        return implode(' > ', $breadcrumb);
    }
    // ========================================
    // Database Queries
    // ========================================

    /**
     * 메뉴 조회
     * @param int $admin_menu_id 관리자 메뉴 ID
     * @return array|false
     */
    public function fetchById(int $admin_menu_id)
    {
        $query = "SELECT * FROM `{$this->table}` WHERE am_id = :admin_menu_id";
        $values = ['admin_menu_id' => $admin_menu_id];
        return Db::getInstance()->run($query, $values)->fetch();
    }

    /**
     * 관리자 메뉴 목록 조회
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
