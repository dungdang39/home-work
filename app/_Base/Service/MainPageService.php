<?php

namespace App\Base\Service;

use Core\Database\Db;
use Slim\Exception\HttpNotFoundException;
use Slim\Http\ServerRequest as Request;

class MainPageService
{
    public const TABLE_NAME = 'mainpage';

    public string $table;
    private Request $request;
    private BannerService $banner_service;

    public function __construct(
        Request $request,
        BannerService $banner_service,
    ) {
        $this->request = $request;
        $this->banner_service = $banner_service;

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
        foreach ($sections as &$section) {
            $section['data_count'] = $this->getDataCountBySection($section);
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

    /**
     * 메인페이지 설정 > 배너 섹션 목록 조회
     * @return array
     */
    public function getBannerPositions()
    {
        $positions = $this->fetchSections(['section' => 'banner', 'is_enabled' => 1]);
        if (empty($positions)) {
            return [];
        }
        return $positions;
    }

    /**
     * 섹션 데이터 개수 조회
     * @param int $section_id 섹션 ID
     * @return int
     * @todo 다른 섹션 데이터 개수 조회 로직 추가 (커뮤니티, 쇼핑카테고리, 기획전)
     */
    public function getDataCountBySection(array $section): int
    {
        if ($section['section'] === 'banner') {
            return $this->banner_service->fetchBannersCountByPosition($section['id']);
        }

        return 0;
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 메인페이지 섹션 목록 조회
     * @return array|false
     */
    public function fetchSections(array $params = [])
    {
        $wheres = [];
        $values = [];

        if (!empty($params['section'])) {
            $wheres[] = 'section = :section';
            $values['section'] = $params['section'];
        }
        $sql_where = $wheres ? 'WHERE ' . implode(' AND ', $wheres) : '';

        $query = "SELECT *
                    FROM {$this->table}
                    {$sql_where}";
        return Db::getInstance()->run($query, $values)->fetchAll();
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
     * @param int|null $except_id 제외할 섹션 ID
     */
    public function exists(string $section, string $title, ?int $except_id = null)
    {
        $wheres = ["section = :section", "section_title = :title"];
        $values = ['section' => $section, 'title' => $title];

        if ($except_id) {
            $wheres[] = 'id != :id';
            $values['id'] = $except_id;
        }

        $sql_where = $wheres ? 'WHERE ' . implode(' AND ', $wheres) : '';

        $query = "SELECT EXISTS(
                        SELECT 1
                            FROM {$this->table}
                            {$sql_where}
                    ) as exist";

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
