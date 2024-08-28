<?php

namespace App\Qa\Service;

use Core\Database\Db;

class QaConfigService
{
    private string $table;
    private array $qa_config;

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . 'qa_config';
    }

    public function getQaConfig()
    {
        if (empty($this->qa_config)) {
            $this->qa_config = $this->fetchQaConfig() ?: [];
        }

        return $this->qa_config;
    }

    public function fetchQaConfig()
    {
        $stmt = Db::getInstance()->run("SELECT * FROM {$this->table}");

        return $stmt->fetch();
    }

    public function insert(array $data): int
    {
        return Db::getInstance()->insert($this->table, $data);
    }

    public function update(array $data): int
    {
        return Db::getInstance()->update($this->table, $data);
    }
}
