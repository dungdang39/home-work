<?php

namespace App\Base\Service;

use Core\Database\Db;

class QaService
{
    public const TABLE_NAME = 'qa';
    public const CATEGORY_TABLE_NAME = 'qa_category';

    private string $table;
    private string $category_table;

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
        $this->category_table = $_ENV['DB_PREFIX'] . self::CATEGORY_TABLE_NAME;
    }

    /**
     * Q&A 카테고리 목록 조회
     * @return array
     */
    public function getCategories()
    {
        $categories = $this->fetchCategories();
        if (empty($categories)) {
            return [];
        }

        foreach ($categories as &$category) {
            $category['qa_count'] = $this->fetchQasTotalCount(['category_id' => $category['id']]);
        }

        return $categories;
    }

    /**
     * Q&A 카테고리 조회
     * @param int $id
     * @return array
     */
    public function getCategory(int $id)
    {
        $category = $this->fetchCategory($id);
        if (empty($category)) {
            throw new \Exception('존재하지 않는 카테고리입니다.');
        }
        return $category;
    }

    public function createCategory(array $data): int
    {
        $data['display_order'] = $this->fetchMaxDisplayOrder() + 1;
        return $this->insertCategory($data);
    }

    // ========================================
    // Database Queries
    // ========================================

    public function fetchQasTotalCount(array $params = []): int
    {
        $values = [];
        $wheres = [];

        if (isset($params['category_id'])) {
            $wheres[] = "category_id = :category_id";
            $values['category_id'] = $params['category_id'];
        }

        $sql_where = $wheres ? 'WHERE ' . implode(' AND ', $wheres) : '';

        $query = "SELECT COUNT(*)
                    FROM {$this->table}
                    {$sql_where}";

        return Db::getInstance()->run($query, $values)->fetchColumn();
    }

    public function fetchCategories()
    {
        $stmt = Db::getInstance()->run("SELECT * FROM {$this->category_table}");

        return $stmt->fetchAll();
    }

    public function fetchCategory(int $id)
    {
        $query = "SELECT * FROM {$this->category_table} WHERE id = :id";
        return Db::getInstance()->run($query, ['id' => $id])->fetch();
    }
    

    private function fetchMaxDisplayOrder(): int
    {
        $query = "SELECT MAX(display_order) FROM {$this->category_table}";
        return Db::getInstance()->run($query)->fetchColumn();
    }

    public function fetch()
    {
        $stmt = Db::getInstance()->run("SELECT * FROM {$this->table}");

        return $stmt->fetch();
    }

    public function insertCategory(array $data): int
    {
        return Db::getInstance()->insert($this->category_table, $data);
    }

    public function update(array $data): int
    {
        return Db::getInstance()->update($this->table, $data);
    }

    public function updateCategory(int $id, array $data): int
    {
        return Db::getInstance()->update($this->category_table, $data, ['id' => $id]);
    }

    public function deleteCategory(int $id): int
    {
        return Db::getInstance()->delete($this->category_table, ['id' => $id]);
    }
}
