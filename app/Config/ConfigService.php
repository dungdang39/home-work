<?php

namespace app\Config;

use App\Admin\Service\ThemeService;
use Core\Database\Db;

class ConfigService
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
            $this->config = $this->fetchConfig() ?: [];
        }
        return $this->config;
    }

    public function getTheme(): string
    {
        $config = $this->getConfig();
        if (empty($config) || empty($config['cf_theme'])) {
            return ThemeService::DEFAULT_THEME;
        }
        return $config['cf_theme'];
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

    /**
     * 설정 캐시를 초기화합니다.
     */
    public function clearCache(): void
    {
        $this->config = [];
    }
}
