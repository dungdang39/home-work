<?php

namespace App\Base\Service;

use Core\Database\Db;
use DI\Container;
use Exception;

class QaService
{
    public const TABLE_NAME = 'qa';
    public const ANSWER_TABLE_NAME = 'qa_answer';

    private string $table;
    private string $answer_table;
    private Container $container;

    public function __construct(
        Container $container
    ) {
        $this->container = $container;
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
        $this->answer_table = $_ENV['DB_PREFIX'] . self::ANSWER_TABLE_NAME;
    }

    /**
     * Q&A 목록 조회
     * @param array $params
     * @return array
     */
    public function getQas(array $params = []): array
    {
        $qas = $this->fetchQas($params);
        if (empty($qas)) {
            return [];
        }
        $config_service = $this->container->get(QaConfigService::class);
        $member_service = $this->container->get(MemberService::class);
        foreach ($qas as &$qa) {
            $qa['category'] = $config_service->fetchCategory($qa['category_id']);
            $qa['member'] = $member_service->fetchMemberById($qa['mb_id']);
        }

        return $qas;
    }

    /**
     * Q&A 상세 조회
     * @param int $id
     * @return array
     */
    public function getQa(int $id): array
    {
        $qa = $this->fetchById($id);
        if (empty($qa)) {
            throw new Exception('Q&A 정보를 찾을 수 없습니다.');
        }
        $config_service = $this->container->get(QaConfigService::class);
        $member_service = $this->container->get(MemberService::class);
        $qa['category'] = $config_service->fetchCategory($qa['category_id']);
        $qa['member'] = $member_service->fetchMemberById($qa['mb_id']);

        return $qa;
    }

    /**
     * Q&A 삭제
     * - 답변 및 Q&A 삭제
     * @param int $id
     * @return bool
     * @todo 첨부파일 삭제 추가
     */
    public function deleteQa(int $id): bool
    {
        Db::getInstance()->getPdo()->beginTransaction();

        $this->deleteAnswer($id);

        // 첨부파일 삭제 추가

        $this->delete($id);

        Db::getInstance()->getPdo()->commit();

        return true;
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * Q&A 총 데이터 수 조회
     * @param array $params
     * @return int
     */
    public function fetchQasTotalCount(array $params = []): int
    {
        $values = [];
        $wheres = [];

        $this->addSearchConditions($wheres, $values, $params);

        $sql_where = Db::buildWhere($wheres);

        $query = "SELECT COUNT(*)
                    FROM {$this->table}
                    {$sql_where}";

        return Db::getInstance()->run($query, $values)->fetchColumn();
    }

    /**
     * Q&A 목록 조회
     * @param array $params
     * @return array|false
     */
    public function fetchQas(array $params = []): array
    {
        $values = [];
        $wheres = [];

        $this->addSearchConditions($wheres, $values, $params);

        $sql_where = Db::buildWhere($wheres);

        $sql_limit = '';
        if (isset($params['offset']) && isset($params['limit'])) {
            $values['offset'] = (int)$params['offset'];
            $values['limit'] = (int)$params['limit'];
            $sql_limit = 'LIMIT :offset, :limit';
        }

        $query = "SELECT *
                    FROM {$this->table}
                    {$sql_where}
                    {$sql_limit}";

        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    public function fetchById(int $id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        return Db::getInstance()->run($query, ['id' => $id])->fetch();
    }

    public function update(array $data): int
    {
        return Db::getInstance()->update($this->table, $data);
    }

    public function delete(int $id): int
    {
        return Db::getInstance()->delete($this->table, ['id' => $id]);
    }

    public function deleteAnswer(int $qa_id): int
    {
        return Db::getInstance()->delete($this->answer_table, ['qa_id' => $qa_id]);
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
        // 카테고리
        if (isset($params['category_id']) && $params['category_id']) {
            $wheres[] = "category_id = :category_id";
            $values['category_id'] = $params['category_id'];
        }
        // 상태
        if (isset($params['status']) && $params['status']) {
            $wheres[] = "status = :status";
            $values['status'] = $params['status'];
        }
        // 검색어
        if (!empty($params['keyword']) && $params['keyword']) {
            $wheres[] = "(subject LIKE :keyword1 OR content LIKE :keyword2)";
            $values["keyword1"] = "%{$params['keyword']}%";
            $values["keyword2"] = "%{$params['keyword']}%";
        }
    }
}
