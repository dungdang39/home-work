<?php

namespace App\Base\Service;

use Core\Database\Db;
use DI\Container;
use Exception;

class QaService
{
    public const QUESTION_TABLE_NAME = 'question';
    public const ANSWER_TABLE_NAME = 'question_answer';

    private string $question_table;
    private string $answer_table;
    private Container $container;

    public function __construct(
        Container $container
    ) {
        $this->container = $container;
        $this->question_table = $_ENV['DB_PREFIX'] . self::QUESTION_TABLE_NAME;
        $this->answer_table = $_ENV['DB_PREFIX'] . self::ANSWER_TABLE_NAME;
    }

    /**
     * Q&A 목록 조회
     * @param array $params
     * @return array
     */
    public function getQuestions(array $params = []): array
    {
        $qas = $this->fetchQuestions($params);
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
     * Q&A 질문 조회
     * @param int $id
     * @return array
     */
    public function getQuestion(int $id): array
    {
        $question = $this->fetchQuestion($id);
        if (empty($question)) {
            throw new Exception('Q&A 정보를 찾을 수 없습니다.');
        }
        $config_service = $this->container->get(QaConfigService::class);
        $member_service = $this->container->get(MemberService::class);
        $question['category'] = $config_service->fetchCategory($question['category_id']);
        $question['member'] = $member_service->fetchMemberById($question['mb_id']);

        return $question;
    }

    /**
     * Q&A 질문ID로 답변 조회
     * @param int $question_id
     * @return array
     */
    public function getAnswerByQuestion(int $question_id): array
    {
        $answer = $this->fetchAnswerByQuestionId($question_id);
        if (empty($answer)) {
            return [];
        }
        $member_service = $this->container->get(MemberService::class);
        $answer['admin'] = $member_service->fetchMemberById($answer['admin_id']);

        return $answer;
    }

    /**
     * Q&A 답변 조회
     * @param int $question_id
     * @return array
     */
    public function getAnswer(?int $answer_id = null): array
    {
        $answer = $this->fetchAnswer($answer_id);
        if (empty($answer)) {
            throw new Exception('Q&A 답변 정보를 찾을 수 없습니다.');
        }
        return $answer;
    }

    /**
     * Q&A 답변 등록
     * @param int $question_id
     * @param array $data
     */
    public function createAnswer(array $data): int
    {
        return $this->insertAnswer($data);
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
        $this->deleteQuestion($id);

        Db::getInstance()->getPdo()->commit();

        return true;
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * Q&A 질문 총 데이터 수 조회
     * @param array $params
     * @return int
     */
    public function fetchQuestionsTotalCount(array $params = []): int
    {
        $values = [];
        $wheres = [];

        $this->addSearchConditions($wheres, $values, $params);

        $sql_where = Db::buildWhere($wheres);

        $query = "SELECT COUNT(*)
                    FROM {$this->question_table}
                    {$sql_where}";

        return Db::getInstance()->run($query, $values)->fetchColumn();
    }

    /**
     * Q&A 질문 목록 조회
     * @param array $params
     * @return array|false
     */
    public function fetchQuestions(array $params = []): array
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
                    FROM {$this->question_table}
                    {$sql_where}
                    {$sql_limit}";

        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    public function fetchQuestion(int $id)
    {
        $query = "SELECT * FROM {$this->question_table} WHERE id = :id";
        return Db::getInstance()->run($query, ['id' => $id])->fetch();
    }

    public function fetchAnswer(?int $answer_id = null)
    {
        $query = "SELECT * FROM {$this->answer_table} WHERE id = :id";
        return Db::getInstance()->run($query, ['id' => $answer_id])->fetch();
    }

    public function fetchAnswerByQuestionId(int $question_id)
    {
        $query = "SELECT * FROM {$this->answer_table} WHERE question_id = :question_id";
        return Db::getInstance()->run($query, ['question_id' => $question_id])->fetch();
    }

    public function insertQuestion(array $data): int
    {
        return Db::getInstance()->insert($this->question_table, $data);
    }

    public function insertAnswer(array $data): int
    {
        return Db::getInstance()->insert($this->answer_table, $data);
    }

    public function updateQuestion(int $question_id, array $data): int
    {
        return Db::getInstance()->update($this->question_table, $data, ['id' => $question_id]);
    }

    public function updateAnswer(int $answer_id, array $data): int
    {
        return Db::getInstance()->update($this->answer_table, $data, ['id' => $answer_id]);
    }

    public function deleteQuestion(int $id): int
    {
        return Db::getInstance()->delete($this->question_table, ['id' => $id]);
    }

    public function deleteAnswer(int $question_id): int
    {
        return Db::getInstance()->delete($this->answer_table, ['question_id' => $question_id]);
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
