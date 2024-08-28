<?php

namespace App\Qa\Service;

use App\Admin\Service\ThemeService;
use Core\Database\Db;

class QaService
{
    private string $table;
    private array $config;

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . 'config';
    }

    public function getConfig()
    {
        if (empty($this->config)) {
            $this->config = $this->fetchConfig();
        }

        return $this->config;
    }

    public function getTheme(): string
    {
        return $this->getConfig()['cf_theme'] ?: ThemeService::DEFAULT_THEME;
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
