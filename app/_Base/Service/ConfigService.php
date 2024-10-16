<?php

namespace App\Base\Service;

use App\Base\Service\ThemeService;
use Core\Database\Db;

class ConfigService
{
    public const TABLE_NAME = 'config';

    private static ?array $config = null;
    private string $table;

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
    }

    /**
     * 기본환경설정 정보 가져오기
     * @return array
     */
    public static function getConfig(): array
    {
        if (self::$config === null) {
            self::$config = self::fetch();
        }

        return self::$config;
    }

    /**
     * 현재 적용중인 테마 조회
     * @return string
     */
    public static function getTheme(): string
    {
        $config = self::getConfig();
        return !empty($config['cf_theme']) ? $config['cf_theme'] : ThemeService::DEFAULT_THEME;
    }

    /**
     * 기본환경설정 조회
     * @return array|false
     */
    private static function fetch()
    {
        $table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
        $query = "SELECT * FROM {$table}";
        return Db::getInstance()->run($query)->fetch();
    }

    /**
     * 기본환경설정 정보 업데이트
     * @param array $data
     * @return int
     */
    public function update(array $data): int
    {
        return Db::getInstance()->update($this->table, $data);
    }

    /**
     * 설정 캐시를 초기화합니다.
     * @return void
     */
    public static function clearCache(): void
    {
        self::$config = null;
    }
}
