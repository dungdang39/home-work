<?php

namespace App\Admin\Service;

use Core\Database\Db;

class MenuService
{
    public const TABLE_NAME = 'menu';
    private string $table;

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
    }

    /**
     * 메뉴 목록 조회
     * @return array
     *
     * @todo cache 적용
     */
    public function getMenus(): array
    {
        $menus = $this->fetchMenus();
        if (!$menus) {
            return [];
        }
        return $this->buildMenuTree($menus);
    }

    /**
     * 메뉴 1건 조회
     * @param int $me_id 메뉴아이디
     * @return array
     */
    public function getMenu(int $me_id): array
    {
        $menu = $this->fetch($me_id);
        if (!$menu) {
            return [];
        }
        return $menu;
    }

    /**
     * 메뉴 추가
     */
    public function createMenu(array $data, string $key): int
    {
        $menu_data = [
            'me_parent_id' => $data['me_parent_id'][$key] ?: null,
            'me_name' => $data['me_name'][$key],
            'me_link' => $data['me_link'][$key],
            'me_order' => $data['me_order'][$key],
            'me_target' => $data['me_target'][$key],
        ];
        return $this->insert($menu_data);
    }

    /**
     * 메뉴 수정
     */
    public function updateMenu(array $data, string $key): int
    {
        $menu_data = [
            'me_parent_id' => $data['me_parent_id'][$key] ?: null,
            'me_name' => $data['me_name'][$key],
            'me_link' => $data['me_link'][$key],
            'me_order' => $data['me_order'][$key],
            'me_target' => $data['me_target'][$key],
        ];

        return $this->update($data['me_id'][$key], $menu_data);
    }

    /**
     * 메뉴 삭제
     */
    public function deleteMenu(array $menu)
    {
        Db::getInstance()->getPdo()->beginTransaction();

        $this->deleteAllMenu($menu);

        Db::getInstance()->getPdo()->commit();
    }

    /**
     * 재귀적 메뉴 전체 삭제
     * @param array $menu
     * @return void
     */
    private function deleteAllMenu(array $menu)
    {
        // 자식 메뉴 전부 삭제
        if (!empty($menu['me_id'])) {
            $children = $this->fetchMenusByParentId($menu['me_id']);
            foreach ($children as $childMenu) {
                $this->deleteAllMenu($childMenu);
            }
        }

        // 현재 메뉴 삭제
        $this->delete($menu['me_id']);
    }

    /**
     * 재귀적으로 메뉴 트리 생성
     * @todo AdminMenuMiddleware에도 동일한 로직이 있으므로 중복 제거 필요.
     * @param array $menus
     * @param int|null $parent_id
     * @return array
     */
    private function buildMenuTree(array $menus, int $parent_id = null)
    {
        $branch = [];

        foreach ($menus as $menu) {
            if ($menu['me_parent_id'] === $parent_id) {
                $sub_menu = $this->buildMenuTree($menus, $menu['me_id']);
                if ($sub_menu) {
                    $menu['sub_menu'] = $sub_menu;
                }
                $branch[] = $menu;
            }
        }

        return $branch;
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * DB 메뉴테이블에서 데이터를 가져옵니다.
     * @param bool $is_mobile
     * @return array
     */
    private function fetchMenus()
    {
        $query = "SELECT * FROM {$this->table}"; //  WHERE me_use = 1

        return Db::getInstance()->run($query)->fetchAll();
    }

    /**
     * 하위 메뉴 조회
     * @param int $me_parent_id
     * @return array
     */
    public function fetchMenusByParentId(int $me_parent_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE me_parent_id = :me_parent_id";
        return Db::getInstance()->run($query, ['me_parent_id' => $me_parent_id])->fetchAll();
    }

    /**
     * 메뉴 정보를 가져옵니다.
     * @param int $me_id 메뉴아이디
     * @return array|false
     */
    public function fetch(int $me_id): array
    {
        $query = "SELECT * FROM {$this->table} WHERE me_id = :me_id";
        return Db::getInstance()->run($query, ['me_id' => $me_id])->fetch();
    }

    /**
     * 메뉴 추가 처리
     * @param array $data 메뉴 데이터
     * @return string|false 메뉴 테이블 me_id
     */
    public function insert(array $data): int
    {
        $insert_id = Db::getInstance()->insert($this->table, $data);

        return $insert_id;
    }

    /**
     * 메뉴 수정 처리
     * @param string $me_id 메뉴아이디
     * @param array $data 수정할 데이터
     * @return int 수정된 행 갯수
     */
    public function update(int $me_id, array $data): int
    {
        $update_count = Db::getInstance()->update($this->table, $data, ['me_id' => $me_id]);

        return $update_count;
    }

    public function delete(int $me_id): int
    {
        return Db::getInstance()->delete($this->table, ['me_id' => $me_id]);
    }

    public function deleteAll()
    {
        return Db::getInstance()->delete($this->table, ['1' => 1]);
    }
}
