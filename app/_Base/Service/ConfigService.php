<?php

namespace App\Base\Service;

use App\Base\Service\ThemeService;
use Core\Database\Db;

class ConfigService
{
    public const TABLE_NAME = 'config';

    protected array $cache = [];

    protected string $table;

    public function __construct()
    {
        $this->table = $_ENV['DB_PREFIX'] . self::TABLE_NAME;
    }

    public function getConfigs(string $scope): array
    {
        $configs = $this->cache[$scope] = $this->fetchConfigsByScope($scope);

        $result = [];
        foreach ($configs as $config) {
            $result[$config['name']] = $config['value'];
        }

        return $result;
    }

    /**
     * 현재 적용중인 테마 조회
     * @return string
     */
    public function getTheme(): string
    {
        return self::getConfig('config', 'theme') ?: ThemeService::DEFAULT_THEME;
    }

    /**
     * 환경설정 값 조회
     * @param string $scope 설정 범위
     * @param string $name 설정 이름
     * @param mixed $default 기본값
     * @return mixed 설정 값
     */
    public function getConfig(string $scope, string $name, $default = null)
    {
        if (isset($this->cache[$scope][$name])) {
            return $this->cache[$scope][$name];
        }

        $config = $this->fetch($scope, $name);

        if ($config) {
            $this->cache[$scope][$name] = $config['value'];
            return $config['value'];
        }

        return $default;
    }

    /**
     * 최고 관리자 여부 확인
     * @param string|null $mb_id 회원 아이디
     * @return bool
     */
    public function isSuperAdmin(?string $mb_id = null): bool
    {
        if (is_null($mb_id)) {
            return false;
        }
        return $this->getConfig('config', 'super_admin') === $mb_id;
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 환경설정 목록 조회
     */
    protected function fetchConfigsByScope(string $scope): array
    {
        $query = "SELECT * FROM {$this->table} WHERE scope = :scope";
        $values = ['scope' => $scope];
        return Db::getInstance()->run($query, $values)->fetchAll();
    }

    /**
     * 환경설정 1건 조회
     * @param string $scope 설정 범위
     * @param string $name 설정 이름
     * @return array|false
     */
    protected function fetch(string $scope, string $name)
    {
        $query = "SELECT * FROM {$this->table} WHERE scope = :scope AND name = :name";
        $values = ['scope' => $scope, 'name' => $name];
        return Db::getInstance()->run($query, $values)->fetch();
    }

    /**
     * 환경설정 정보 추가
     * @param array $data
     * @return int
     */
    public function insert(array $data): int
    {
        return Db::getInstance()->insert($this->table, $data);
    }

    /**
     * 환경설정 정보 수정
     * @param array $data
     * @return int
     */
    public function update(string $scope, string $name, string $value): int
    {
        return Db::getInstance()->update(
            $this->table,
            ['value' => $value],
            ['scope' => $scope, 'name' => $name]
        );
    }
}
