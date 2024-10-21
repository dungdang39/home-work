<?php

namespace App\Base\Service;

use Core\Database\Db;

class QaService
{
    public const TABLE_NAME = 'qa';

    private string $table;

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
    }

    public function fetch()
    {
        $stmt = Db::getInstance()->run("SELECT * FROM {$this->table}");

        return $stmt->fetch();
    }

    public function update(array $data): int
    {
        return Db::getInstance()->update($this->table, $data);
    }
}
