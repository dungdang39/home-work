<?php

namespace app\Config;

use Core\Database\Db;

class ConfigService
{
    private string $table;
    private array $config;

    public function __construct()
    {
        // @TODO: 테이블명을 변경해야함.
        $this->table = 'new_config';
    }

    public function getConfig()
    {
        if (empty($this->config)) {
            $this->config = $this->fetchConfig();
        }

        return $this->config;
    }

    public function fetchConfig()
    {
        $stmt = Db::getInstance()->run("SELECT * FROM {$this->table}");

        return $stmt->fetch();
    }

    public function update(array $data): int
    {
        return Db::getInstance()->update($this->table, $data);
    }
}