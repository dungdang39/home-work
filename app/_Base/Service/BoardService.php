<?php

namespace App\Base\Service;

use Core\Database\Db;

class BoardService
{
    public const TABLE_NAME = 'board';
    public const CATEGORY_TABLE_NAME = 'board_category';

    private string $table;
    private string $category_table;

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
        $this->category_table = $_ENV['DB_PREFIX'] . self::CATEGORY_TABLE_NAME;
    }

    /**
     * 게시판 목록 조회
     * @param array $params 검색 조건
     * @return array
     */
    public function getBoards(array $params = []): array
    {
        $boards = $this->fetchBoards($params);
        if (!$boards) {
            return [];
        }
        return $boards;
    }

    /**
     * 게시판 1건 조회
     * @param string $board_id 게시판 ID
     * @return array
     */
    public function getBoard(string $board_id): array
    {
        $board = $this->fetchBoard($board_id);
        if (!$board) {
            return [];
        }
        return $board;
    }

    /**
     * 게시판 카테고리 목록 조회
     * @return array
     */
    public function getCategories(string $board_id): array
    {
        $categories = $this->fetchCategories($board_id);
        if (!$categories) {
            return [];
        }
        return $categories;
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 게시판 카테고리 목록 조회
     * @param string $board_id 게시판 ID
     * @return array|false
     */
    public function fetchCategories(string $board_id)
    {
        $query = "SELECT * FROM {$this->category_table} WHERE board_id = :board_id ORDER BY display_order";
        return Db::getInstance()->run($query, ['board_id' => $board_id])->fetchAll();
    }

    /**
     * 총 게시판 수 조회
     * @param array $params 검색 조건
     * @return int
     */
    public function fetchBoardsTotalCount(array $params = [])
    {
        $wheres = [];
        $values = [];

        $this->addSearchConditions($wheres, $values, $params);
        $sql_where = DB::buildWhere($wheres);

        $query = "SELECT COUNT(*) FROM {$this->table} {$sql_where}";

        return Db::getInstance()->run($query, $values)->fetchColumn();
    }

    /**
     * 게시판 목록 조회
     * @param array $params 검색 조건
     * @return array|false
     */
    public function fetchBoards(array $params = [])
    {
        $wheres = [];
        $values = [];

        $this->addSearchConditions($wheres, $values, $params);
        $sql_where = DB::buildWhere($wheres);

        $sql_limit = '';
        if (isset($params['offset']) && isset($params['limit'])) {
            $values['offset'] = $params['offset'];
            $values['limit'] = $params['limit'];
            $sql_limit = 'LIMIT :offset, :limit';
        }

        $query = "SELECT *
                    FROM {$this->table}
                    {$sql_where}
                    ORDER BY display_order
                    {$sql_limit}";
        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * 그룹별 게시판 목록 조회
     * @param string $gr_id 그룹 ID
     * @return array|false
     */
    public function fetchBoardsByGroupId(string $gr_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE gr_id = :gr_id ORDER BY bo_order";
        $stmt = Db::getInstance()->run($query, ['gr_id' => $gr_id]);
        return $stmt->fetchAll();
    }

    /**
     * 게시판 정보 조회
     * @param string $board_id 게시판 ID
     * @return array|false
     */
    public function fetchBoard(string $board_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE board_id = :board_id";
        return Db::getInstance()->run($query, ['board_id' => $board_id])->fetch();
    }

    /**
     * 게시판 정보 수정
     * @param array $data 수정할 데이터
     * @return int
     */
    public function updateBoard(array $data): int
    {
        return Db::getInstance()->update($this->table, $data, ['bo_table' => $this->board['bo_table']]);
    }

    /**
     * 게시글 갯수 1 증가
     * @return void
     */
    public function increaseWriteCount(): void
    {
        $query = "UPDATE {$this->table} SET bo_count_write = bo_count_write + 1 WHERE bo_table = :bo_table";
        Db::getInstance()->run($query, ['bo_table' => $this->board['bo_table']]);
    }

    /**
     * 댓글 수 1 증가
     * @return void
     */
    public function increaseCommentCount(): void
    {
        $query = "UPDATE {$this->table} SET bo_count_comment = bo_count_comment + 1 WHERE bo_table = :bo_table";
        Db::getInstance()->run($query, ['bo_table' => $this->board['bo_table']]);
    }

    /**
     * 게시글 및 댓글 수 차감
     * @param int $count_writes 차감할 게시글 수
     * @param int $count_comments 차감할 댓글 수
     * @return void
     */
    public function decreaseWriteAndCommentCount(int $count_writes = 0, int $count_comments = 0): void
    {
        $query = "UPDATE {$this->table} 
                    SET bo_count_write = bo_count_write - :count_write, 
                        bo_count_comment = bo_count_comment - :count_comment 
                    WHERE bo_table = :bo_table";

        Db::getInstance()->run($query, [
            'count_write' => $count_writes,
            'count_comment' => $count_comments,
            'bo_table' => $this->board['bo_table']
        ]);
    }

    /**
     * 게시판 추가
     * @param array $data 추가할 데이터
     * @return int
     */
    public function insert(array $data): int
    {
        return Db::getInstance()->insert($this->table, $data);
    }

    /**
     * 게시판 수정
     * @param string $board_id 게시판 ID
     * @param array $data 수정할 데이터
     * @return int
     */
    public function update(string $board_id, array $data): int
    {
        return Db::getInstance()->update($this->table, $data, ['board_id' => $board_id]);
    }

    /**
     * 게시판 삭제
     * @param string $board_id 게시판 ID
     * @return int
     */
    public function delete(string $board_id): int
    {
        return Db::getInstance()->delete($this->table, ['board_id' => $board_id]);
    }

    /**
     * 목록 검색 조건 추가
     * @param array $params 검색 조건
     * @param array $wheres WHERE 절
     * @param array $values 바인딩 값
     * @return void
     */
    private function addSearchConditions(array &$wheres, array &$values, ?array $params = [])
    {
        if (isset($params['field']) && isset($params['keyword'])) {
            $wheres[] = "{$params['field']} LIKE :keyword";
            $values["keyword"] = "%{$params['keyword']}%";
        }
    }

    // ========================================
    // Getters and Setters
    // ========================================
}
