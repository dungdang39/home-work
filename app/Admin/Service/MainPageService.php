<?php

namespace App\Admin\Service;

use Core\Database\Db;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\ServerRequest as Request;

class MainPageService
{
    public const TABLE_NAME = 'mainpage';

    private string $table;
    private Request $request;

    public function __construct(
        Request $request
    ) {
        $this->request = $request;
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
    }

    /**
     * 메인페이지 섹션 목록 조회
     * @return array
     */
    public function getSections(): array
    {
        $sections = $this->fetchSections();
        if (empty($sections)) {
            return [];
        }
        return $sections;
    }

    /**
     * 메인페이지 섹션 조회
     * @param int $id 섹션 ID
     * @return array
     * @throws HttpNotFoundException
     */
    public function getSection(int $id): array
    {
        $section = $this->fetch($id);
        if (empty($section)) {
            throw new HttpNotFoundException($this->request, '메인페이지 섹션이 존재하지 않습니다.');
        }
        return $section;
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 메인페이지 섹션 목록 조회
     * @return array|false
     */
    public function fetchSections()
    {
        $query = "SELECT * FROM {$this->table}";
        return Db::getInstance()->run($query)->fetchAll();
    }

    /**
     * 메인페이지 섹션 조회
     * @param int $id 섹션 ID
     * @return array|false
     */
    public function fetch(int $id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        return Db::getInstance()->run($query, ['id' => $id])->fetch();
    }

    /**
     * 메인페이지 섹션이 이미 존재하는지 확인
     * @param string $section 섹션 유형
     * @param string $title 섹션 제목
     * @param int|null $id 섹션 ID
     */
    public function exists(string $section, string $title, ?int $id = null)
    {
        $values = ['section' => $section, 'title' => $title];
        
        $sql_where = '';
        if ($id) {
            $values['id'] = $id;
            $sql_where = 'AND id != :id';
        }

        $query = "SELECT EXISTS(
                        SELECT 1
                            FROM {$this->table}
                            WHERE section = :section
                                AND section_title = :title
                                {$sql_where}
                    ) as exist";
        // print_r($query);
        // exit;

        $stmt = Db::getInstance()->run($query, $values);
        return $stmt->fetchColumn() == 1;
    }

    /**
     * 메인페이지 섹션 추가
     * 
     * @param array $data 추가할 데이터
     * @return int  추가된 행의 ID
     */
    public function insert(array $data): int
    {
        return Db::getInstance()->insert($this->table, $data);
    }

    /**
     * 메인페이지 섹션 수정
     * 
     * @param int $id 섹션 ID
     * @param array $data 수정할 데이터
     * @return int  수정된 행 수
     */
    public function update(int $id, array $data): int
    {
        return Db::getInstance()->update($this->table, $data, ['id' => $id]);
    }

    /**
     * 메인페이지 섹션 삭제
     * 
     * @param int $id 섹션 ID
     * @return int  삭제된 행 수
     */
    public function delete(int $id): int
    {
        return Db::getInstance()->delete($this->table, ['id' => $id]);
    }
}