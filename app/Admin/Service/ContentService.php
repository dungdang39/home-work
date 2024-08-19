<?php

namespace App\Admin\Service;

use Core\Database\Db;

class ContentService
{
    private string $table;

    public function __construct()
    {
        $this->table = 'new_content';
    }

    /**
     * 콘텐츠 조회
     * @param int $co_id
     * @return array|null
     */
    public function fetchContent(int $co_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE co_id = :co_id";
        $content = Db::getInstance()->run($query, ['co_id' => $co_id])->fetch();
        if (!isset($content['co_id'])) {
            return null;
        }

        return $content;
    }


    /**
     * 콘텐츠 리스트 조회
     */
    public function fetchContents()
    {
        $query = "SELECT * FROM {$this->table}";

        return Db::getInstance()->run($query)->fetchAll();
    }
}
