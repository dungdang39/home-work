<?php

namespace App\Faq;

use Core\Database\Db;
use Exception;

class FaqService
{
    public string $category_table;
    public string $table;

    public function __construct()
    {
        $this->category_table = $_ENV['DB_PREFIX'] . 'faq_category';
        $this->table = $_ENV['DB_PREFIX'] . 'faq';
    }

    /**
     * FAQ 카테고리 목록정보 가져오기
     * @param array $params  검색 조건
     * @return array
     */
    public function getFaqCategories(array $params): array
    {
        $faq_categories = $this->fetchFaqCategories($params);
        if (empty($faq_categories)) {
            return [];
        }

        foreach ($faq_categories as &$category) {
            $category['faq_count'] = $this->fetchFaqCount($category['id']);
        }

        return $faq_categories;
    }

    /**
     * FAQ 카테고리 정보 가져오기
     * @param int $id  FAQ 카테고리 ID
     * @return array
     */
    public function getFaqCategory(int $id): array
    {
        $faq_category = $this->fetchFaqCategory($id);
        if (empty($faq_category)) {
            throw new Exception('FAQ 카테고리 정보를 찾을 수 없습니다.', 404);
        }

        return $faq_category;
    }

    /**
     * FAQ 항목 목록정보 가져오기
     * @param int $faq_category_id  FAQ 카테고리 ID
     * @return array
     */
    public function getFaqs(int $faq_category_id): array
    {
        $faqs = $this->fetchFaqs($faq_category_id);

        return $faqs ?: [];
    }

    /**
     * FAQ 카테고리 정보 가져오기
     * @param int $id  FAQ 항목 ID
     * @return array
     */
    public function getFaq(int $id): array
    {
        $faq = $this->fetchFaq($id);

        if (empty($faq)) {
            throw new Exception('FAQ 항목 정보를 찾을 수 없습니다.', 404);
        }

        return $faq;
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * FAQ 카테고리 총 갯수 조회
     */
    public function fetchFaqCategoriesTotalCount(array $params = []): int
    {
        $wheres = [];
        $values = [];

        $where = $wheres ? "WHERE " . implode(' AND ', $wheres) : "";

        $query = "SELECT COUNT(*) FROM {$this->category_table} {$where}";

        return Db::getInstance()->run($query, $values)->fetchColumn();
    }

    /**
     * FAQ 카테고리 목록 조회
     * 
     * @param array $params  검색 조건
     * @return array|false
     */
    public function fetchFaqCategories(array $params = [])
    {
        $wheres = [];
        $values = [];

        if (isset($params['offset']) && isset($params['limit'])) {
            $values["offset"] = $params['offset'];
            $values["limit"] = $params['limit'];
            $sql_limit = "LIMIT :offset, :limit";
        }

        $where = $wheres ? "WHERE " . implode(' AND ', $wheres) : "";

        $query = "SELECT * FROM {$this->category_table}
                    {$where}
                    ORDER BY `order` ASC, created_at ASC
                    {$sql_limit}";

        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * FAQ 카테고리 조회
     * @param int $id  FAQ 카테고리 ID
     * @return array|false
     */
    public function fetchFaqCategory(int $id)
    {
        $query = "SELECT * FROM {$this->category_table} WHERE id = :id";
        return Db::getInstance()->run($query, ["id" => $id])->fetch();
    }

    /**
     * FAQ 카테고리에 속한 FAQ 갯수 조회
     * @param int $faq_category_id  FAQ 카테고리 ID
     * @return int
     */
    public function fetchFaqCount(int $faq_category_id): int
    {
        $query = "SELECT COUNT(*) FROM {$this->table} WHERE faq_category_id = :faq_category_id";
        return Db::getInstance()->run($query, ["faq_category_id" => $faq_category_id])->fetchColumn();
    }

    /**
     * FAQ 항목 목록 조회
     * 
     * @param int $faq_category_id  FAQ 카테고리 ID
     * @return array|false
     */
    public function fetchFaqs(int $faq_category_id)
    {
        $wheres = [];
        $values = [];

        $wheres[] = "faq_category_id = :faq_category_id";
        $values["faq_category_id"] = $faq_category_id;

        $where = $wheres ? "WHERE " . implode(' AND ', $wheres) : "";

        $query = "SELECT * FROM {$this->table}
                    {$where}
                    ORDER BY `order` ASC, `created_at` DESC";

        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * FAQ 항목 조회
     * 
     * @param int $id  FAQ 항목 ID
     * @return array|false
     */
    public function fetchFaq(int $id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id1 = :id";
        return Db::getInstance()->run($query, ["id" => $id])->fetch();
    }

    /**
     * FAQ 추가
     * 
     * @param int $faq_category_id  FAQ 카테고리 ID
     * @param array $data  추가할 데이터
     * @return int  추가된 행의 ID
     */
    public function insert(int $faq_category_id, array $data): int
    {
        $data['faq_category_id'] = $faq_category_id;
        return Db::getInstance()->insert($this->table, $data);
    }

    /**
     * FAQ 카테고리 추가
     * 
     * @param array $data  추가할 데이터
     * @return int  추가된 행의 ID
     */
    public function insertCategory(array $data): int
    {
        return Db::getInstance()->insert($this->category_table, $data);
    }

    /**
     * FAQ 항목 수정
     * 
     * @param int $id  FAQ 항목ID
     * @param array $data  수정할 데이터
     * @return int  수정된 행 수
     */
    public function update(string $id, array $data): int
    {
        return Db::getInstance()->update($this->table, $data, ["id" => $id]);
    }

    /**
     * FAQ 카테고리 수정
     * 
     * @param int $id  FAQ 카테고리 ID
     * @param array $data  수정할 데이터
     * @return int  수정된 행 수
     */
    public function updateCategory(int $id, array $data): int
    {
        return Db::getInstance()->update($this->category_table, $data, ["id" => $id]);
    }

    /**
     * FAQ 항목 삭제
     * @param int $id  FAQ 항목 ID
     * @return int  삭제된 행 수
     */
    public function delete(int $id): int
    {
        return Db::getInstance()->delete($this->table, ["id" => $id]);
    }

    /**
     * FAQ 항목들 삭제
     * @param int $faq_category_id  FAQ 카테고리 ID
     * @return int  삭제된 행 수
     */
    public function deleteFaqs(int $faq_category_id): int
    {
        return Db::getInstance()->delete($this->table, ["faq_category_id" => $faq_category_id]);
    }

    /**
     * FAQ 카테고리 삭제
     * 
     * @param string $code  FAQ 카테고리 코드
     * @return int  삭제된 행 수
     */
    public function deleteCategory(int $id): int
    {
        return Db::getInstance()->delete($this->category_table, ["id" => $id]);
    }

    // ========================================
    // Getters and Setters
    // ========================================
}
